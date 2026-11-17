<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\SubDepartment;
use App\Models\Designation;
use App\Models\DesignationShift;
use App\Models\Employee;
use App\Models\EmployeeShift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class EmployeeShiftAssignmentController extends Controller
{
    /**
     * Display the shift assignment UI.
     */
    public function index(Request $request)
    {
        try {
            $clientId = session('ClientUser');

            $departments = Department::where('client_id', $clientId)
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('department_name', 'asc')
                ->get();

            return view('client.employees.assign_shifts', compact('departments'));
        } catch (Exception $e) {
            Log::error('Error loading Shift Assignment page: ' . $e->getMessage());
            return back()->with('error', 'Unable to load the shift assignment page.');
        }
    }

    /**
     * AJAX: Get filter dependencies (Sub Depts & Designations) based on Department.
     */
    public function getDependencies(Request $request)
    {
        $request->validate(['department_id' => 'required|integer']);
        $clientId = session('ClientUser');
        $deptId = $request->department_id;

        $subDepartments = SubDepartment::where('client_id', $clientId)
            ->where('department_id', $deptId)
            ->where('status', 1)
            ->where('trash', 0)
            ->get(['id', 'sub_department_name']);

        $designations = Designation::where('client_id', $clientId)
            ->where('department_id', $deptId)
            ->where('status', 1)
            ->where('trash', 0)
            ->get(['id', 'designation_name']);

        return response()->json([
            'sub_departments' => $subDepartments,
            'designations' => $designations
        ]);
    }

    /**
     * AJAX: Get Employees and available Shifts based on Filters.
     */
    public function getEmployeesAndShifts(Request $request)
    {
        $request->validate([
            'department_id' => 'required|integer',
            'designation_id' => 'required|integer'
        ]);

        $clientId = session('ClientUser');

        // Fetch Employees
        $empQuery = Employee::where('client_id', $clientId)
            ->where('department_id', $request->department_id)
            ->where('designation_id', $request->designation_id)
            ->where('status', 1)
            ->where('trash', 0);

        if ($request->filled('sub_department_id')) {
            $empQuery->where('sub_department_id', $request->sub_department_id);
        }

        $employees = $empQuery->select('id', 'employee_id as emp_code', 'employee_name')->get();

        // Fetch Master Shifts for this Designation
        $shifts = DesignationShift::where('designation_id', $request->designation_id)
            ->get(['id', 'shift_name', 'login_time', 'logout_time', 'is_overnight']);

        // Format shifts for display
        $shifts->transform(function ($shift) {
            $in = Carbon::parse($shift->login_time)->format('h:i A');
            $out = Carbon::parse($shift->logout_time)->format('h:i A');
            $overnight = $shift->is_overnight ? ' (Next Day)' : '';
            $shift->display_name = "{$shift->shift_name} [{$in} - {$out}]{$overnight}";
            return $shift;
        });

        return response()->json([
            'employees' => $employees,
            'shifts' => $shifts
        ]);
    }

    /**
     * Store bulk shift assignments for selected employees.
     */
    public function store(Request $request)
    {
        $request->validate([
            'designation_shift_id' => 'required|integer|exists:rl_designation_shifts_tbl,id',
            'start_date' => 'required|date',
            'number_of_days' => 'required|integer|min:1',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'integer|exists:rl_employees_tbl,id'
        ]);

        try {
            DB::beginTransaction();
            $clientId = session('ClientUser');

            $startDate = Carbon::parse($request->start_date);
            // Calculate End Date: Start Date + (Days - 1)
            $endDate = $startDate->copy()->addDays($request->number_of_days - 1);

            $insertData = [];
            $now = now();

            foreach ($request->employee_ids as $empId) {
                // Check for exact overlap to prevent duplicates (Optional but recommended)
                EmployeeShift::where('employee_id', $empId)
                    ->where('start_date', '<=', $endDate->toDateString())
                    ->where('end_date', '>=', $startDate->toDateString())
                    ->delete(); // Removing overlaps to apply new master rule

                $insertData[] = [
                    'client_id' => $clientId,
                    'employee_id' => $empId,
                    'designation_shift_id' => $request->designation_shift_id,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'status' => 1,
                    'created_by' => $clientId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Bulk Insert
            EmployeeShift::insert($insertData);

            DB::commit();
            return redirect()->back()->with('success', 'Shifts successfully assigned to ' . count($request->employee_ids) . ' employee(s) for ' . $request->number_of_days . ' day(s).');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error assigning bulk shifts: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to assign shifts. Please try again.');
        }
    }

    /**
     * Display a list of all assigned employee shifts with filters.
     */
    public function assignedShifts(Request $request)
    {
        try {
            $clientId = session('ClientUser');

            // Start query with eager loading for optimization
            $query = EmployeeShift::with(['employee.department', 'employee.subDepartment', 'shift'])
                ->where('client_id', $clientId);

            // Filter by Date Range (Overlapping dates)
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->where(function ($q) use ($request) {
                    $q->where('start_date', '<=', $request->end_date)
                        ->where('end_date', '>=', $request->start_date);
                });
            }

            // Filter by Employee Attributes (Department, Sub Dept, Search)
            if ($request->filled('department_id') || $request->filled('sub_department_id') || $request->filled('search')) {
                $query->whereHas('employee', function ($q) use ($request) {
                    if ($request->filled('department_id')) {
                        $q->where('department_id', $request->department_id);
                    }
                    if ($request->filled('sub_department_id')) {
                        $q->where('sub_department_id', $request->sub_department_id);
                    }
                    if ($request->filled('search')) {
                        $q->where(function ($sq) use ($request) {
                            $sq->where('employee_name', 'like', '%' . $request->search . '%')
                                ->orWhere('employee_id', 'like', '%' . $request->search . '%');
                        });
                    }
                });
            }

            $assignedShifts = $query->orderBy('start_date', 'desc')
                ->paginate(15)
                ->appends($request->all());

            $departments = Department::where('client_id', $clientId)
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('department_name', 'asc')
                ->get();

            $subDepartments = SubDepartment::where('client_id', $clientId)
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('sub_department_name', 'asc')
                ->get();

            return view('client.employees.view_assigned_shifts', compact('assignedShifts', 'departments', 'subDepartments'));

        } catch (Exception $e) {
            Log::error('Error loading assigned shifts: ' . $e->getMessage());
            return back()->with('error', 'Unable to load the assigned shifts page.');
        }
    }
}