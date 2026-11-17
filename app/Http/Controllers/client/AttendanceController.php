<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Client;
use App\Models\Holiday;
use App\Models\Department;
use App\Models\OfficeTiming;
use App\Models\AttendanceLog;
use App\Models\PaidLeave;
use App\Models\EmployeeOT;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\PayrollService;
use Illuminate\Support\Facades\DB;


class AttendanceController extends Controller
{
    private function authorizeAccess()
    {
        if (!session()->has('ClientUser')) {
            abort(403, 'Unauthorized access.');
        }

        $client = Client::where('id', session('ClientUser'))
            ->where('status', 1)
            ->where('trash', 0)
            ->first();

        if (!$client) {
            session()->forget('ClientUser');
            abort(403, 'Unauthorized access.');
        }
    }
    private function getLoggedInUser()
    {
        return Client::where('id', session('ClientUser'))
            ->where('status', 1)
            ->where('trash', 0)
            ->first();
    }

    public function manageAttendancejok(Request $request, PayrollService $payrollService)
    {
        $this->authorizeAccess();
        $user = $this->getLoggedInUser();

        // 1. Base Query - Removed 'employee.designationInfo.shifts' to decouple from legacy shift logic
        $query = Attendance::with(['employee.department', 'employee.designationInfo'])
            ->select('rl_attendance_tbl.*')
            ->join('rl_employees_tbl', 'rl_attendance_tbl.emp_id', '=', 'rl_employees_tbl.id')
            ->leftJoin('rl_departments_tbl', 'rl_employees_tbl.department_id', '=', 'rl_departments_tbl.id')
            ->where('rl_attendance_tbl.client_id', $user->id);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('rl_employees_tbl.employee_name', 'like', "%{$search}%")
                    ->orWhere('rl_employees_tbl.employee_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->where('rl_attendance_tbl.punch_in_time', '>=', Carbon::createFromFormat('d-m-Y', $request->from_date)->startOfDay());
        }

        if ($request->filled('to_date')) {
            $query->where('rl_attendance_tbl.punch_in_time', '<=', Carbon::createFromFormat('d-m-Y', $request->to_date)->endOfDay());
        }

        $attendances = $query
            ->orderByRaw('ISNULL(rl_departments_tbl.priority), rl_departments_tbl.priority ASC')
            ->orderBy('rl_departments_tbl.department_name', 'asc')
            ->orderBy('rl_attendance_tbl.punch_in_time', 'desc')
            ->paginate(20);

        // 2. Eager Load Rosters for the Paginated Dataset to avoid N+1 Queries
        $empIds = $attendances->pluck('emp_id')->unique()->toArray();
        $dates = $attendances->map(function ($a) {
            return $a->punch_in_time ? Carbon::parse($a->punch_in_time)->format('Y-m-d') : null;
        })->filter()->unique()->toArray();

        $rosters = DB::table('rl_employee_rosters_tbl')
            ->join('rl_shifts_tbl', 'rl_employee_rosters_tbl.shift_id', '=', 'rl_shifts_tbl.id')
            ->whereIn('rl_employee_rosters_tbl.employee_id', $empIds)
            ->whereIn('rl_employee_rosters_tbl.roster_date', $dates)
            ->select('rl_employee_rosters_tbl.*', 'rl_shifts_tbl.logout_time')
            ->get()
            ->keyBy(function ($r) {
                return $r->employee_id . '_' . $r->roster_date;
            });

        $settings = OfficeTiming::where('client_id', $user->id)->first();
        $allowedBreak = $settings ? ($settings->break_time ?? 30) : 30;

        $attendances->getCollection()->transform(function ($attendance) use ($allowedBreak, $rosters) {
            $attendance->working_hours = '--';
            $defaultLogoutTime = '17:00:00';

            $dateStr = $attendance->punch_in_time ? Carbon::parse($attendance->punch_in_time)->format('Y-m-d') : null;

            // 3. Fetch specific rostered shift for THIS exact punch date
            if ($dateStr) {
                $rosterKey = $attendance->emp_id . '_' . $dateStr;
                if ($rosters->has($rosterKey)) {
                    $defaultLogoutTime = $rosters->get($rosterKey)->logout_time;
                }
            }

            $attendance->default_logout = Carbon::parse($defaultLogoutTime)->format('H:i:s');

            if (!$attendance->punch_in_time)
                return $attendance;

            $logs = AttendanceLog::where('client_id', $attendance->client_id)
                ->where('emp_id', $attendance->emp_id)
                ->where('logical_date', $dateStr) // CRITICAL: Use logical_date from Phase 1 update
                ->orderBy('punch_time')
                ->get();

            if ($logs->count() < 2)
                return $attendance;

            $firstPunch = Carbon::parse($logs->first()->punch_time);
            $lastPunch = Carbon::parse($logs->last()->punch_time);
            $totalMinutes = $firstPunch->diffInMinutes($lastPunch);
            $totalBreak = 0;

            for ($i = 1; $i < $logs->count() - 1; $i += 2) {
                $breakStart = Carbon::parse($logs[$i]->punch_time);
                $breakEnd = Carbon::parse($logs[$i + 1]->punch_time);
                $totalBreak += $breakStart->diffInMinutes($breakEnd);
            }

            if ($totalBreak > $allowedBreak) {
                $totalMinutes -= ($totalBreak - $allowedBreak);
            }

            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            $attendance->working_hours = "{$hours}h {$minutes}m";

            return $attendance;
        });

        $groupedAttendances = $attendances->getCollection()->groupBy(function ($attendance) {
            return $attendance->employee->department->department_name ?? 'No Department';
        });

        return view('client.manageAttendance', compact('attendances', 'groupedAttendances', 'settings'));
    }

    public function manageAttendance(Request $request, PayrollService $payrollService)
    {
        $this->authorizeAccess();
        $user = $this->getLoggedInUser();

        $query = Attendance::with(['employee.department', 'employee.designationInfo'])
            ->select('rl_attendance_tbl.*')
            // ->join('rl_employees_tbl', 'rl_attendance_tbl.emp_id', '=', 'rl_employees_tbl.id')
            ->join('rl_employees_tbl', function ($join) {
                $join->on('rl_attendance_tbl.emp_id', '=', 'rl_employees_tbl.id')
                    ->where('rl_employees_tbl.status', 1)
                    ->where('rl_employees_tbl.trash', '!=', 1);
            })
            ->leftJoin('rl_departments_tbl', 'rl_employees_tbl.department_id', '=', 'rl_departments_tbl.id')
            ->where('rl_attendance_tbl.client_id', $user->id);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('rl_employees_tbl.employee_name', 'like', "%{$search}%")
                    ->orWhere('rl_employees_tbl.employee_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->where('rl_attendance_tbl.punch_in_time', '>=', Carbon::createFromFormat('d-m-Y', $request->from_date)->startOfDay());
        }

        if ($request->filled('to_date')) {
            $query->where('rl_attendance_tbl.punch_in_time', '<=', Carbon::createFromFormat('d-m-Y', $request->to_date)->endOfDay());
        }

        $attendances = $query
            ->orderByRaw('ISNULL(rl_departments_tbl.priority), rl_departments_tbl.priority ASC')
            ->orderBy('rl_departments_tbl.department_name', 'asc')
            ->orderBy('rl_attendance_tbl.punch_in_time', 'desc')
            ->paginate(20);

        $empIds = $attendances->pluck('emp_id')->unique()->toArray();
        $dates = $attendances->map(function ($a) {
            return $a->punch_in_time ? Carbon::parse($a->punch_in_time)->format('Y-m-d') : null;
        })->filter()->unique()->toArray();

        $rosters = DB::table('rl_employee_rosters_tbl')
            ->join('rl_shifts_tbl', 'rl_employee_rosters_tbl.shift_id', '=', 'rl_shifts_tbl.id')
            ->whereIn('rl_employee_rosters_tbl.employee_id', $empIds)
            ->whereIn('rl_employee_rosters_tbl.roster_date', $dates)
            ->select('rl_employee_rosters_tbl.*', 'rl_shifts_tbl.logout_time')
            ->get()
            ->keyBy(function ($r) {
                return $r->employee_id . '_' . $r->roster_date;
            });

        // Fetch OT Maps to flag double shifts
        $otRecords = EmployeeOT::where('client_id', $user->id)
            ->whereIn('employee_id', $empIds)
            ->get();

        $otDatesMap = [];
        foreach ($otRecords as $ot) {
            if ($ot->remarks) {
                preg_match_all('/\d{4}-\d{2}-\d{2}/', $ot->remarks, $matches);
                foreach ($matches[0] as $d) {
                    $otDatesMap[$ot->employee_id . '_' . $d] = true;
                }
            }
        }

        $settings = OfficeTiming::where('client_id', $user->id)->first();
        $allowedBreak = $settings ? ($settings->break_time ?? 30) : 30;

        $attendances->getCollection()->transform(function ($attendance) use ($allowedBreak, $rosters, $otDatesMap) {
            $attendance->working_hours = '--';
            $defaultLogoutTime = '17:00:00';

            $dateStr = $attendance->punch_in_time ? Carbon::parse($attendance->punch_in_time)->format('Y-m-d') : null;

            // Flag if this date is tracked as an Extra/OT Shift
            $attendance->is_ot = $dateStr ? isset($otDatesMap[$attendance->emp_id . '_' . $dateStr]) : false;

            if ($dateStr) {
                $rosterKey = $attendance->emp_id . '_' . $dateStr;
                if ($rosters->has($rosterKey)) {
                    $defaultLogoutTime = $rosters->get($rosterKey)->logout_time;
                }
            }

            $attendance->default_logout = Carbon::parse($defaultLogoutTime)->format('H:i:s');

            if (!$attendance->punch_in_time)
                return $attendance;

            $logs = AttendanceLog::where('client_id', $attendance->client_id)
                ->where('emp_id', $attendance->emp_id)
                ->where('logical_date', $dateStr)
                ->orderBy('punch_time')
                ->get();

            if ($logs->count() < 2)
                return $attendance;

            $firstPunch = Carbon::parse($logs->first()->punch_time);
            $lastPunch = Carbon::parse($logs->last()->punch_time);
            $totalMinutes = $firstPunch->diffInMinutes($lastPunch);
            $totalBreak = 0;

            for ($i = 1; $i < $logs->count() - 1; $i += 2) {
                $breakStart = Carbon::parse($logs[$i]->punch_time);
                $breakEnd = Carbon::parse($logs[$i + 1]->punch_time);
                $totalBreak += $breakStart->diffInMinutes($breakEnd);
            }

            if ($totalBreak > $allowedBreak) {
                $totalMinutes -= ($totalBreak - $allowedBreak);
            }

            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            $attendance->working_hours = "{$hours}h {$minutes}m";

            return $attendance;
        });

        $groupedAttendances = $attendances->getCollection()->groupBy(function ($attendance) {
            return $attendance->employee->department->department_name ?? 'No Department';
        });

        return view('client.manageAttendance', compact('attendances', 'groupedAttendances', 'settings'));
    }

    public function createAttendance()
    {
        $this->authorizeAccess();

        $user = $this->getLoggedInUser();

        $employees = Employee::where(
            'client_id',
            $user->id
        )
            ->where('status', 1)
            ->where('trash', 0)
            ->orderBy('employee_name')
            ->get();

        return view(
            'client.addAttendance',
            compact('employees')
        );
    }
    public function storeAttendance(Request $request)
    {
        try {

            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            $validator = Validator::make(
                $request->all(),
                [
                    'emp_id' => 'required|exists:rl_employees_tbl,id',
                    'punch_in_time' => 'required|date',
                    'punch_out_time' => 'nullable|date|after:punch_in_time',
                ],
                [
                    'emp_id.required' => 'Please select an employee.',
                    'emp_id.exists' => 'Selected employee does not exist.',
                    'punch_in_time.required' => 'Punch In Time is required.',
                    'punch_out_time.after' => 'Punch Out Time must be greater than Punch In Time.',
                ]
            );

            if ($validator->fails()) {

                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $attendanceDate = Carbon::parse(
                $request->punch_in_time
            )->toDateString();

            $alreadyExists = Attendance::where(
                'emp_id',
                $request->emp_id
            )
                ->whereDate(
                    'punch_in_time',
                    $attendanceDate
                )
                ->exists();

            if ($alreadyExists) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Attendance already exists for this employee on this date.'
                    );
            }

            Attendance::create([
                'emp_id' => $request->emp_id,
                'punch_in_time' => $request->punch_in_time,
                'punch_out_time' => $request->punch_out_time,
                'client_id' => $user->id
            ]);

            return redirect()
                ->route('manageAttendance')
                ->with(
                    'success',
                    'Attendance added successfully.'
                );
        } catch (\Throwable $th) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $th->getMessage()
                );
        }
    }
    public function storeBulkAttendance(Request $request)
    {
        try {

            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            $validator = Validator::make(
                $request->all(),
                [
                    'attendance' => 'required|array',
                ]
            );

            if ($validator->fails()) {

                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            foreach ($request->attendance as $empId => $attendance) {

                if (
                    empty($attendance['punch_in_time']) &&
                    empty($attendance['punch_out_time'])
                ) {
                    continue;
                }

                if (
                    !empty($attendance['punch_in_time']) &&
                    !empty($attendance['punch_out_time']) &&
                    strtotime($attendance['punch_out_time']) <=
                    strtotime($attendance['punch_in_time'])
                ) {

                    return back()
                        ->withInput()
                        ->with(
                            'error',
                            'Punch Out Time must be greater than Punch In Time.'
                        );
                }

                $attendanceDate = Carbon::parse(
                    $attendance['punch_in_time']
                )->toDateString();

                $alreadyExists = Attendance::where(
                    'emp_id',
                    $empId
                )
                    ->whereDate(
                        'punch_in_time',
                        $attendanceDate
                    )
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                $attendanceRecord = Attendance::create([
                    'emp_id' => $empId,
                    'punch_in_time' => !empty($attendance['punch_in_time'])
                        ? $attendance['punch_in_time']
                        : null,
                    'punch_out_time' => !empty($attendance['punch_out_time'])
                        ? $attendance['punch_out_time']
                        : null,
                    'client_id' => $user->id
                ]);

                // Store Punch In Log
                if (!empty($attendance['punch_in_time'])) {
                    AttendanceLog::create([
                        'client_id' => $user->id,
                        'emp_id' => $empId,
                        'punch_time' => $attendance['punch_in_time'],
                    ]);
                }

                // Store Punch Out Log
                if (!empty($attendance['punch_out_time'])) {
                    AttendanceLog::create([
                        'client_id' => $user->id,
                        'emp_id' => $empId,
                        'punch_time' => $attendance['punch_out_time'],
                    ]);
                }
            }

            return redirect()
                ->route('manageAttendance')
                ->with(
                    'success',
                    'Bulk attendance added successfully.'
                );
        } catch (\Throwable $th) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $th->getMessage()
                );
        }
    }

    public function checkOutAttendance(Request $request, $id)
    {

        try {

            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            $attendance = Attendance::where(
                'client_id',
                $user->id
            )
                ->findOrFail($id);

            if ($attendance->punch_out_time) {

                return back()->with(
                    'error',
                    'Employee already checked out.'
                );
            }

            if ($request->filled('use_current_time')) {

                $punchOutTime = now();
            } else {

                $request->validate([
                    'custom_checkout_time' => 'required'
                ]);

                $punchOutTime = Carbon::parse(
                    $request->custom_checkout_time
                );
            }

            if ($punchOutTime <= Carbon::parse($attendance->punch_in_time)) {

                return back()->with(
                    'error',
                    'Check Out Time must be greater than Check In Time.'
                );
            }

            $attendance->update([
                'punch_out_time' => $punchOutTime
            ]);

            // 2. INSERT MISSING LOG: Create the AttendanceLog for the checkout so Working Hours calculate properly
            AttendanceLog::create([
                'client_id' => $user->id,
                'emp_id' => $attendance->emp_id,
                'punch_time' => $punchOutTime,
            ]);

            return back()->with(
                'success',
                'Check Out marked successfully.'
            );
        } catch (\Throwable $th) {
            $th->getMessage();
            dd($th->getMessage());
            exit;

            return back()->with(
                'error',
                $th->getMessage()
            );
        }
    }
    public function bulkAttendance()
    {
        try {

            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            $employees = Employee::where(
                'client_id',
                $user->id
            )
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('employee_name')
                ->get();

            return view(
                'client.bulkAttendance',
                compact('employees')
            );
        } catch (\Throwable $th) {

            return back()
                ->with(
                    'error',
                    $th->getMessage()
                );
        }
    }
    public function editAttendance($id)
    {
        $this->authorizeAccess();

        $user = $this->getLoggedInUser();

        $attendance = Attendance::where(
            'client_id',
            $user->id
        )
            ->findOrFail($id);

        $employees = Employee::where(
            'client_id',
            $user->id
        )
            ->where('status', 1)
            ->where('trash', 0)
            ->orderBy('employee_name')
            ->get();

        return view(
            'client.editAttendance',
            compact(
                'attendance',
                'employees'
            )
        );
    }
    public function updateAttendance(Request $request, $id)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $attendance = Attendance::where('client_id', $user->id)->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'emp_id' => 'required|exists:rl_employees_tbl,id',
                'punch_in_time' => 'required',
                'punch_out_time' => 'nullable',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            // Parse Indian Date Format (d-m-Y h:i A) securely into Native Timestamps
            $punchIn = Carbon::createFromFormat('d-m-Y h:i A', $request->punch_in_time);
            $punchOut = $request->filled('punch_out_time') ? Carbon::createFromFormat('d-m-Y h:i A', $request->punch_out_time) : null;

            if ($punchOut && $punchOut->lte($punchIn)) {
                return back()->withErrors(['punch_out_time' => 'Punch Out Time must be greater than Punch In Time.'])->withInput();
            }

            $originalDate = Carbon::parse($attendance->punch_in_time)->toDateString();

            // 1. Update Main Attendance Record
            $attendance->update([
                'emp_id' => $request->emp_id,
                'punch_in_time' => $punchIn->format('Y-m-d H:i:s'),
                'punch_out_time' => $punchOut ? $punchOut->format('Y-m-d H:i:s') : null,
            ]);

            // 2. Synchronize Attendance Logs to Fix Working Hours Calculation
            $logs = AttendanceLog::where('client_id', $user->id)
                ->where('emp_id', $request->emp_id)
                ->whereDate('punch_time', $originalDate)
                ->orderBy('punch_time', 'asc')
                ->get();

            if ($logs->isEmpty()) {
                // If logs are completely missing, recreate them
                AttendanceLog::create([
                    'client_id' => $user->id,
                    'emp_id' => $request->emp_id,
                    'punch_time' => $punchIn->format('Y-m-d H:i:s'),
                ]);

                if ($punchOut) {
                    AttendanceLog::create([
                        'client_id' => $user->id,
                        'emp_id' => $request->emp_id,
                        'punch_time' => $punchOut->format('Y-m-d H:i:s'),
                    ]);
                }
            } else {
                // Safely update the first log (Punch In)
                $firstLog = $logs->first();
                $firstLog->update([
                    'punch_time' => $punchIn->format('Y-m-d H:i:s')
                ]);

                if ($punchOut) {
                    // Update or append the last log (Punch Out)
                    if ($logs->count() > 1) {
                        $lastLog = $logs->last();
                        $lastLog->update([
                            'punch_time' => $punchOut->format('Y-m-d H:i:s')
                        ]);
                    } else {
                        AttendanceLog::create([
                            'client_id' => $user->id,
                            'emp_id' => $request->emp_id,
                            'punch_time' => $punchOut->format('Y-m-d H:i:s'),
                        ]);
                    }
                } else {
                    // If the user deliberately removes the punch out time, delete the latest log
                    if ($logs->count() > 1) {
                        $lastLog = $logs->last();
                        $lastLog->delete();
                    }
                }
            }

            return redirect()
                ->route('manageAttendance')
                ->with('success', 'Attendance updated successfully.');

        } catch (\Throwable $th) {
            return back()
                ->withInput()
                ->with('error', $th->getMessage());
        }
    }

    public function deleteAttendance($id)
    {
        try {

            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            Attendance::where(
                'client_id',
                $user->id
            )
                ->where('id', $id)
                ->delete();

            return back()
                ->with(
                    'success',
                    'Attendance deleted successfully.'
                );
        } catch (\Throwable $th) {

            return back()
                ->with(
                    'error',
                    'Failed to delete attendance.'
                );
        }
    }
    public function storeOfficeTiming(Request $request)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();
            $validator = Validator::make(
                $request->all(),
                [
                    'start_time' => 'required',
                    'end_time' => 'required',
                    'grace_time' => 'required|numeric|min:0',
                    'half_day_limit' => 'required|numeric|min:1'
                ]
            );
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            OfficeTiming::updateOrCreate(
                ['client_id' => $user->id],
                [
                    'start_time' => date('H:i:s', strtotime($request->start_time)),
                    'end_time' => date('H:i:s', strtotime($request->end_time)),
                    'grace_time' => $request->grace_time,
                    'half_day_limit' => $request->half_day_limit,
                    'updated_by' => $user->id,
                    'created_by' => $user->id,
                ]
            );
            return back()->with('success', 'Office timing saved successfully.');
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function monthlyAttendance(Request $request, PayrollService $payrollService)
    {
        $this->authorizeAccess();

        $user = $this->getLoggedInUser();
        $month = (int) ($request->month ?? date('m'));
        $year = (int) ($request->year ?? date('Y'));
        $departmentId = $request->department_id;

        $departments = Department::where('client_id', $user->id)
            ->where('status', 1)
            ->where('trash', 0)
            ->orderBy('department_name', 'asc')
            ->get();

        // Delegate monthly array creation strictly to the PayrollService
        $data = $payrollService->getMonthlyAttendanceGrid($user->id, $month, $year, $departmentId);

        return view('client.monthlyAttendance', [
            'groupedEmployees' => $data['groupedEmployees'],
            'attendanceStatus' => $data['attendanceStatus'],
            'daysCount' => $data['daysInMonth'],
            'month' => $month,
            'year' => $year,
            'departments' => $departments,
            'selectedDepartment' => $departmentId
        ]);
    }

    public function managePayrolljok(Request $request, PayrollService $payrollService)
    {
        try {
            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            $month = $request->month ?? date('m');
            $year = $request->year ?? date('Y');

            // 1. Eager load both department and designationInfo
            $employees = Employee::with(['department', 'designationInfo'])
                ->where('client_id', $user->id)
                ->where('status', 1)
                ->where('trash', 0)
                ->get()
                ->sortBy(function ($emp) {
                    // 2. Sort by Dept Priority -> Designation Name -> Employee Name
                    $priority = $emp->department ? $emp->department->priority : 99999;
                    $designation = $emp->designationInfo ? $emp->designationInfo->designation_name : 'ZZZZZ';

                    return sprintf('%05d-%s-%s', $priority, $designation, $emp->employee_name);
                });

            $payrollData = [];

            foreach ($employees as $employee) {
                $data = $payrollService->calculatePayroll($employee, $month, $year);

                if (!empty($data['error'])) {
                    continue;
                }

                $deptName = $employee->department ? $employee->department->department_name : 'No Department';

                // Group data by Department Name
                $payrollData[$deptName][] = [
                    'id' => $employee->id,
                    'employee_name' => $employee->employee_name,
                    'employee_id' => $employee->employee_id,

                    // 3. Include designation in the data array
                    'designation_name' => $employee->designationInfo->designation_name ?? '--',

                    'present_days' => $data['present_days'],
                    'absent_days' => $data['absent_days'],
                    'half_days' => $data['half_days'],
                    'late_days' => $data['late_days'],
                    'week_offs' => $data['week_offs'],
                    'holidays' => $data['holidays'],
                    'paid_leaves' => $data['paid_leave_days'],
                    'working_days' => $data['working_days'],
                    'gross_salary' => $data['gross_salary'],
                    'lop_days' => $data['lop_days'],
                    'lop_amount' => $data['lop_amount'],
                    'other_deductions' => $data['total_deductions'],
                    'net_salary' => $data['net_salary'],

                    // --- FIELDS EXPOSED FOR BREAKDOWN TOOLTIPS ---
                    'per_day_salary' => $data['per_day_salary'] ?? 0,
                    'extra_leave_days' => $data['extra_leave_days'] ?? 0,
                    'deduction_rows' => $data['deduction_rows'] ?? [],
                ];
            }

            return view('client.managePayroll', compact(
                'payrollData',
                'month',
                'year'
            ));
        } catch (\Exception $e) {
            Log::error('Manage Payroll Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'month' => $request->month,
                'year' => $request->year,
                'client_id' => $user->id ?? null,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong while loading payroll data.');
        }
    }

    public function managePayroll(Request $request, PayrollService $payrollService)
    {
        try {
            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            $month = $request->month ?? date('m');
            $year = $request->year ?? date('Y');

            // ADDED: October 2026 one-time deduction-exception banner message for the summary table
            $deductionExceptionMessage = ((int) $month === 10 && (int) $year === 2026)
                ? 'Deductions for Late Logins and Half Days have not been applied to this month\'s payroll, as employees were not informed of this policy before the start of the October 2026 payroll cycle. This policy will be enforced from November 2026 onward.'
                : null;

            // 1. Eager load both department and designationInfo
            $employees = Employee::with(['department', 'designationInfo'])
                ->where('client_id', $user->id)
                ->where('status', 1)
                ->where('trash', 0)
                ->get()
                ->sortBy(function ($emp) {
                    // 2. Sort by Dept Priority -> Designation Name -> Employee Name
                    $priority = $emp->department ? $emp->department->priority : 99999;
                    $designation = $emp->designationInfo ? $emp->designationInfo->designation_name : 'ZZZZZ';

                    return sprintf('%05d-%s-%s', $priority, $designation, $emp->employee_name);
                });

            $payrollData = [];

            foreach ($employees as $employee) {
                $data = $payrollService->calculatePayroll($employee, $month, $year);

                if (!empty($data['error'])) {
                    continue;
                }

                $deptName = $employee->department ? $employee->department->department_name : 'No Department';

                // Group data by Department Name
                $payrollData[$deptName][] = [
                    'id' => $employee->id,
                    'employee_name' => $employee->employee_name,
                    'employee_id' => $employee->employee_id,

                    // 3. Include designation in the data array
                    'designation_name' => $employee->designationInfo->designation_name ?? '--',

                    'present_days' => $data['present_days'],
                    'absent_days' => $data['absent_days'],
                    'half_days' => $data['half_days'],
                    'late_days' => $data['late_days'],
                    'week_offs' => $data['week_offs'],
                    'holidays' => $data['holidays'],
                    'paid_leaves' => $data['paid_leave_days'],
                    'working_days' => $data['working_days'],
                    'gross_salary' => $data['gross_salary'],
                    'lop_days' => $data['lop_days'],
                    'lop_amount' => $data['lop_amount'],
                    'other_deductions' => $data['total_deductions'],
                    'net_salary' => $data['net_salary'],

                    // --- OVERTIME (date-wise or manual) ---
                    'ot_days' => $data['ot_days'] ?? 0,
                    'ot_amount' => $data['ot_amount'] ?? 0,
                    'ot_hours_formatted' => $data['ot_hours_formatted'] ?? '0h 0m',
                    'ot_source' => $data['ot_source'] ?? 'manual',

                    // --- FIELDS EXPOSED FOR BREAKDOWN TOOLTIPS ---
                    'per_day_salary' => $data['per_day_salary'] ?? 0,
                    'extra_leave_days' => $data['extra_leave_days'] ?? 0,
                    'deduction_rows' => $data['deduction_rows'] ?? [],
                ];
            }

            return view('client.managePayroll', compact(
                'payrollData',
                'month',
                'year',
                'deductionExceptionMessage' // ADDED
            ));
        } catch (\Exception $e) {
            Log::error('Manage Payroll Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'month' => $request->month,
                'year' => $request->year,
                'client_id' => $user->id ?? null,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Something went wrong while loading payroll data.');
        }
    }

    public function manageOfficeTimings()
    {
        $this->authorizeAccess();

        $user = $this->getLoggedInUser();

        // Standard client timing
        $settings = OfficeTiming::where('client_id', $user->id)
            ->whereNull('dept_id')
            ->first();

        // Department-wise timings - only active departments
        $departmentTimings = OfficeTiming::where('client_id', $user->id)
            ->whereNotNull('dept_id')
            ->whereHas('department', function ($query) {
                $query->where('status', 1)
                    ->where('trash', 0);
            })
            ->with('department')
            ->get();

        return view(
            'client.manageOfficeTimings',
            compact('settings', 'departmentTimings')
        );
    }
    public function createOfficeTimings()
    {
        $this->authorizeAccess();

        $user = $this->getLoggedInUser();

        // Standard / default office timing
        $settings = OfficeTiming::where('client_id', $user->id)
            ->whereNull('dept_id')
            ->first();

        // Department-wise timings
        $departmentTimings = OfficeTiming::where('client_id', $user->id)
            ->whereNotNull('dept_id')
            ->with('department')
            ->get();

        // All departments of this client
        $departments = Department::where('client_id', $user->id)
            ->where('status', 1)
            ->where('trash', 0)
            ->get();

        return view(
            'client.createOfficeTimings',
            compact(
                'settings',
                'departmentTimings',
                'departments'
            )
        );
    }

    public function saveOfficeTiming(Request $request)
    {
        try {

            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            $request->validate([
                'default_start_time' => 'required',
                'default_end_time' => 'required',
                'default_grace_time' => 'required|numeric|min:0',
                'default_half_day_limit' => 'required|numeric|min:1',

                'department_ids' => 'nullable|array',

                'start_time' => 'nullable|array',
                'end_time' => 'nullable|array',
                'grace_time' => 'nullable|array',
                'half_day_limit' => 'nullable|array',
            ]);

            OfficeTiming::updateOrCreate(
                [
                    'client_id' => $user->id,
                    'dept_id' => null,
                ],
                [
                    'start_time' => Carbon::parse(
                        $request->default_start_time
                    )->format('H:i:s'),

                    'end_time' => Carbon::parse(
                        $request->default_end_time
                    )->format('H:i:s'),

                    'grace_time' => $request->default_grace_time,

                    'half_day_limit' => $request->default_half_day_limit,

                    'updated_by' => $user->id,
                ]
            );

            foreach ($request->department_ids ?? [] as $deptId) {

                OfficeTiming::updateOrCreate(
                    [
                        'client_id' => $user->id,
                        'dept_id' => $deptId,
                    ],
                    [
                        'start_time' => Carbon::parse(
                            $request->start_time[$deptId]
                        )->format('H:i:s'),

                        'end_time' => Carbon::parse(
                            $request->end_time[$deptId]
                        )->format('H:i:s'),

                        'grace_time' => $request->grace_time[$deptId],

                        'half_day_limit' => $request->half_day_limit[$deptId],

                        'updated_by' => $user->id,
                    ]
                );

            }


            return redirect()
                ->route('manageOfficeTimings')
                ->with(
                    'success',
                    'Office timings saved successfully.'
                );

        } catch (\Throwable $th) {

            return back()
                ->withInput()
                ->with('error', $th->getMessage());
        }
    }

    /* Query and build a structured Matrix with department grouping & status calculations */
    private function buildAttendanceMatrix(Request $request)
    {
        $user = $this->getLoggedInUser();

        // 1. Resolve Date Range
        $fromStr = $request->filled('from_date') ? $request->from_date : date('d-m-Y');
        $toStr = $request->filled('to_date') ? $request->to_date : date('d-m-Y');

        try {
            $startDate = Carbon::createFromFormat('d-m-Y', $fromStr)->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', $toStr)->endOfDay();
        } catch (Exception $e) {
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
        }

        if ($startDate->gt($endDate)) {
            $temp = $startDate;
            $startDate = $endDate->copy()->startOfDay();
            $endDate = $temp->copy()->endOfDay();
        }

        $dateRange = [];
        $curr = $startDate->copy();
        while ($curr->lte($endDate)) {
            $dateRange[] = $curr->format('Y-m-d');
            $curr->addDay();
        }

        // 2. Fetch Active Employees scoped to Client
        $empQuery = Employee::with(['department', 'designationInfo'])
            ->where('client_id', $user->id)
            ->where('trash', 0)
            ->where('status', 1);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $empQuery->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $employees = $empQuery->orderBy('department_id', 'asc')
            ->orderBy('employee_name', 'asc')
            ->get();

        // 3. Eager load Punches, Approved Leaves, Holidays, and Policies
        $attendances = Attendance::where('client_id', $user->id)
            ->where('punch_in_time', '>=', $startDate)
            ->where('punch_in_time', '<=', $endDate)
            ->get();

        $attendanceMap = [];
        foreach ($attendances as $att) {
            if ($att->punch_in_time) {
                $dKey = Carbon::parse($att->punch_in_time)->format('Y-m-d');
                $attendanceMap[$att->emp_id][$dKey] = $att;
            }
        }

        $approvedLeaves = \App\Models\EmployeeLeaveDate::whereHas('leave', function ($q) use ($user) {
            $q->where('client_id', $user->id)->where('status', 1)->where('trash', 0);
        })
            ->where('date', '>=', $startDate->format('Y-m-d'))
            ->where('date', '<=', $endDate->format('Y-m-d'))
            ->with('leave')
            ->get();

        $leavesMap = [];
        foreach ($approvedLeaves as $al) {
            $dateKey = Carbon::parse($al->date)->format('Y-m-d');
            $leavesMap[$al->leave->employee_id . '_' . $dateKey] = (int) $al->is_paid;
        }

        $holidays = Holiday::with('departments')
            ->where('client_id', $user->id)
            ->where('status', 1)
            ->where('trash', 0)
            ->where('date', '>=', $startDate->format('Y-m-d'))
            ->where('date', '<=', $endDate->format('Y-m-d'))
            ->get();

        $defaultTiming = OfficeTiming::where('client_id', $user->id)->whereNull('dept_id')->first();
        $deptTimings = OfficeTiming::where('client_id', $user->id)->whereNotNull('dept_id')->get()->keyBy('dept_id');
        $departmentPolicies = PaidLeave::where('client_id', $user->id)->where('status', 1)->where('trash', 0)->get()->keyBy('department_id');

        // 4. Construct Grouped Dataset by Department
        $departments = [];
        foreach ($employees as $emp) {
            $deptName = $emp->department->department_name ?? 'General Administration';
            if (!isset($departments[$deptName])) {
                $departments[$deptName] = [];
            }

            $timing = $deptTimings->get($emp->department_id) ?? $defaultTiming;
            $deptPolicy = $departmentPolicies->get($emp->department_id);

            // Department Holidays
            $employeeHolidays = $holidays->filter(function ($h) use ($emp) {
                return $h->departments->isEmpty() || $h->departments->contains('id', $emp->department_id);
            })->map(function ($h) {
                return Carbon::parse($h->date)->format('Y-m-d');
            })->toArray();

            // Department Week-offs
            $weekOffType = $deptPolicy->week_off_type ?? 'weekly';
            $weekOffCount = (int) ($deptPolicy->week_offs ?? 1);

            $empRow = [
                'name' => $emp->employee_name,
                'id_code' => $emp->employee_id ?? 'N/A',
                'designation' => $emp->designationInfo->designation_name ?? '--', // <--- FIXED
                'summary' => ['P' => 0, 'A' => 0, 'H' => 0, 'L' => 0, 'PL' => 0, 'UL' => 0, 'HOL' => 0, 'W' => 0],
                'days' => []
            ];

            foreach ($dateRange as $dStr) {
                $cDate = Carbon::parse($dStr);
                $att = $attendanceMap[$emp->id][$dStr] ?? null;
                $leaveKey = $emp->id . '_' . $dStr;

                // Priority 1: Physical Punch In
                if ($att && $att->punch_in_time) {
                    $inTime = Carbon::parse($att->punch_in_time);
                    $outTime = ($att->punch_out_time && $att->punch_out_time != $att->punch_in_time) ? Carbon::parse($att->punch_out_time) : null;
                    $status = 'P';

                    if ($timing) {
                        $start = Carbon::parse($dStr . ' ' . Carbon::parse($timing->start_time)->format('H:i:s'));
                        $grace = $start->copy()->addMinutes((int) ($timing->grace_time ?? 15));

                        if ($outTime) {
                            $worked = $inTime->diffInMinutes($outTime) / 60;
                            if ($worked < (float) ($timing->half_day_limit ?? 4)) {
                                $status = 'H';
                            }
                        }

                        if ($status !== 'H' && $inTime->gt($grace)) {
                            $status = 'L';
                        }
                    }

                    $empRow['summary'][$status]++;
                    if ($status === 'L') {
                        $empRow['summary']['P']++;
                    }

                    $empRow['days'][$dStr] = [
                        'status' => $status,
                        'in' => $inTime->format('h:i A'),
                        'out' => $outTime ? $outTime->format('h:i A') : '--'
                    ];
                    continue;
                }

                // Priority 2: Approved Leaves
                if (isset($leavesMap[$leaveKey])) {
                    $st = $leavesMap[$leaveKey] === 1 ? 'PL' : 'UL';
                    $empRow['summary'][$st]++;
                    $empRow['days'][$dStr] = ['status' => $st, 'in' => '--', 'out' => '--'];
                    continue;
                }

                // Priority 3: Holidays
                if (in_array($dStr, $employeeHolidays)) {
                    $empRow['summary']['HOL']++;
                    $empRow['days'][$dStr] = ['status' => 'HOL', 'in' => '--', 'out' => '--'];
                    continue;
                }

                // Priority 4: Week Offs
                $isWeekOff = false;
                if ($weekOffCount > 0) {
                    if ($weekOffType === 'weekly') {
                        $isWeekOff = ($weekOffCount === 1) ? $cDate->isSunday() : ($cDate->isSaturday() || $cDate->isSunday());
                    } else {
                        $isWeekOff = $cDate->isSunday(); // Fallback for monthly quota days
                    }
                }

                if ($isWeekOff) {
                    $empRow['summary']['W']++;
                    $empRow['days'][$dStr] = ['status' => 'W', 'in' => '--', 'out' => '--'];
                    continue;
                }

                // Priority 5: Absent
                $empRow['summary']['A']++;
                $empRow['days'][$dStr] = ['status' => 'A', 'in' => '--', 'out' => '--'];
            }

            $departments[$deptName][] = $empRow;
        }

        return [
            'startDate' => $startDate->format('d M Y'),
            'endDate' => $endDate->format('d M Y'),
            'dateRange' => $dateRange,
            'departments' => $departments,
            'user' => $user
        ];
    }

    /* Export Attendance Matrix to Excel */
    public function exportAttendanceExcel(Request $request)
    {
        $this->authorizeAccess();
        $matrix = $this->buildAttendanceMatrix($request);

        $filename = 'Attendance_Matrix_' . Carbon::now()->format('d_M_Y_His') . '.xls';

        return response()->streamDownload(function () use ($matrix) {
            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
            echo '<style>
                body { font-family: Arial, sans-serif; font-size: 11px; }
                .title-header { font-size: 15px; font-weight: bold; color: #0f172a; text-align: left; }
                .legend-table { border: 1px solid #cbd5e1; margin-bottom: 12px; }
                .legend-table td { padding: 4px 8px; font-size: 10px; border: none; }
                .dept-banner { background-color: #0f172a; color: #ffffff; font-size: 12px; font-weight: bold; padding: 7px; }
                th { background-color: #f1f5f9; color: #1e293b; font-weight: bold; border: 1px solid #cbd5e1; text-align: center; }
                td { border: 1px solid #cbd5e1; vertical-align: middle; }
                .text-center { text-align: center; }
                .text-left { text-align: left; }
                .badge-p { color: #166534; font-weight: bold; }
                .badge-a { color: #991b1b; font-weight: bold; }
                .badge-h { color: #b45309; font-weight: bold; }
                .badge-l { color: #1d4ed8; font-weight: bold; }
                .badge-pl { color: #047857; font-weight: bold; }
                .badge-ul { color: #b91c1c; font-weight: bold; }
                .badge-hol { color: #0891b2; font-weight: bold; }
            </style></head><body>';

            echo '<table>';
            $totalCols = 12 + count($matrix['dateRange']);

            // Company Title
            echo '<tr><td colspan="' . $totalCols . '" class="title-header">' . htmlspecialchars($matrix['user']->client_name ?? 'PRIMS HRMS') . ' - Consolidated Attendance Register</td></tr>';
            echo '<tr><td colspan="' . $totalCols . '" style="color:#64748b; font-size:11px;">Duration: ' . $matrix['startDate'] . ' to ' . $matrix['endDate'] . ' | Report Generated: ' . Carbon::now()->format('d M Y, h:i A') . '</td></tr>';
            echo '<tr><td colspan="' . $totalCols . '" style="height:8px;"></td></tr>';

            // Explanatory Symbol Legend Block
            echo '<tr><td colspan="' . $totalCols . '" style="background:#f8fafc; font-weight:bold; border:1px solid #cbd5e1; padding:5px;">SYMBOL LEGEND & EXPLANATION:</td></tr>';
            echo '<tr><td colspan="' . $totalCols . '" style="background:#f8fafc; border:1px solid #cbd5e1; padding:5px; font-size:10px;">
                    <strong>[P]</strong> = Present &nbsp;|&nbsp; 
                    <strong>[A]</strong> = Absent &nbsp;|&nbsp; 
                    <strong>[H]</strong> = Half Day &nbsp;|&nbsp; 
                    <strong>[L]</strong> = Late Login &nbsp;|&nbsp; 
                    <strong>[PL]</strong> = Paid Leave &nbsp;|&nbsp; 
                    <strong>[UL]</strong> = Unpaid Leave &nbsp;|&nbsp; 
                    <strong>[HOL]</strong> = Holiday &nbsp;|&nbsp; 
                    <strong>[W]</strong> = Week Off
                  </td></tr>';
            echo '<tr><td colspan="' . $totalCols . '" style="height:12px;"></td></tr>';

            foreach ($matrix['departments'] as $deptName => $employees) {
                echo '<tr><td colspan="' . $totalCols . '" class="dept-banner">DEPARTMENT: ' . strtoupper(htmlspecialchars($deptName)) . ' (' . count($employees) . ' Staff)</td></tr>';

                echo '<tr>';
                echo '<th style="width:35px;">#</th>';
                echo '<th style="width:180px; text-align:left;">Employee Name</th>';
                echo '<th style="width:80px;">Emp ID</th>';
                echo '<th style="width:130px; text-align:left;">Designation</th>';
                echo '<th style="background:#dcfce7; color:#166534; width:35px;">P</th>';
                echo '<th style="background:#fee2e2; color:#991b1b; width:35px;">A</th>';
                echo '<th style="background:#fef3c7; color:#b45309; width:35px;">H</th>';
                echo '<th style="background:#dbeafe; color:#1d4ed8; width:35px;">L</th>';
                echo '<th style="background:#d1fae5; color:#065f46; width:35px;">PL</th>';
                echo '<th style="background:#ffe4e6; color:#9f1239; width:35px;">UL</th>';
                echo '<th style="background:#cffafe; color:#155e75; width:38px;">HOL</th>';
                echo '<th style="background:#f1f5f9; color:#475569; width:35px;">W</th>';

                foreach ($matrix['dateRange'] as $dStr) {
                    $cObj = Carbon::parse($dStr);
                    $style = $cObj->isSunday() ? 'background-color:#fee2e2; color:#991b1b;' : '';
                    echo '<th style="width:130px; ' . $style . '">' . $cObj->format('d M (D)') . '</th>';
                }
                echo '</tr>';

                $idx = 1;
                foreach ($employees as $emp) {
                    echo '<tr>';
                    echo '<td class="text-center">' . $idx++ . '</td>';
                    echo '<td class="text-left" style="font-weight:600;">' . htmlspecialchars($emp['name']) . '</td>';
                    echo '<td class="text-center">' . htmlspecialchars($emp['id_code']) . '</td>';

                    // SAFELY FETCH DESIGNATION NAME FROM RELATIONSHIP OR FALLBACK TO STRING
                    $designationName = $emp['designation_info']['designation_name'] ?? $emp['designationInfo']['designation_name'] ?? $emp['designation'] ?? '--';
                    echo '<td class="text-left">' . htmlspecialchars($designationName) . '</td>';

                    echo '<td class="text-center" style="background:#f0fdf4; font-weight:bold; color:#166534;">' . $emp['summary']['P'] . '</td>';
                    echo '<td class="text-center" style="background:#fef2f2; font-weight:bold; color:#991b1b;">' . $emp['summary']['A'] . '</td>';
                    echo '<td class="text-center" style="background:#fffbeb; font-weight:bold; color:#b45309;">' . $emp['summary']['H'] . '</td>';
                    echo '<td class="text-center" style="background:#eff6ff; font-weight:bold; color:#1d4ed8;">' . $emp['summary']['L'] . '</td>';
                    echo '<td class="text-center" style="background:#ecfdf5; font-weight:bold; color:#047857;">' . $emp['summary']['PL'] . '</td>';
                    echo '<td class="text-center" style="background:#fff1f2; font-weight:bold; color:#be123c;">' . $emp['summary']['UL'] . '</td>';
                    echo '<td class="text-center" style="background:#ecfeff; font-weight:bold; color:#0e7490;">' . $emp['summary']['HOL'] . '</td>';
                    echo '<td class="text-center" style="background:#f8fafc; font-weight:bold; color:#475569;">' . $emp['summary']['W'] . '</td>';

                    foreach ($matrix['dateRange'] as $dStr) {
                        $day = $emp['days'][$dStr];
                        $st = $day['status'];

                        if ($st === 'P' || $st === 'L' || $st === 'H') {
                            $badge = ($st === 'H') ? 'badge-h' : (($st === 'L') ? 'badge-l' : 'badge-p');
                            echo '<td class="text-center" style="font-size:10px;"><span class="' . $badge . '">[' . $st . ']</span> ' . $day['in'] . ' - ' . $day['out'] . '</td>';
                        } elseif ($st === 'PL') {
                            echo '<td class="text-center" style="background:#f0fdf4;"><span class="badge-pl">PAID LEAVE</span></td>';
                        } elseif ($st === 'UL') {
                            echo '<td class="text-center" style="background:#fef2f2;"><span class="badge-ul">UNPAID LEAVE</span></td>';
                        } elseif ($st === 'HOL') {
                            echo '<td class="text-center" style="background:#ecfeff;"><span class="badge-hol">HOLIDAY</span></td>';
                        } elseif ($st === 'W') {
                            echo '<td class="text-center" style="color:#64748b; background:#f8fafc;">WEEK OFF</td>';
                        } else {
                            echo '<td class="text-center" style="background:#fff1f2;"><span class="badge-a">ABSENT</span></td>';
                        }
                    }
                    echo '</tr>';
                }

                echo '<tr><td colspan="' . $totalCols . '" style="height:12px; border:none;"></td></tr>';
            }

            echo '</table></body></html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /* Export Attendance Matrix to PDF (Forced File Download) */
    public function exportAttendancePdf(Request $request)
    {
        $this->authorizeAccess();
        $matrix = $this->buildAttendanceMatrix($request);

        $daysCount = count($matrix['dateRange']);
        $filename = 'Attendance_Register_' . date('d_M_Y_His') . '.pdf';

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            // Allocate A2 landscape (1684 x 1191 pt) for 15+ days to fit up to 31 dates without cutoff
            $paperSize = ($daysCount > 14) ? 'a2' : 'a3';

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('client.reports.attendance_matrix_pdf', compact('matrix'))
                ->setPaper($paperSize, 'landscape')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'sans-serif',
                    'dpi' => 110,
                ]);

            return $pdf->download($filename);
        }

        return response()->view('client.reports.attendance_matrix_pdf', compact('matrix'));
    }

    /**
     * ADDED: Show the form for entering/editing a single employee's attendance
     * across an entire month, day by day, with that day's rostered shift name
     * and timings shown for reference. Filling in every day is NOT required —
     * the admin can save only the days they have data for.
     */
    public function monthlyAttendanceEntry(Request $request)
    {
        $this->authorizeAccess();
        $user = $this->getLoggedInUser();

        $employees = Employee::where('client_id', $user->id)
            ->where('status', 1)
            ->where('trash', 0)
            ->orderBy('employee_name')
            ->get();

        $employeeId = $request->employee_id;
        $month = $request->month ?? date('m');
        $year = $request->year ?? date('Y');

        $employee = null;
        $days = [];

        if (!empty($employeeId)) {

            $employee = Employee::with(['department', 'designationInfo'])
                ->where('client_id', $user->id)
                ->where('id', $employeeId)
                ->first();

            if ($employee) {

                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

                // Roster for this employee this month, keyed by day (d)
                $rosters = DB::table('rl_employee_rosters_tbl')
                    ->leftJoin('rl_shifts_tbl', 'rl_employee_rosters_tbl.shift_id', '=', 'rl_shifts_tbl.id')
                    ->where('rl_employee_rosters_tbl.client_id', $user->id)
                    ->where('rl_employee_rosters_tbl.employee_id', $employee->id)
                    ->whereMonth('rl_employee_rosters_tbl.roster_date', $month)
                    ->whereYear('rl_employee_rosters_tbl.roster_date', $year)
                    ->select(
                        'rl_employee_rosters_tbl.roster_date',
                        'rl_employee_rosters_tbl.is_weekoff',
                        'rl_shifts_tbl.shift_name',
                        'rl_shifts_tbl.login_time',
                        'rl_shifts_tbl.logout_time'
                    )
                    ->get()
                    ->keyBy(function ($r) {
                        return Carbon::parse($r->roster_date)->format('d');
                    });

                // Existing attendance for this employee this month, keyed by day (d)
                $existingAttendance = Attendance::where('client_id', $user->id)
                    ->where('emp_id', $employee->id)
                    ->whereMonth('punch_in_time', $month)
                    ->whereYear('punch_in_time', $year)
                    ->get()
                    ->keyBy(function ($a) {
                        return Carbon::parse($a->punch_in_time)->format('d');
                    });

                for ($d = 1; $d <= $daysInMonth; $d++) {

                    $dayKey = sprintf('%02d', $d);
                    $date = Carbon::create((int) $year, (int) $month, $d);
                    $roster = $rosters->get($dayKey);
                    $attendance = $existingAttendance->get($dayKey);

                    $days[] = [
                        'day' => $d,
                        'date' => $date->format('Y-m-d'),
                        'day_name' => $date->format('D'),
                        'is_weekend' => $date->isSunday(),
                        'is_weekoff' => $roster ? (bool) $roster->is_weekoff : false,
                        'shift_name' => ($roster && !$roster->is_weekoff) ? $roster->shift_name : null,
                        'shift_time' => ($roster && !$roster->is_weekoff && $roster->login_time && $roster->logout_time)
                            ? Carbon::parse($roster->login_time)->format('h:i A') . ' - ' . Carbon::parse($roster->logout_time)->format('h:i A')
                            : null,
                        // 'punch_in' => ($attendance && $attendance->punch_in_time)
                        //     ? Carbon::parse($attendance->punch_in_time)->format('H:i')
                        //     : '',
                        // 'punch_out' => ($attendance && $attendance->punch_out_time)
                        //     ? Carbon::parse($attendance->punch_out_time)->format('H:i')
                        //     : '',
                        // CHANGED: display format switched to 12-hour + AM/PM to match the new time picker.
// Carbon::parse() on the save side already understands "02:30 PM" natively, so
// storeMonthlyAttendanceSingle() needs no change at all.
                        'punch_in' => ($attendance && $attendance->punch_in_time)
                            ? Carbon::parse($attendance->punch_in_time)->format('h:i A')
                            : '',
                        'punch_out' => ($attendance && $attendance->punch_out_time)
                            ? Carbon::parse($attendance->punch_out_time)->format('h:i A')
                            : '',
                    ];
                }
            }
        }

        return view('client.monthlyAttendanceEntry', compact(
            'employees',
            'employee',
            'employeeId',
            'month',
            'year',
            'days'
        ));
    }

    /**
     * ADDED: Save a single employee's attendance for as many days of the
     * selected month as the admin has filled in. Days left blank are simply
     * skipped — nothing is required to be complete. A day that already has
     * an attendance record is updated instead of duplicated, and its
     * AttendanceLog rows are kept in sync the same way updateAttendance() does.
     */
    public function storeMonthlyAttendanceSingle(Request $request)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $validator = Validator::make($request->all(), [
                'employee_id' => 'required|exists:rl_employees_tbl,id',
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2000|max:2100',
                'attendance' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $employee = Employee::where('client_id', $user->id)
                ->where('id', $request->employee_id)
                ->first();

            if (!$employee) {
                return back()->withInput()->with('error', 'Selected employee was not found.');
            }

            $month = $request->month;
            $year = $request->year;
            $rows = $request->attendance ?? [];

            $saved = 0;
            $skipped = 0;
            $errors = [];

            foreach ($rows as $date => $row) {

                $punchInTime = trim($row['punch_in'] ?? '');
                $punchOutTime = trim($row['punch_out'] ?? '');

                // Nothing entered for this day — leave it alone, this is expected.
                if ($punchInTime === '' && $punchOutTime === '') {
                    continue;
                }

                if ($punchInTime === '') {
                    $skipped++;
                    $errors[] = Carbon::parse($date)->format('d M') . ': Punch In time is required if Punch Out is set.';
                    continue;
                }

                try {
                    $punchIn = Carbon::parse($date . ' ' . $punchInTime);
                } catch (\Throwable $e) {
                    $skipped++;
                    $errors[] = Carbon::parse($date)->format('d M') . ': Invalid Punch In time.';
                    continue;
                }

                $punchOut = null;

                if ($punchOutTime !== '') {
                    try {
                        $punchOut = Carbon::parse($date . ' ' . $punchOutTime);
                    } catch (\Throwable $e) {
                        $skipped++;
                        $errors[] = Carbon::parse($date)->format('d M') . ': Invalid Punch Out time.';
                        continue;
                    }

                    if ($punchOut->lte($punchIn)) {
                        $skipped++;
                        $errors[] = Carbon::parse($date)->format('d M') . ': Punch Out must be after Punch In.';
                        continue;
                    }
                }

                $attendance = Attendance::where('client_id', $user->id)
                    ->where('emp_id', $employee->id)
                    ->whereDate('punch_in_time', $date)
                    ->first();

                if ($attendance) {
                    $attendance->update([
                        'punch_in_time' => $punchIn->format('Y-m-d H:i:s'),
                        'punch_out_time' => $punchOut ? $punchOut->format('Y-m-d H:i:s') : null,
                    ]);
                } else {
                    $attendance = Attendance::create([
                        'client_id' => $user->id,
                        'emp_id' => $employee->id,
                        'punch_in_time' => $punchIn->format('Y-m-d H:i:s'),
                        'punch_out_time' => $punchOut ? $punchOut->format('Y-m-d H:i:s') : null,
                    ]);
                }

                // Keep AttendanceLog in sync so Working Hours / late-login calculations
                // elsewhere in the app stay correct.
                $logs = AttendanceLog::where('client_id', $user->id)
                    ->where('emp_id', $employee->id)
                    ->whereDate('punch_time', $date)
                    ->orderBy('punch_time', 'asc')
                    ->get();

                if ($logs->isEmpty()) {

                    AttendanceLog::create([
                        'client_id' => $user->id,
                        'emp_id' => $employee->id,
                        'punch_time' => $punchIn->format('Y-m-d H:i:s'),
                    ]);

                    if ($punchOut) {
                        AttendanceLog::create([
                            'client_id' => $user->id,
                            'emp_id' => $employee->id,
                            'punch_time' => $punchOut->format('Y-m-d H:i:s'),
                        ]);
                    }

                } else {

                    $firstLog = $logs->first();
                    $firstLog->update(['punch_time' => $punchIn->format('Y-m-d H:i:s')]);

                    if ($punchOut) {
                        if ($logs->count() > 1) {
                            $lastLog = $logs->last();
                            $lastLog->update(['punch_time' => $punchOut->format('Y-m-d H:i:s')]);
                        } else {
                            AttendanceLog::create([
                                'client_id' => $user->id,
                                'emp_id' => $employee->id,
                                'punch_time' => $punchOut->format('Y-m-d H:i:s'),
                            ]);
                        }
                    } elseif ($logs->count() > 1) {
                        // Punch out was cleared for this day — drop the stale checkout log.
                        $logs->last()->delete();
                    }
                }

                $saved++;
            }

            $redirect = redirect()->route('monthlyAttendanceEntry', [
                'employee_id' => $employee->id,
                'month' => $month,
                'year' => $year,
            ]);

            if ($saved > 0 && empty($errors)) {
                return $redirect->with('success', "{$saved} day(s) saved successfully for {$employee->employee_name}.");
            }

            if ($saved > 0 && !empty($errors)) {
                return $redirect->with('success', "{$saved} day(s) saved.")
                    ->with('error', 'Some days were skipped: ' . implode(' | ', $errors));
            }

            if ($saved === 0 && !empty($errors)) {
                return $redirect->with('error', 'Nothing was saved. ' . implode(' | ', $errors));
            }

            return $redirect->with('error', 'No attendance data was entered.');

        } catch (\Throwable $th) {
            return back()->withInput()->with('error', $th->getMessage());
        }
    }

}