<?php

namespace App\Http\Controllers\employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\OfficeTiming;
use App\Models\PaidLeave;
use App\Models\Leave;
use App\Models\Holiday;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        $empId = $employee->id;

        // Safely fetch client_id from the employee model or fallback to their last attendance record
        $clientId = $employee->client_id ?? (Attendance::where('emp_id', $empId)->latest('punch_in_time')->value('client_id') ?? 1);

        $today = Carbon::today();
        $currentYear = $today->year;
        $monthStart = Carbon::now()->startOfMonth();

        // =========================================================
        // 1. LEAVES DATA & DATES
        // =========================================================
        $totalLeavesApplied = Leave::where('employee_id', $empId)
            ->whereYear('created_at', $currentYear)
            ->count();

        $paidLeaveSettings = PaidLeave::where('client_id', $clientId)->first();
        $totalYearlyPaidLeaves = $paidLeaveSettings ? $paidLeaveSettings->yearly : 0;

        $usedPaidLeaves = Leave::where('employee_id', $empId)
            ->where('leave_status', Leave::STATUS_ACCEPTED)
            ->whereYear('created_at', $currentYear)
            ->count();

        $leftPaidLeaves = max(0, $totalYearlyPaidLeaves - $usedPaidLeaves);

        $totalLeavesDenominator = $totalYearlyPaidLeaves > 0 ? $totalYearlyPaidLeaves : 1;
        $leftLeavesPercentage = round(($leftPaidLeaves / $totalLeavesDenominator) * 100, 1);
        $usedLeavesPercentage = round(($usedPaidLeaves / $totalLeavesDenominator) * 100, 1);

        // Fetch Approved Leave Records for Dates display
        $leaveRecords = Leave::where('employee_id', $empId)
            ->where('leave_status', Leave::STATUS_ACCEPTED)
            ->whereYear('leave_date_from', $currentYear)
            ->orderBy('leave_date_from', 'desc')
            ->get();

        // =========================================================
        // 2. ATTENDANCE DATES, COUNTS & PIE CHART
        // =========================================================
        $officeTiming = OfficeTiming::where('client_id', $clientId)->first();
        $halfDayLimit = $officeTiming ? (float) $officeTiming->half_day_limit : 4.0;
        $officeStartTime = $officeTiming ? Carbon::parse($officeTiming->start_time) : Carbon::parse('10:00:00');
        $officeEndTime = $officeTiming ? Carbon::parse($officeTiming->end_time) : Carbon::parse('18:00:00');
        $graceTime = $officeTiming ? (int) $officeTiming->grace_time : 15;

        // Calculate standard shift length (e.g., 8 hours)
        $standardShiftHours = abs($officeEndTime->diffInMinutes($officeStartTime)) / 60;

        $monthlyAttendance = Attendance::where('emp_id', $empId)
            ->whereBetween('punch_in_time', [$monthStart, $today->copy()->endOfDay()])
            ->get();

        $fullDays = 0;
        $halfDays = 0;
        $totalLateLogins = 0;
        $totalOtDays = 0;

        $lateLoginDates = [];
        $halfDayDates = [];

        foreach ($monthlyAttendance as $att) {
            $punchIn = Carbon::parse($att->punch_in_time);
            $expectedStartTime = $punchIn->copy()->setTimeFrom($officeStartTime)->addMinutes($graceTime);

            // Late Login Check
            if ($punchIn->greaterThan($expectedStartTime)) {
                $totalLateLogins++;
                $lateLoginDates[] = $punchIn;
            }

            // Half Day / Full Day / Overtime Check
            if ($att->punch_out_time) {
                $punchOut = Carbon::parse($att->punch_out_time);

                // Using abs() to ensure positive hours worked calculation
                $hoursWorked = abs($punchOut->diffInMinutes($punchIn)) / 60;

                // Overtime Check
                if ($hoursWorked > $standardShiftHours) {
                    $totalOtDays++;
                }

                if ($hoursWorked < $halfDayLimit) {
                    $halfDays++;
                    $halfDayDates[] = $punchIn;
                } else {
                    $fullDays++;
                }
            } else {
                // No punch out yet today -> count as full day present so far
                $fullDays++;
            }
        }

        // Calculate valid working days passed in the current month (excluding weekends)
        $workingDaysPassed = 0;
        $currentDate = clone $monthStart;
        while ($currentDate->lte($today)) {
            if (!$currentDate->isWeekend()) {
                $workingDaysPassed++;
            }
            $currentDate->addDay();
        }

        $totalPresent = $fullDays + ($halfDays * 0.5);
        $totalWorkedDaysCount = $fullDays + $halfDays;
        $totalAbsent = max(0, $workingDaysPassed - $totalWorkedDaysCount);

        // Progress Bar Percentages
        $workingDaysDenominator = $workingDaysPassed > 0 ? $workingDaysPassed : 1;
        $fullDayPercentage = round(($fullDays / $workingDaysDenominator) * 100, 1);
        $halfDayPercentage = round(($halfDays / $workingDaysDenominator) * 100, 1);
        $absentPercentage = round(($totalAbsent / $workingDaysDenominator) * 100, 1);
        $latePercentage = round(($totalLateLogins / $workingDaysDenominator) * 100, 1);

        // Pie Chart Percentages (Based purely on days worked)
        $analysisDenominator = $totalWorkedDaysCount > 0 ? $totalWorkedDaysCount : 1;
        $onTimeDays = max(0, $totalWorkedDaysCount - $totalLateLogins);

        $onTimePiePercentage = round(($onTimeDays / $analysisDenominator) * 100, 1);
        $latePiePercentage = round(($totalLateLogins / $analysisDenominator) * 100, 1);
        $otPiePercentage = round(($totalOtDays / $analysisDenominator) * 100, 1);

        // =========================================================
        // 3. UPCOMING HOLIDAYS
        // =========================================================
        $upcomingHolidays = Holiday::where('client_id', $clientId)
            ->where('date', '>=', $today)
            ->where('status', 1)
            ->where('trash', 0)
            ->orderBy('date', 'asc')
            ->get();

        // =========================================================
        // 4. YEARLY ANALYTICS (For the Bar Chart)
        // =========================================================
        $selectedYear = $request->input('year', $currentYear);
        $currentMonthNum = $today->month;

        $yearlyPresentData = [];
        $yearlyAbsentData = [];

        // Fetch all attendances for the selected year and group by month
        $yearlyAttendance = Attendance::where('emp_id', $empId)
            ->whereYear('punch_in_time', $selectedYear)
            ->get()
            ->groupBy(function ($att) {
                return Carbon::parse($att->punch_in_time)->format('n');
            });

        for ($m = 1; $m <= 12; $m++) {
            // If the month is in the future, return 0 for both present and absent
            if ($selectedYear > $currentYear || ($selectedYear == $currentYear && $m > $currentMonthNum)) {
                $yearlyPresentData[] = 0;
                $yearlyAbsentData[] = 0;
                continue;
            }

            $workingDaysInMonth = 0;
            $monthStartNode = Carbon::create($selectedYear, $m, 1);

            // If calculating the current month, only count working days up to TODAY
            $monthEndNode = ($selectedYear == $currentYear && $m == $currentMonthNum)
                ? $today->copy()
                : $monthStartNode->copy()->endOfMonth();

            $dateIterator = clone $monthStartNode;
            while ($dateIterator->lte($monthEndNode)) {
                if (!$dateIterator->isWeekend()) {
                    $workingDaysInMonth++;
                }
                $dateIterator->addDay();
            }

            // Calculate employee's stats for this specific month
            $attendancesForMonth = $yearlyAttendance->get($m, collect());
            $mFullDays = 0;
            $mHalfDays = 0;

            foreach ($attendancesForMonth as $att) {
                $pIn = Carbon::parse($att->punch_in_time);
                if ($att->punch_out_time) {
                    $pOut = Carbon::parse($att->punch_out_time);
                    $hWorked = abs($pOut->diffInMinutes($pIn)) / 60;
                    if ($hWorked < $halfDayLimit) {
                        $mHalfDays++;
                    } else {
                        $mFullDays++;
                    }
                } else {
                    $mFullDays++;
                }
            }

            $mPresent = $mFullDays + ($mHalfDays * 0.5);
            $mWorkedDaysCount = $mFullDays + $mHalfDays;
            $mAbsent = max(0, $workingDaysInMonth - $mWorkedDaysCount);

            $yearlyPresentData[] = $mPresent;
            $yearlyAbsentData[] = $mAbsent;

            // Fetch the 5 most recent leave applications (all statuses)
            $recentLeaves = Leave::where('employee_id', $empId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('employee.index', compact(
            'totalLeavesApplied',
            'leftPaidLeaves',
            'usedPaidLeaves',
            'leftLeavesPercentage',
            'usedLeavesPercentage',
            'totalPresent',
            'fullDays',
            'halfDays',
            'fullDayPercentage',
            'halfDayPercentage',
            'totalAbsent',
            'totalLateLogins',
            'absentPercentage',
            'latePercentage',
            'onTimePiePercentage',
            'latePiePercentage',
            'otPiePercentage',
            'leaveRecords',
            'lateLoginDates',
            'halfDayDates',
            'upcomingHolidays',
            'yearlyPresentData',
            'yearlyAbsentData',
            'selectedYear',
            'recentLeaves'
        ));
    }
}