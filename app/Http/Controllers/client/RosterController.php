<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\EmployeeOT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;

class RosterController extends Controller
{
    private function authorizeAccess()
    {
        if (!session()->has('ClientUser'))
            abort(403, 'Unauthorized access.');
    }

    public function manageRoster(Request $request)
    {
        $this->authorizeAccess();
        $clientId = session('ClientUser');

        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');
        $departmentId = $request->department_id;

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Filters
        $departments = Department::where('client_id', $clientId)->where('status', 1)->where('trash', 0)->get();
        $shifts = DB::table('rl_shifts_tbl')->where('client_id', $clientId)->where('status', 1)->where('trash', 0)->get();

        // Fetch Employees
        $query = Employee::with(['department', 'designationInfo'])
            ->where('client_id', $clientId)
            ->where('status', 1)
            ->where('trash', 0);

        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
        }

        $employees = $query->get();

        // 1. Flat List for the Bulk Assign Modal Dropdown
        $flatEmployees = $employees->sortBy('employee_name');

        // 2. Grouped List for the Matrix Grid
        $groupedEmployees = $employees->sortBy(function ($emp) {
            $priority = $emp->department ? $emp->department->priority : 99999;
            return sprintf('%05d-%s', $priority, $emp->employee_name);
        })->groupBy(function ($emp) {
            return $emp->department ? $emp->department->department_name : 'General Administration';
        });

        // Existing Rosters
        $existingRosters = DB::table('rl_employee_rosters_tbl')
            ->where('client_id', $clientId)
            ->whereMonth('roster_date', $month)
            ->whereYear('roster_date', $year)
            ->get()
            ->keyBy(function ($r) {
                return $r->employee_id . '_' . Carbon::parse($r->roster_date)->format('d');
            });

        return view('client.roster.manage', compact(
            'flatEmployees',
            'groupedEmployees',
            'shifts',
            'departments',
            'existingRosters',
            'month',
            'year',
            'daysInMonth',
            'departmentId'
        ));
    }

    public function saveRoster(Request $request)
    {
        $this->authorizeAccess();
        $clientId = session('ClientUser');

        $request->validate([
            'rosterData' => 'required|array'
        ]);

        try {
            DB::beginTransaction();
            $now = Carbon::now();

            foreach ($request->rosterData as $employeeId => $dates) {
                foreach ($dates as $date => $shiftValue) {

                    // If the value is empty, the user cleared the shift. We must delete the record.
                    if ($shiftValue === '') {
                        DB::table('rl_employee_rosters_tbl')
                            ->where('client_id', $clientId)
                            ->where('employee_id', $employeeId)
                            ->where('roster_date', $date)
                            ->delete();
                        continue;
                    }

                    $isWeekOff = ($shiftValue === 'weekoff') ? 1 : 0;
                    $shiftId = ($shiftValue === 'weekoff') ? null : $shiftValue;

                    // Manual check prevents the strict unique-key constraint crashes caused by upsert()
                    $existing = DB::table('rl_employee_rosters_tbl')
                        ->where('client_id', $clientId)
                        ->where('employee_id', $employeeId)
                        ->where('roster_date', $date)
                        ->first();

                    if ($existing) {
                        DB::table('rl_employee_rosters_tbl')
                            ->where('id', $existing->id)
                            ->update([
                                'shift_id' => $shiftId,
                                'is_weekoff' => $isWeekOff,
                                'updated_at' => $now
                            ]);
                    } else {
                        DB::table('rl_employee_rosters_tbl')->insert([
                            'client_id' => $clientId,
                            'employee_id' => $employeeId,
                            'roster_date' => $date,
                            'shift_id' => $shiftId,
                            'is_weekoff' => $isWeekOff,
                            'created_at' => $now,
                            'updated_at' => $now
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Roster updated successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Roster Save Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX handler for Double Shift / Absentee Reassignment
     */
    public function reassignShiftAjax(Request $request)
    {
        $this->authorizeAccess();
        $clientId = session('ClientUser');

        $request->validate([
            'from_emp_id' => 'required',
            'to_emp_id' => 'required',
            'date' => 'required|date',
            'shift_id' => 'required'
        ]);

        try {
            DB::beginTransaction();

            // 1. Remove shift from absent employee completely
            DB::table('rl_employee_rosters_tbl')
                ->where('client_id', $clientId)
                ->where('employee_id', $request->from_emp_id)
                ->where('roster_date', $request->date)
                ->delete();

            // 2. Add an Extra Shift (OT) entry for the covering employee
            $dateObj = Carbon::parse($request->date);
            $month = $dateObj->month;
            $year = $dateObj->year;
            $daysInMonth = $dateObj->daysInMonth;

            $coveringEmp = Employee::find($request->to_emp_id);
            $gross = $coveringEmp->gross_salary ?? 0;
            $perDayRate = $daysInMonth ? ($gross / $daysInMonth) : 0;

            $ot = EmployeeOT::where('client_id', $clientId)
                ->where('employee_id', $request->to_emp_id)
                ->where('ot_month', $month)
                ->where('ot_year', $year)
                ->first();

            $remarkString = "Extra Shift: " . $request->date;

            if ($ot) {
                $ot->ot_days += 1;
                $ot->ot_hours += 8; // Assumes standard 8 hour extra shift
                $ot->ot_amount = $ot->ot_days * $perDayRate;

                // Append the date so the Attendance controller can catch it
                $ot->remarks = empty($ot->remarks) ? $remarkString : $ot->remarks . " | " . $remarkString;
                $ot->save();
            } else {
                EmployeeOT::create([
                    'client_id' => $clientId,
                    'employee_id' => $request->to_emp_id,
                    'ot_month' => $month,
                    'ot_year' => $year,
                    'ot_days' => 1,
                    'ot_hours' => 8,
                    'ot_rate' => $perDayRate,
                    'ot_amount' => 1 * $perDayRate,
                    'remarks' => $remarkString,
                    'status' => 1,
                    'trash' => 0,
                    'created_by' => $clientId
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Shift reassigned successfully and OT recorded for the covering employee.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Shift Reassign OT Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function exportRoster(Request $request)
    {
        $this->authorizeAccess();
        $clientId = session('ClientUser');

        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');
        $departmentId = $request->department_id;

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Fetch Shifts
        $shifts = DB::table('rl_shifts_tbl')
            ->where('client_id', $clientId)
            ->where('status', 1)
            ->where('trash', 0)
            ->get()
            ->keyBy('id');

        // Fetch Employees
        $query = Employee::with(['department', 'designationInfo'])
            ->where('client_id', $clientId)
            ->where('status', 1)
            ->where('trash', 0);

        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
        }

        $employees = $query->get();

        // Group Employees by Department
        $groupedEmployees = $employees->sortBy(function ($emp) {
            $priority = $emp->department ? $emp->department->priority : 99999;
            return sprintf('%05d-%s', $priority, $emp->employee_name);
        })->groupBy(function ($emp) {
            return $emp->department ? $emp->department->department_name : 'General Administration';
        });

        // Fetch Existing Rosters
        $existingRosters = DB::table('rl_employee_rosters_tbl')
            ->where('client_id', $clientId)
            ->whereMonth('roster_date', $month)
            ->whereYear('roster_date', $year)
            ->get()
            ->keyBy(function ($r) {
                return $r->employee_id . '_' . Carbon::parse($r->roster_date)->format('d');
            });

        $monthName = date('F', mktime(0, 0, 0, $month, 10));

        // Output as .xls so the user's OS opens it directly in Excel
        $fileName = "Shift_Roster_{$monthName}_{$year}.xls";

        $clientName = session('ClientName', 'Company Name');

        // Render blade view as an Excel XML file
        return response()->view('client.roster.export', compact(
            'groupedEmployees',
            'shifts',
            'existingRosters',
            'month',
            'year',
            'daysInMonth',
            'monthName',
            'clientName'
        ))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}