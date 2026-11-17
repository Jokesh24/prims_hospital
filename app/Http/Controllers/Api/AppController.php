<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppController extends Controller
{

    public function insertBiometriAttendence(Request $request)
    {
        try {

            $attendanceData = $request->input('attendanceData');

            if (empty($attendanceData) || !is_array($attendanceData)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Attendance data is required.'
                ], 400);
            }

            usort($attendanceData, function ($a, $b) {
                return Carbon::parse($a['loginTime'])
                    <=> Carbon::parse($b['loginTime']);
            });

            DB::beginTransaction();

            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            $lastAcceptedPunch = [];

            // FIXED: how long an attendance row may stay "open" (no punch_out) before a
            // later punch is no longer assumed to be its checkout. Covers the longest
            // configured shift (12h Night Shift) plus a generous grace window.
            $maxOpenShiftHours = 20;

            foreach ($attendanceData as $row) {

                if (empty($row['teacherId']) || empty($row['loginTime'])) {
                    $skipped++;
                    continue;
                }

                $employee = Employee::where('biometric_id', $row['teacherId'])->first();

                if (!$employee) {
                    $skipped++;
                    continue;
                }

                $punchTime = Carbon::parse($row['loginTime']);
                $employeeKey = $employee->id;

                if (isset($lastAcceptedPunch[$employeeKey])) {
                    $previousPunch = $lastAcceptedPunch[$employeeKey];

                    if ($previousPunch->diffInSeconds($punchTime) < 300) {
                        $skipped++;
                        continue;
                    }
                }

                // =========================================================================
                // CRITICAL OVERNIGHT LOGIC: Determine the "Logical Date" for this punch
                // (Brought in line with syncAttendance() so both entry points agree.)
                //
                // FIXED: Primary signal is now the employee's own OPEN attendance record
                // (punch_out_time still NULL), not the roster. The old roster-only check
                // silently failed whenever "yesterday's" roster was assigned/edited AFTER
                // the punch was already processed (e.g. a bulk roster update run the next
                // day) — exactly what happened for employees 280's Nov 1→2 and Nov 3→4
                // overnight shifts. Checking for an open shift first removes that
                // dependency on roster timing entirely. The roster check remains as a
                // fallback for punches with no open shift to anchor to (e.g. a missed
                // clock-in where only the checkout punch ever arrives).
                // =========================================================================
                $logicalDate = $punchTime->toDateString();

                $openAttendance = Attendance::where('client_id', $employee->client_id)
                    ->where('emp_id', $employee->id)
                    ->whereNull('punch_out_time')
                    ->where('punch_in_time', '<', $punchTime)
                    ->orderBy('punch_in_time', 'desc')
                    ->lockForUpdate()
                    ->first();

                if ($openAttendance) {
                    $openPunchIn = Carbon::parse($openAttendance->punch_in_time);

                    if ($openPunchIn->diffInHours($punchTime) <= $maxOpenShiftHours) {
                        $logicalDate = $openPunchIn->toDateString();
                    } else {
                        // Too old to plausibly be this shift's checkout — leave it open
                        // and don't anchor this punch to it.
                        $openAttendance = null;
                    }
                }

                if ($logicalDate === $punchTime->toDateString()) {
                    $yesterday = $punchTime->copy()->subDay()->toDateString();

                    $yesterdayRoster = DB::table('rl_employee_rosters_tbl')
                        ->join('rl_shifts_tbl', 'rl_employee_rosters_tbl.shift_id', '=', 'rl_shifts_tbl.id')
                        ->where('rl_employee_rosters_tbl.employee_id', $employee->id)
                        ->where('rl_employee_rosters_tbl.roster_date', $yesterday)
                        ->first();

                    if ($yesterdayRoster && $yesterdayRoster->is_overnight) {
                        // If the punch happens before (Logout Time + 4 hours buffer), it belongs to Yesterday
                        $shiftEndStr = $yesterday . ' ' . $yesterdayRoster->logout_time;
                        $shiftEnd = Carbon::parse($shiftEndStr)->addDay(); // Add day because it crosses midnight
                        $bufferEnd = $shiftEnd->copy()->addHours(4);

                        if ($punchTime->lte($bufferEnd)) {
                            $logicalDate = $yesterday;
                        }
                    }
                }

                $startTime = $punchTime->copy()->subMinutes(5);
                $endTime = $punchTime->copy()->addMinutes(5);

                $alreadyExists = AttendanceLog::where('client_id', $employee->client_id)
                    ->where('emp_id', $employee->id)
                    ->whereBetween('punch_time', [$startTime, $endTime])
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;
                    continue;
                }

                // Insert Log with Logical Date (raw insert so we don't depend on the
                // AttendanceLog model's $fillable including logical_date)
                DB::table('rl_attendance_logs_tbl')->insert([
                    'client_id' => $employee->client_id,
                    'emp_id' => $employee->id,
                    'punch_time' => $punchTime,
                    'logical_date' => $logicalDate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $lastAcceptedPunch[$employeeKey] = $punchTime->copy();

                // =========================================================================
                // UPDATE MAIN ATTENDANCE RECORD BASED ON LOGICAL DATE (not calendar date)
                // =========================================================================
                $logs = DB::table('rl_attendance_logs_tbl')
                    ->where('emp_id', $employee->id)
                    ->where('logical_date', $logicalDate)
                    ->orderBy('punch_time', 'asc')
                    ->get();

                if ($logs->isEmpty()) {
                    continue;
                }

                $firstPunch = $logs->first()->punch_time;

                $lastPunch = $logs->count() > 1
                    ? $logs->last()->punch_time
                    : null;

                // FIXED: update the open shift we anchored to directly when we have one —
                // unambiguous even if more than one attendance row could share the same
                // punch_in_time calendar date. Falls back to the date lookup otherwise.
                $attendance = $openAttendance
                    ?? Attendance::where('client_id', $employee->client_id)
                        ->where('emp_id', $employee->id)
                        ->whereDate('punch_in_time', $logicalDate)
                        ->lockForUpdate()
                        ->first();

                if (!$attendance) {

                    Attendance::create([
                        'client_id' => $employee->client_id,
                        'emp_id' => $employee->id,
                        'punch_in_time' => $firstPunch,
                        'punch_out_time' => $lastPunch,
                    ]);

                    $inserted++;

                } else {

                    $attendance->update([
                        'punch_in_time' => $firstPunch,
                        'punch_out_time' => $lastPunch,
                    ]);

                    $updated++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance synchronized successfully.',
                'inserted_records' => $inserted,
                'updated_records' => $updated,
                'skipped_records' => $skipped
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    public function syncAttendance(Request $request)
    {
        try {
            $attendanceData = $request->input('attendanceData');

            if (empty($attendanceData) || !is_array($attendanceData)) {
                return response()->json(['status' => 'error', 'message' => 'Attendance data is required.'], 400);
            }

            // Sort chronologically
            usort($attendanceData, function ($a, $b) {
                return Carbon::parse($a['loginTime']) <=> Carbon::parse($b['loginTime']);
            });

            DB::beginTransaction();

            $inserted = 0;
            $updated = 0;
            $skipped = 0;
            $lastAcceptedPunch = [];

            // FIXED: see insertBiometriAttendence() for the full explanation.
            $maxOpenShiftHours = 20;

            foreach ($attendanceData as $row) {
                if (empty($row['teacherId']) || empty($row['loginTime'])) {
                    $skipped++;
                    continue;
                }

                $employee = Employee::where('biometric_id', $row['teacherId'])->first();
                if (!$employee) {
                    $skipped++;
                    continue;
                }

                $punchTime = Carbon::parse($row['loginTime']);
                $employeeKey = $employee->id;

                // Prevent duplicate/spam punches within 5 minutes
                if (isset($lastAcceptedPunch[$employeeKey]) && $lastAcceptedPunch[$employeeKey]->diffInSeconds($punchTime) < 300) {
                    $skipped++;
                    continue;
                }

                // =========================================================================
                // CRITICAL OVERNIGHT LOGIC: Determine the "Logical Date" for this punch
                //
                // FIXED: Primary signal is now the employee's own OPEN attendance record
                // (punch_out_time still NULL), not the roster alone — see
                // insertBiometriAttendence() above for the full root-cause explanation.
                // =========================================================================
                $logicalDate = $punchTime->toDateString();

                $openAttendance = Attendance::where('client_id', $employee->client_id)
                    ->where('emp_id', $employee->id)
                    ->whereNull('punch_out_time')
                    ->where('punch_in_time', '<', $punchTime)
                    ->orderBy('punch_in_time', 'desc')
                    ->lockForUpdate()
                    ->first();

                if ($openAttendance) {
                    $openPunchIn = Carbon::parse($openAttendance->punch_in_time);

                    if ($openPunchIn->diffInHours($punchTime) <= $maxOpenShiftHours) {
                        $logicalDate = $openPunchIn->toDateString();
                    } else {
                        $openAttendance = null;
                    }
                }

                if ($logicalDate === $punchTime->toDateString()) {
                    $yesterday = $punchTime->copy()->subDay()->toDateString();

                    // Check if the employee was rostered for an OVERNIGHT shift yesterday
                    $yesterdayRoster = DB::table('rl_employee_rosters_tbl')
                        ->join('rl_shifts_tbl', 'rl_employee_rosters_tbl.shift_id', '=', 'rl_shifts_tbl.id')
                        ->where('rl_employee_rosters_tbl.employee_id', $employee->id)
                        ->where('rl_employee_rosters_tbl.roster_date', $yesterday)
                        ->first();

                    if ($yesterdayRoster && $yesterdayRoster->is_overnight) {
                        // If the punch happens before (Logout Time + 4 hours buffer), it belongs to Yesterday
                        $shiftEndStr = $yesterday . ' ' . $yesterdayRoster->logout_time;
                        $shiftEnd = Carbon::parse($shiftEndStr)->addDay(); // Add day because it crosses midnight
                        $bufferEnd = $shiftEnd->copy()->addHours(4);

                        if ($punchTime->lte($bufferEnd)) {
                            $logicalDate = $yesterday;
                        }
                    }
                }

                // Check if log already exists
                $alreadyExists = DB::table('rl_attendance_logs_tbl')
                    ->where('emp_id', $employee->id)
                    ->whereBetween('punch_time', [$punchTime->copy()->subMinutes(5), $punchTime->copy()->addMinutes(5)])
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;
                    continue;
                }

                // Insert Log with Logical Date
                DB::table('rl_attendance_logs_tbl')->insert([
                    'client_id' => $employee->client_id,
                    'emp_id' => $employee->id,
                    'punch_time' => $punchTime,
                    'logical_date' => $logicalDate, // Tracks the actual shift day
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $lastAcceptedPunch[$employeeKey] = $punchTime->copy();

                // =========================================================================
                // UPDATE MAIN ATTENDANCE RECORD BASED ON LOGICAL DATE
                // =========================================================================
                $logs = DB::table('rl_attendance_logs_tbl')
                    ->where('emp_id', $employee->id)
                    ->where('logical_date', $logicalDate)
                    ->orderBy('punch_time', 'asc')
                    ->get();

                if ($logs->isEmpty())
                    continue;

                $firstPunch = $logs->first()->punch_time;
                $lastPunch = $logs->count() > 1 ? $logs->last()->punch_time : null;

                // FIXED: update the open shift we anchored to directly when we have one.
                $attendance = $openAttendance
                    ?? Attendance::where('client_id', $employee->client_id)
                        ->where('emp_id', $employee->id)
                        ->whereDate('punch_in_time', $logicalDate) // Note: Using logical_date to anchor
                        ->lockForUpdate()
                        ->first();

                if (!$attendance) {
                    Attendance::create([
                        'client_id' => $employee->client_id,
                        'emp_id' => $employee->id,
                        'punch_in_time' => $firstPunch,
                        'punch_out_time' => $lastPunch,
                    ]);
                    $inserted++;
                } else {
                    $attendance->update([
                        'punch_in_time' => $firstPunch, // Update first punch if an earlier one synced
                        'punch_out_time' => $lastPunch,
                    ]);
                    $updated++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance synchronized successfully.',
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    public function insertBiometriAttendencejok(Request $request)
    {
        try {

            $attendanceData = $request->input('attendanceData');

            if (empty($attendanceData) || !is_array($attendanceData)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Attendance data is required.'
                ], 400);
            }

            usort($attendanceData, function ($a, $b) {
                return Carbon::parse($a['loginTime'])
                    <=> Carbon::parse($b['loginTime']);
            });

            DB::beginTransaction();

            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            $lastAcceptedPunch = [];

            foreach ($attendanceData as $row) {

                if (empty($row['teacherId']) || empty($row['loginTime'])) {
                    $skipped++;
                    continue;
                }

                $employee = Employee::where('biometric_id', $row['teacherId'])->first();

                if (!$employee) {
                    $skipped++;
                    continue;
                }

                $punchTime = Carbon::parse($row['loginTime']);
                $employeeKey = $employee->id;

                if (isset($lastAcceptedPunch[$employeeKey])) {
                    $previousPunch = $lastAcceptedPunch[$employeeKey];

                    if ($previousPunch->diffInSeconds($punchTime) < 300) {
                        $skipped++;
                        continue;
                    }
                }

                // =========================================================================
                // CRITICAL OVERNIGHT LOGIC: Determine the "Logical Date" for this punch
                // (Brought in line with syncAttendance() so both entry points agree.)
                // =========================================================================
                $logicalDate = $punchTime->toDateString();
                $yesterday = $punchTime->copy()->subDay()->toDateString();

                $yesterdayRoster = DB::table('rl_employee_rosters_tbl')
                    ->join('rl_shifts_tbl', 'rl_employee_rosters_tbl.shift_id', '=', 'rl_shifts_tbl.id')
                    ->where('rl_employee_rosters_tbl.employee_id', $employee->id)
                    ->where('rl_employee_rosters_tbl.roster_date', $yesterday)
                    ->first();

                if ($yesterdayRoster && $yesterdayRoster->is_overnight) {
                    // If the punch happens before (Logout Time + 4 hours buffer), it belongs to Yesterday
                    $shiftEndStr = $yesterday . ' ' . $yesterdayRoster->logout_time;
                    $shiftEnd = Carbon::parse($shiftEndStr)->addDay(); // Add day because it crosses midnight
                    $bufferEnd = $shiftEnd->copy()->addHours(4);

                    if ($punchTime->lte($bufferEnd)) {
                        $logicalDate = $yesterday;
                    }
                }

                $startTime = $punchTime->copy()->subMinutes(5);
                $endTime = $punchTime->copy()->addMinutes(5);

                $alreadyExists = AttendanceLog::where('client_id', $employee->client_id)
                    ->where('emp_id', $employee->id)
                    ->whereBetween('punch_time', [$startTime, $endTime])
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;
                    continue;
                }

                // Insert Log with Logical Date (raw insert so we don't depend on the
                // AttendanceLog model's $fillable including logical_date)
                DB::table('rl_attendance_logs_tbl')->insert([
                    'client_id' => $employee->client_id,
                    'emp_id' => $employee->id,
                    'punch_time' => $punchTime,
                    'logical_date' => $logicalDate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $lastAcceptedPunch[$employeeKey] = $punchTime->copy();

                // =========================================================================
                // UPDATE MAIN ATTENDANCE RECORD BASED ON LOGICAL DATE (not calendar date)
                // =========================================================================
                $logs = DB::table('rl_attendance_logs_tbl')
                    ->where('emp_id', $employee->id)
                    ->where('logical_date', $logicalDate)
                    ->orderBy('punch_time', 'asc')
                    ->get();

                if ($logs->isEmpty()) {
                    continue;
                }

                $firstPunch = $logs->first()->punch_time;

                $lastPunch = $logs->count() > 1
                    ? $logs->last()->punch_time
                    : null;

                $attendance = Attendance::where('client_id', $employee->client_id)
                    ->where('emp_id', $employee->id)
                    ->whereDate('punch_in_time', $logicalDate)
                    ->first();

                if (!$attendance) {

                    Attendance::create([
                        'client_id' => $employee->client_id,
                        'emp_id' => $employee->id,
                        'punch_in_time' => $firstPunch,
                        'punch_out_time' => $lastPunch,
                    ]);

                    $inserted++;

                } else {

                    $attendance->update([
                        'punch_in_time' => $firstPunch,
                        'punch_out_time' => $lastPunch,
                    ]);

                    $updated++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance synchronized successfully.',
                'inserted_records' => $inserted,
                'updated_records' => $updated,
                'skipped_records' => $skipped
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    public function syncAttendancejok(Request $request)
    {
        try {
            $attendanceData = $request->input('attendanceData');

            if (empty($attendanceData) || !is_array($attendanceData)) {
                return response()->json(['status' => 'error', 'message' => 'Attendance data is required.'], 400);
            }

            // Sort chronologically
            usort($attendanceData, function ($a, $b) {
                return Carbon::parse($a['loginTime']) <=> Carbon::parse($b['loginTime']);
            });

            DB::beginTransaction();

            $inserted = 0;
            $updated = 0;
            $skipped = 0;
            $lastAcceptedPunch = [];

            foreach ($attendanceData as $row) {
                if (empty($row['teacherId']) || empty($row['loginTime'])) {
                    $skipped++;
                    continue;
                }

                $employee = Employee::where('biometric_id', $row['teacherId'])->first();
                if (!$employee) {
                    $skipped++;
                    continue;
                }

                $punchTime = Carbon::parse($row['loginTime']);
                $employeeKey = $employee->id;

                // Prevent duplicate/spam punches within 5 minutes
                if (isset($lastAcceptedPunch[$employeeKey]) && $lastAcceptedPunch[$employeeKey]->diffInSeconds($punchTime) < 300) {
                    $skipped++;
                    continue;
                }

                // =========================================================================
                // CRITICAL OVERNIGHT LOGIC: Determine the "Logical Date" for this punch
                // =========================================================================
                $logicalDate = $punchTime->toDateString();
                $yesterday = $punchTime->copy()->subDay()->toDateString();

                // Check if the employee was rostered for an OVERNIGHT shift yesterday
                $yesterdayRoster = DB::table('rl_employee_rosters_tbl')
                    ->join('rl_shifts_tbl', 'rl_employee_rosters_tbl.shift_id', '=', 'rl_shifts_tbl.id')
                    ->where('rl_employee_rosters_tbl.employee_id', $employee->id)
                    ->where('rl_employee_rosters_tbl.roster_date', $yesterday)
                    ->first();

                if ($yesterdayRoster && $yesterdayRoster->is_overnight) {
                    // If the punch happens before (Logout Time + 4 hours buffer), it belongs to Yesterday
                    $shiftEndStr = $yesterday . ' ' . $yesterdayRoster->logout_time;
                    $shiftEnd = Carbon::parse($shiftEndStr)->addDay(); // Add day because it crosses midnight
                    $bufferEnd = $shiftEnd->copy()->addHours(4);

                    if ($punchTime->lte($bufferEnd)) {
                        $logicalDate = $yesterday;
                    }
                }

                // Check if log already exists
                $alreadyExists = DB::table('rl_attendance_logs_tbl')
                    ->where('emp_id', $employee->id)
                    ->whereBetween('punch_time', [$punchTime->copy()->subMinutes(5), $punchTime->copy()->addMinutes(5)])
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;
                    continue;
                }

                // Insert Log with Logical Date
                DB::table('rl_attendance_logs_tbl')->insert([
                    'client_id' => $employee->client_id,
                    'emp_id' => $employee->id,
                    'punch_time' => $punchTime,
                    'logical_date' => $logicalDate, // Tracks the actual shift day
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $lastAcceptedPunch[$employeeKey] = $punchTime->copy();

                // =========================================================================
                // UPDATE MAIN ATTENDANCE RECORD BASED ON LOGICAL DATE
                // =========================================================================
                $logs = DB::table('rl_attendance_logs_tbl')
                    ->where('emp_id', $employee->id)
                    ->where('logical_date', $logicalDate)
                    ->orderBy('punch_time', 'asc')
                    ->get();

                if ($logs->isEmpty())
                    continue;

                $firstPunch = $logs->first()->punch_time;
                $lastPunch = $logs->count() > 1 ? $logs->last()->punch_time : null;

                // Update main Attendance table anchoring to the Logical Date, not Punch Time Date
                $attendance = Attendance::where('client_id', $employee->client_id)
                    ->where('emp_id', $employee->id)
                    ->whereDate('punch_in_time', $logicalDate) // Note: Using logical_date to anchor
                    ->first();

                if (!$attendance) {
                    // If it's an overnight shift and the first punch is at 11 PM, we must ensure 
                    // the punch_in_time stores the actual datetime, but we treat it as logicalDate.
                    Attendance::create([
                        'client_id' => $employee->client_id,
                        'emp_id' => $employee->id,
                        'punch_in_time' => $firstPunch,
                        'punch_out_time' => $lastPunch,
                    ]);
                    $inserted++;
                } else {
                    $attendance->update([
                        'punch_in_time' => $firstPunch, // Update first punch if an earlier one synced
                        'punch_out_time' => $lastPunch,
                    ]);
                    $updated++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance synchronized successfully.',
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function syncAttendanceold(Request $request)
    {
        try {

            $attendanceData = $request->input('attendanceData');

            if (empty($attendanceData) || !is_array($attendanceData)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Attendance data is required.'
                ], 400);
            }

            usort($attendanceData, function ($a, $b) {
                return Carbon::parse($a['loginTime'])
                    <=> Carbon::parse($b['loginTime']);
            });

            DB::beginTransaction();

            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            $lastAcceptedPunch = [];

            foreach ($attendanceData as $row) {

                if (empty($row['teacherId']) || empty($row['loginTime'])) {
                    $skipped++;
                    continue;
                }

                $employee = Employee::where('biometric_id', $row['teacherId'])->first();

                if (!$employee) {
                    $skipped++;
                    continue;
                }

                $punchTime = Carbon::parse($row['loginTime']);
                $employeeKey = $employee->id;

                if (isset($lastAcceptedPunch[$employeeKey])) {
                    $previousPunch = $lastAcceptedPunch[$employeeKey];

                    if ($previousPunch->diffInSeconds($punchTime) < 300) {
                        $skipped++;
                        continue;
                    }
                }

                $startTime = $punchTime->copy()->subMinutes(5);
                $endTime = $punchTime->copy()->addMinutes(5);

                $alreadyExists = AttendanceLog::where('client_id', $employee->client_id)
                    ->where('emp_id', $employee->id)
                    ->whereBetween('punch_time', [$startTime, $endTime])
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;
                    continue;
                }

                AttendanceLog::create([
                    'client_id' => $employee->client_id,
                    'emp_id' => $employee->id,
                    'punch_time' => $punchTime,
                ]);

                $lastAcceptedPunch[$employeeKey] = $punchTime->copy();

                $logs = AttendanceLog::where('client_id', $employee->client_id)
                    ->where('emp_id', $employee->id)
                    ->whereDate('punch_time', $punchTime->toDateString())
                    ->orderBy('punch_time')
                    ->get();

                if ($logs->isEmpty()) {
                    continue;
                }

                $firstPunch = $logs->first()->punch_time;

                $lastPunch = $logs->count() > 1
                    ? $logs->last()->punch_time
                    : null;

                $attendance = Attendance::where('client_id', $employee->client_id)
                    ->where('emp_id', $employee->id)
                    ->whereDate('punch_in_time', $punchTime->toDateString())
                    ->first();

                if (!$attendance) {

                    Attendance::create([
                        'client_id' => $employee->client_id,
                        'emp_id' => $employee->id,
                        'punch_in_time' => $firstPunch,
                        'punch_out_time' => $lastPunch,
                    ]);

                    $inserted++;

                } else {

                    $attendance->update([
                        'punch_in_time' => $firstPunch,
                        'punch_out_time' => $lastPunch,
                    ]);

                    $updated++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Missing biometric attendance synchronized successfully.',
                'inserted_records' => $inserted,
                'updated_records' => $updated,
                'skipped_records' => $skipped
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    public function insertBiometriAttendenceold(Request $request)
    {
        try {

            $attendanceData = $request->input('attendanceData');

            if (empty($attendanceData) || !is_array($attendanceData)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Attendance data is required.'
                ], 400);
            }

            usort($attendanceData, function ($a, $b) {
                return Carbon::parse($a['loginTime'])
                    <=> Carbon::parse($b['loginTime']);
            });

            DB::beginTransaction();

            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            $lastAcceptedPunch = [];

            foreach ($attendanceData as $row) {

                if (empty($row['teacherId']) || empty($row['loginTime'])) {
                    $skipped++;
                    continue;
                }

                $employee = Employee::where('biometric_id', $row['teacherId'])->first();

                if (!$employee) {
                    $skipped++;
                    continue;
                }

                $punchTime = Carbon::parse($row['loginTime']);
                $employeeKey = $employee->id;

                if (isset($lastAcceptedPunch[$employeeKey])) {
                    $previousPunch = $lastAcceptedPunch[$employeeKey];

                    if ($previousPunch->diffInSeconds($punchTime) < 300) {
                        $skipped++;
                        continue;
                    }
                }

                $startTime = $punchTime->copy()->subMinutes(5);
                $endTime = $punchTime->copy()->addMinutes(5);

                $alreadyExists = AttendanceLog::where('client_id', $employee->client_id)
                    ->where('emp_id', $employee->id)
                    ->whereBetween('punch_time', [$startTime, $endTime])
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;
                    continue;
                }

                AttendanceLog::create([
                    'client_id' => $employee->client_id,
                    'emp_id' => $employee->id,
                    'punch_time' => $punchTime,
                ]);

                $lastAcceptedPunch[$employeeKey] = $punchTime->copy();

                $logs = AttendanceLog::where('client_id', $employee->client_id)
                    ->where('emp_id', $employee->id)
                    ->whereDate('punch_time', $punchTime->toDateString())
                    ->orderBy('punch_time', 'asc')
                    ->get();

                if ($logs->isEmpty()) {
                    continue;
                }

                $firstPunch = $logs->first()->punch_time;

                $lastPunch = $logs->count() > 1
                    ? $logs->last()->punch_time
                    : null;

                $attendance = Attendance::where('client_id', $employee->client_id)
                    ->where('emp_id', $employee->id)
                    ->whereDate('punch_in_time', $punchTime->toDateString())
                    ->first();

                if (!$attendance) {

                    Attendance::create([
                        'client_id' => $employee->client_id,
                        'emp_id' => $employee->id,
                        'punch_in_time' => $firstPunch,
                        'punch_out_time' => $lastPunch,
                    ]);

                    $inserted++;

                } else {

                    $attendance->update([
                        'punch_in_time' => $firstPunch,
                        'punch_out_time' => $lastPunch,
                    ]);

                    $updated++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance synchronized successfully.',
                'inserted_records' => $inserted,
                'updated_records' => $updated,
                'skipped_records' => $skipped
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
}