<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        // Assuming you identify the client from the session or Auth
        $clientId = session('ClientUser');
        $year = $request->input('year', date('Y'));
        $today = Carbon::today();
        $month = $today->format('m');
        $day = $today->format('d');

        // Calculate stats for current month
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $monthlyAttendance = Attendance::where('client_id', $clientId)
            ->whereBetween('punch_in_time', [$monthStart, $monthEnd])
            ->get();

        $totalRecords = $monthlyAttendance->count() ?: 1; // Prevent division by zero

        $monthlyStats = $this->getMonthlyAttendanceStats($clientId, $year);

        // Mapping logic (adjust 'status' field name if yours differs)
        $stats = [
            'on_time' => $monthlyAttendance->where('status', 'Present')->count(),
            'late' => $monthlyAttendance->where('status', 'Late')->count(),
            'ot' => $monthlyAttendance->where('status', 'OT')->count(),
        ];

        // Calculate percentages
        $percentages = [
            'on_time' => round(($stats['on_time'] / $totalRecords) * 100, 1),
            'late' => round(($stats['late'] / $totalRecords) * 100, 1),
            'ot' => round(($stats['ot'] / $totalRecords) * 100, 1),
        ];

        $attendanceData = Attendance::where('client_id', $clientId)
            ->whereYear('punch_in_time', $year)
            ->selectRaw('MONTH(punch_in_time) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month');

        // 1. Total Employees
        $totalEmployees = Employee::where('client_id', $clientId)->count();

        // 2. Active Employees (Gender Split)
        $activeEmployees = Employee::where('client_id', $clientId)
            ->where('status', 1) // Assuming 'status' column exists
            ->get();

        $totalActive = $activeEmployees->count();
        $maleCount = $activeEmployees->where('gender', 1)->count(); // Ensure 'gender' column exists
        $femaleCount = $activeEmployees->where('gender', 2)->count();

        // 3. Today's Attendance
        $today = Carbon::today();
        $attendanceToday = Attendance::where('client_id', $clientId)
            
            ->whereDate('punch_in_time', $today)
            ->get();

        $presentCount = $attendanceToday->count();
        $absentCount = $totalEmployees - $presentCount;

        // 2. Dynamic Leaves: Fetch employees with accepted leaves for today
        $onLeaveEmployees = \App\Models\Leave::where('client_id', $clientId)
            ->whereDate('leave_date_from', '<=', $today)
            ->whereDate('to_leave_date', '>=', $today)
            ->where('leave_status', \App\Models\Leave::STATUS_ACCEPTED)
            ->with('employee')
            ->get();

        // 3. Dynamic Late Logins
        $officeTiming = \App\Models\OfficeTiming::where('client_id', $clientId)->first();
        $lateEmployees = [];

        if ($officeTiming) {
            $startTime = Carbon::parse($officeTiming->start_time);
            $graceMinutes = $officeTiming->grace_time;
            $lateThreshold = $startTime->addMinutes($graceMinutes);

            $lateEmployees = Attendance::where('client_id', $clientId)
                ->whereDate('punch_in_time', $today)
                ->whereTime('punch_in_time', '>', $lateThreshold->format('H:i:s'))
                ->with('employee')
                ->get();
        }

        // Add this to your existing dashboard method
        $upcomingHolidays = Holiday::where('client_id', $clientId)
            ->where('date', '>=', $today)
            ->where('status', 1)
            ->where('trash', 0)
            ->orderBy('date', 'asc')
            ->get();

        // 1. Birthdays (Coming up in the current month)
        $birthdays = Employee::where('client_id', $clientId)
            ->whereMonth('dob', '>=', $month)
            ->orderByRaw("MONTH(dob), DAY(dob)")
            ->limit(5)
            ->get();

        // 2. Anniversaries (Based on joining_date)
        $anniversaries = Employee::where('client_id', $clientId)
            ->whereMonth('marriage_date', '>=', $month)
            ->orderByRaw("MONTH(marriage_date), DAY(marriage_date)")
            ->limit(5)
            ->get();

        // 3. New Joiners (Joined in the last 30 days)
        $newJoiners = Employee::where('client_id', $clientId)
            ->where('joining_date', '>=', $today->subDays(30))
            ->orderBy('joining_date', 'desc')
            ->limit(5)
            ->get();

        // Prepare arrays for Chart.js (filling missing months with 0)
        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = $attendanceData->get($i, 0);
        }

        return view('client.index', compact(
            'birthdays',
            'anniversaries',
            'newJoiners',
            'today',
            'totalEmployees',
            'totalActive',
            'maleCount',
            'femaleCount',
            'presentCount',
            'absentCount',
            'onLeaveEmployees',
            'lateEmployees',
            'upcomingHolidays',
            'chartData',
            'monthlyStats',
            'year',
            'stats',
            'percentages'
        ))->with([
                    'onLeaveCount' => $onLeaveEmployees->count(),
                    'lateCount' => $lateEmployees->count()
                ]);
    }

    // In DashboardController.php

    public function getMonthlyAttendanceStats($clientId, $year)
    {
        $stats = ['present' => [], 'absent' => []];
        $officeTiming = \App\Models\OfficeTiming::where('client_id', $clientId)->first();
        $start = Carbon::parse($officeTiming->start_time ?? '10:00:00');
        $grace = $officeTiming->grace_time ?? 15;
        $halfDayLimit = $officeTiming->half_day_limit ?? 4.0;

        for ($month = 1; $month <= 12; $month++) {
            $attendances = Attendance::where('client_id', $clientId)
                ->whereYear('punch_in_time', $year)
                ->whereMonth('punch_in_time', $month)
                ->get();

            $p = 0;
            $l = 0;
            $h = 0;

            foreach ($attendances as $att) {
                $punchIn = Carbon::parse($att->punch_in_time);
                $threshold = $start->copy()->addMinutes($grace);

                // Logic: Late
                if ($punchIn->format('H:i:s') > $threshold->format('H:i:s')) {
                    $l++;
                }
                // Logic: Half Day (If punch_out exists and duration is short)
                elseif ($att->punch_out_time && $punchIn->diffInHours($att->punch_out_time) <= $halfDayLimit) {
                    $h++;
                }
                // Logic: Present
                else {
                    $p++;
                }
            }

            $hol = \App\Models\Holiday::where('client_id', $clientId)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->count();

            $totalDaysInMonth = Carbon::create($year, $month)->daysInMonth;

            // Your formula: Present Days = P + L
            $presentDays = $p + $l;

            // Absent Days = Total - (Present + HalfDay + WeekOff + Holiday)
            // Since we don't have a W column, we subtract everything else
            $absentDays = $totalDaysInMonth - ($presentDays + $h + $hol);

            $stats['present'][] = $presentDays;
            $stats['absent'][] = max(0, $absentDays);
        }

        return $stats;
    }
}