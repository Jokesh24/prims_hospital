<?php

namespace App\Http\Controllers\employee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\OfficeTiming;
use App\Models\Holiday;
use App\Models\PaidLeave;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeAttendanceController extends Controller
{
    // Daily Attendance Log for Employee
    public function dailyLog(Request $request)
    {
        $employee = Auth::guard('employee')->user();

        $query = Attendance::where('emp_id', $employee->id);

        if ($request->filled('from_date')) {
            $query->where('punch_in_time', '>=', Carbon::createFromFormat('d-m-Y', $request->from_date)->startOfDay());
        }
        if ($request->filled('to_date')) {
            $query->where('punch_in_time', '<=', Carbon::createFromFormat('d-m-Y', $request->to_date)->endOfDay());
        }

        $attendances = $query->latest('punch_in_time')->paginate(20);
        $settings = OfficeTiming::where('client_id', $employee->client_id)->first();

        $allowedBreak = $settings->break_time ?? 30;

        // Apply identical working hours calculation from client-side
        $attendances->getCollection()->transform(function ($attendance) use ($allowedBreak) {
            $attendance->working_hours = '--';

            if (!$attendance->punch_in_time) {
                return $attendance;
            }

            $logs = AttendanceLog::where('client_id', $attendance->client_id)
                ->where('emp_id', $attendance->emp_id)
                ->whereDate('punch_time', Carbon::parse($attendance->punch_in_time)->toDateString())
                ->orderBy('punch_time')
                ->get();

            if ($logs->count() < 2) {
                return $attendance;
            }

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

        return view('employee.attendance.daily_attendance', compact('attendances', 'settings'));
    }

    // Monthly Attendance Sheet for Employee
    public function monthlySheet(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        $officeTiming = OfficeTiming::where('client_id', $employee->client_id)->first();

        $paidLeave = PaidLeave::where('client_id', $employee->client_id)
            ->where('status', 1)
            ->where('trash', 0)
            ->latest('id')
            ->first();

        $weekOffs = $paidLeave->week_offs ?? 0;

        $holidays = Holiday::where('client_id', $employee->client_id)
            ->where('status', 1)
            ->where('trash', 0)
            ->pluck('date')
            ->map(function ($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })->toArray();

        $attendanceData = Attendance::where('emp_id', $employee->id)
            ->whereMonth('punch_in_time', $month)
            ->whereYear('punch_in_time', $year)
            ->get()
            ->keyBy(function ($row) {
                return Carbon::parse($row->punch_in_time)->format('Y-m-d');
            });

        $attendanceStatus = [];

        // Apply identical comprehensive calculation from client-side
        for ($day = 1; $day <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $attendance = $attendanceData->get($date);

            // Attendance exists
            if ($attendance) {
                $status = 'P';

                if ($officeTiming) {
                    $loginTime = Carbon::parse($attendance->punch_in_time);
                    $officeStart = Carbon::parse($date . ' ' . Carbon::parse($officeTiming->start_time)->format('H:i:s'));
                    $graceEnd = $officeStart->copy()->addMinutes($officeTiming->grace_time);

                    if (!empty($attendance->punch_out_time)) {
                        $workedHours = $loginTime->diffInMinutes(Carbon::parse($attendance->punch_out_time)) / 60;
                        if ($workedHours < $officeTiming->half_day_limit) {
                            $status = 'H';
                        }
                    }

                    if ($status != 'H' && $loginTime->gt($graceEnd)) {
                        $status = 'L';
                    }
                }

                $attendanceStatus[$date] = $status;
                continue;
            }

            // Holiday
            if (in_array($date, $holidays)) {
                $attendanceStatus[$date] = 'HOL';
                continue;
            }

            // Week Off
            $carbonDate = Carbon::parse($date);
            $isWeekOff = false;

            if ($weekOffs == 1) {
                $isWeekOff = $carbonDate->isSunday();
            } elseif ($weekOffs == 2) {
                $isWeekOff = $carbonDate->isSaturday() || $carbonDate->isSunday();
            }

            if ($isWeekOff) {
                $attendanceStatus[$date] = 'W';
                continue;
            }

            // Absent
            $attendanceStatus[$date] = 'A';
        }

        return view('employee.attendance.monthly_attendance', compact('attendanceStatus', 'month', 'year', 'employee'));
    }
}