<?php

namespace App\Services;

use App\Models\AdvanceRecovery;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeOT;
use App\Models\PaidLeave;
use App\Models\Holiday;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Designation;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Leave;
use App\Models\Earnings;
use App\Models\Deductions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function generatePayroll($clientId, $month, $year, $recoverAdvance = false)
    {
        Log::info('PAYROLL | GENERATE PAYROLL METHOD STARTED', [
            'client_id' => $clientId,
            'month' => $month,
            'year' => $year,
        ]);

        $employees = Employee::where('client_id', $clientId)
            ->where('status', 1)
            ->where('trash', 0)
            ->get();

        foreach ($employees as $employee) {

            $data = $this->calculatePayroll($employee, $month, $year);

            Log::info('PAYROLL | GENERATE RESULT RECEIVED', [
                'employee_id' => $employee->id,
                'data' => $data,
            ]);

            if (!is_array($data) || ($data['error'] ?? false)) {
                Log::error('PAYROLL | GENERATION SKIPPED', [
                    'employee_id' => $employee->id,
                    'month' => $month,
                    'year' => $year,
                    'error' => $data['message'] ?? 'Invalid payroll calculation result'
                ]);
                continue;
            }

            DB::transaction(function () use ($clientId, $month, $year, $employee, $data, $recoverAdvance) {
                $payroll = Payroll::where('client_id', $clientId)
                    ->where('employee_id', $employee->id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->lockForUpdate()
                    ->first();

                if (!$payroll) {
                    $payroll = new Payroll();
                    $payroll->client_id = $clientId;
                    $payroll->employee_id = $employee->id;
                    $payroll->month = $month;
                    $payroll->year = $year;
                }

                $payroll->fill([
                    'working_days' => $data['working_days'],
                    'present_days' => $data['present_days'],
                    'absent_days' => $data['absent_days'],
                    'half_days' => $data['half_days'],
                    'week_offs' => $data['week_offs'],                               // ADDED
                    'holidays' => $data['holidays'],                                 // ADDED
                    'weekoff_absence_offset' => $data['weekoff_absence_offset'] ?? 0,
                    'leave_days' => $data['leave_days'],
                    'paid_leave_days' => $data['paid_leave_days'],
                    'lop_days' => $data['lop_days'],
                    'gross_salary' => $data['gross_salary'],
                    'salary_after_lop' => $data['salary_after_lop'],
                    'lop_amount' => $data['lop_amount'],
                    'total_earnings' => $data['total_earnings'],
                    'total_deductions' => $data['total_deductions'],
                    'net_salary' => $data['net_salary'],
                ]);

                $payroll->save();
                PayrollItem::where('payroll_id', $payroll->id)->delete();

                foreach ($data['earning_rows'] as $row) {
                    PayrollItem::create([
                        'payroll_id' => $payroll->id,
                        'type' => 'earning',
                        'title' => $row['title'],
                        'value_type' => $row['value_type'],
                        'configured_value' => $row['value_type'] == 1 ? $row['percent'] : $row['amount'],
                        'calculated_amount' => $row['amount'],
                    ]);
                }

                foreach ($data['deduction_rows'] as $row) {
                    PayrollItem::create([
                        'payroll_id' => $payroll->id,
                        'type' => 'deduction',
                        'title' => $row['title'],
                        'value_type' => $row['value_type'],
                        'configured_value' => $row['value_type'] == 1 ? $row['percent'] : $row['amount'],
                        'calculated_amount' => $row['amount'],
                    ]);
                }

                if ($recoverAdvance && !empty($data['advance_id']) && ($data['advance_recovery'] ?? 0) > 0) {
                    $this->processAdvanceRecovery(
                        $employee,
                        $data['advance_id'],
                        $data['advance_recovery'],
                        $payroll->id
                    );
                }
            });
        }
    }

    public function calculatePayroll($employee, $month, $year)
    {
        try {
            $clientId = $employee->client_id;
            $grossSalary = $employee->gross_salary ?? 0;
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            $masters = $this->getPayrollMasters($clientId, $employee->id);

            $rosters = DB::table('rl_employee_rosters_tbl')
                ->leftJoin('rl_shifts_tbl', 'rl_employee_rosters_tbl.shift_id', '=', 'rl_shifts_tbl.id')
                ->where('rl_employee_rosters_tbl.client_id', $clientId)
                ->where('rl_employee_rosters_tbl.employee_id', $employee->id)
                ->whereMonth('rl_employee_rosters_tbl.roster_date', $month)
                ->whereYear('rl_employee_rosters_tbl.roster_date', $year)
                ->select('rl_employee_rosters_tbl.*', 'rl_shifts_tbl.login_time', 'rl_shifts_tbl.logout_time', 'rl_shifts_tbl.grace_time')
                ->get()
                ->keyBy('roster_date');

            $ot = $this->calculateOT($employee, $month, $year);
            $otDaysOriginal = $ot['ot_days'] ?? 0;

            // NEW: Date-wise OT ("Assign OT" screen) is an explicit, per-date payable amount.
            // It is paid in full and is NOT used to offset absences, so the total the admin
            // entered is exactly the total that reaches payroll and the payslip.
            // Legacy/manual OT entries keep the existing offset rule untouched.
            $applyOtOffset = (($ot['ot_source'] ?? 'manual') !== 'dates');

            // Strictly evaluate attendance against the Roster logic & apply OT offset to leaves
            $attendance = $this->calculateAttendance($employee, $month, $year, $daysInMonth, $masters, $rosters, $otDaysOriginal, $applyOtOffset);

            // Client Rule: "If number of double shifts equal OT, leaves will be get minus."
            // If OT days were used to cancel out unapproved absent days, we must deduct them from the payable OT amount.
            if (isset($attendance['payable_ot_days']) && $attendance['payable_ot_days'] != $otDaysOriginal) {
                $payableDays = max(0, $attendance['payable_ot_days']);

                // FIXED: Preserve the actual hours-per-day ratio from the originally recorded OT
                // instead of assuming a hardcoded 8 hours/day, so OT hours stay accurate even when
                // partially offset against LOP/absences.
                $originalOtHours = (float) ($ot['ot_hours'] ?? 0);
                $avgHoursPerDay = $otDaysOriginal > 0 ? ($originalOtHours / $otDaysOriginal) : 8;

                $ot['ot_days'] = $payableDays;
                $ot['ot_hours'] = round($payableDays * $avgHoursPerDay, 2);

                $perDayRate = $daysInMonth ? ($grossSalary / $daysInMonth) : 0;
                $ot['ot_amount'] = round($payableDays * $perDayRate, 2);
            }

            // Dynamically calculate the per-minute salary based on the exact shifts assigned this month
            // CHANGED: pass $employee through so flexible-timing departments can force a flat 8-hour day
            $salary = $this->calculateSalaryAfterLOP(
                $grossSalary,
                $attendance,
                $daysInMonth,
                $rosters,
                $employee
            );

            $earnings = $this->calculateEarnings($employee, $ot);
            $advance = $this->calculateAdvanceRecovery($employee);
            $tds = $this->calculateTDS($employee);

            $deductions = $this->calculateDeductions($employee, $earnings, $advance, $attendance, $tds, $salary);
            $netSalary = $this->calculateNetSalary($salary, $earnings, $ot, $deductions, $advance, $tds);
            $finalGrossSalary = ($employee->gross_salary ?? 0) + ($ot['ot_amount'] ?? 0);

            return $this->preparePayrollArray($finalGrossSalary, $attendance, $salary, $earnings, $ot, $advance, $tds, $deductions, $netSalary);

        } catch (\Throwable $e) {
            Log::error('Payroll Calculation Error: ' . $e->getMessage(), [
                'employee_id' => $employee->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => true,
                'message' => 'An error occurred while calculating payroll. Please check the logs for details.'
            ];
        }
    }

    /*  Payroll Masters (OLD, kept for reference — not used by calculatePayroll) */
    private function getPayrollMastersjok($clientId, $employeeId)
    {
        $employee = Employee::where('id', $employeeId)->where('client_id', $clientId)->firstOrFail();

        $paidLeaveRule = PaidLeave::where('client_id', $clientId)
            ->where('department_id', $employee->department_id)
            ->where('status', 1)
            ->where('trash', 0)
            ->latest('id')
            ->first();

        $holidays = Holiday::where('client_id', $clientId)
            ->where('status', 1)->where('trash', 0)
            ->where(function ($query) use ($employee) {
                $query->whereHas('departments', function ($departmentQuery) use ($employee) {
                    $departmentQuery->where('rl_departments_tbl.id', $employee->department_id);
                })->orWhereDoesntHave('departments');
            })
            ->pluck('date')->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->unique()->values()->toArray();

        $leaveDates = [];
        $approvedLeaves = Leave::where('client_id', $clientId)
            ->where('employee_id', $employee->id)
            ->where('leave_status', 1)->get();

        foreach ($approvedLeaves as $leave) {
            $startDate = Carbon::parse($leave->from_date);
            $endDate = Carbon::parse($leave->to_date);
            while ($startDate->lte($endDate)) {
                $leaveDates[] = $startDate->format('Y-m-d');
                $startDate->addDay();
            }
        }

        return [
            'employee' => $employee,
            'paidLeaveRule' => $paidLeaveRule,
            'holidays' => array_values(array_unique($holidays)),
            'leaveDates' => array_values(array_unique($leaveDates)),
        ];
    }

    /*  Payroll Masters  */
    private function getPayrollMasters($clientId, $employeeId)
    {
        $employee = Employee::where('id', $employeeId)->where('client_id', $clientId)->firstOrFail();

        // CHANGED: resolve employee-specific policy first, fall back to department policy
        $paidLeaveRule = PaidLeave::resolveFor($employee);

        $holidays = Holiday::where('client_id', $clientId)
            ->where('status', 1)->where('trash', 0)
            ->where(function ($query) use ($employee) {
                $query->whereHas('departments', function ($departmentQuery) use ($employee) {
                    $departmentQuery->where('rl_departments_tbl.id', $employee->department_id);
                })->orWhereDoesntHave('departments');
            })
            ->pluck('date')->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
            ->unique()->values()->toArray();

        $leaveDates = [];
        $approvedLeaves = Leave::where('client_id', $clientId)
            ->where('employee_id', $employee->id)
            ->where('leave_status', 1)->get();

        foreach ($approvedLeaves as $leave) {
            $startDate = Carbon::parse($leave->from_date);
            $endDate = Carbon::parse($leave->to_date);
            while ($startDate->lte($endDate)) {
                $leaveDates[] = $startDate->format('Y-m-d');
                $startDate->addDay();
            }
        }

        return [
            'employee' => $employee,
            'paidLeaveRule' => $paidLeaveRule,
            'holidays' => array_values(array_unique($holidays)),
            'leaveDates' => array_values(array_unique($leaveDates)),
        ];
    }

    public function calculateWeekOffs($deptPolicy, $daysInMonth, $month, $year)
    {
        $employeeWeekOffDates = [];
        if (!$deptPolicy)
            return $employeeWeekOffDates;

        $weekOffType = $deptPolicy->week_off_type ?? 'weekly';
        $weekOffCount = (int) ($deptPolicy->week_offs ?? 1);

        if ($weekOffCount > 0) {
            if ($weekOffType === 'weekly') {
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $cDate = Carbon::create($year, $month, $d);
                    if ($weekOffCount === 1 && $cDate->isSunday()) {
                        $employeeWeekOffDates[] = $cDate->format('Y-m-d');
                    } elseif ($weekOffCount >= 2 && ($cDate->isSaturday() || $cDate->isSunday())) {
                        $employeeWeekOffDates[] = $cDate->format('Y-m-d');
                    }
                }
            } else {
                $allocated = 0;
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $cDate = Carbon::create($year, $month, $d);
                    if ($cDate->isSunday() && $allocated < $weekOffCount) {
                        $employeeWeekOffDates[] = $cDate->format('Y-m-d');
                        $allocated++;
                    }
                }
                if ($allocated < $weekOffCount) {
                    for ($d = 1; $d <= $daysInMonth; $d++) {
                        $cDate = Carbon::create($year, $month, $d);
                        if ($cDate->isSaturday() && !in_array($cDate->format('Y-m-d'), $employeeWeekOffDates) && $allocated < $weekOffCount) {
                            $employeeWeekOffDates[] = $cDate->format('Y-m-d');
                            $allocated++;
                        }
                    }
                }
            }
        }
        return $employeeWeekOffDates;
    }

    /**
     * ADDED: Department-level flag — no strict login/shift timing enforced; attendance and
     * per-hour salary are evaluated against a flat 8-hour reference shift instead of the
     * employee's rostered shift timings, regardless of whatever roster data might exist.
     */
    private function isFlexibleTimingDepartment(Employee $employee): bool
    {
        return $employee->department && (int) ($employee->department->flexible_timing ?? 0) === 1;
    }

    /**
     * ADDED: Department-level flag — Late Login and Half Day amounts are never deducted
     * for this department, in any month, going forward.
     */
    private function isNoDeductionsDepartment(Employee $employee): bool
    {
        return $employee->department && (int) ($employee->department->no_deductions ?? 0) === 1;
    }

    /**
     * ADDED: One-time payroll exception — employees were not informed about the Late Login /
     * Half Day deduction rules before the October 2026 payroll cycle began, so those specific
     * deductions are waived for October 2026 only. Every month after this (November 2026
     * onward) continues to deduct normally, with no code change required.
     */
    private function isOctober2026Exception($month, $year): bool
    {
        return (int) $month === 10 && (int) $year === 2026;
    }

    /* Attendance - Strict Roster Evaluation */
    private function calculateAttendance($employee, $month, $year, $daysInMonth, $masters, $rosters, $otDaysAvailable = 0, $applyOtOffset = true)
    {
        $paidLeaveRule = $masters['paidLeaveRule'];
        $leaveDates = $masters['leaveDates'];
        $holidays = $masters['holidays'];

        $attendanceData = Attendance::where('client_id', $employee->client_id)
            ->where('emp_id', $employee->id)
            ->whereMonth('punch_in_time', $month)
            ->whereYear('punch_in_time', $year)
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->punch_in_time)->format('Y-m-d'));

        $employeeWeekOffDates = $this->calculateWeekOffs($paidLeaveRule, $daysInMonth, $month, $year);

        // ADDED: department-level behavior flags resolved once per employee
        $isFlexibleTiming = $this->isFlexibleTimingDepartment($employee);
        $suppressLateAndHalfDayDeductions = $this->isOctober2026Exception($month, $year)
            || $this->isNoDeductionsDepartment($employee);

        $presentDays = 0;
        $absentDays = 0;
        $halfDays = 0;
        $leaveDays = 0;
        $lateDays = 0;
        $holidayDays = 0;
        $weekOffDays = 0;
        $totalLateMinutes = 0;

        // ADDED: flat 8-hour reference shift used only for flexible-timing departments
        $flexibleShiftMinutes = 8 * 60;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $dailyRoster = $rosters->get($date);

            if ($dailyRoster && $dailyRoster->is_weekoff == 1) {
                $weekOffDays++;
                continue;
            }

            if (in_array($date, $holidays)) {
                $holidayDays++;
                continue;
            }

            if (!$dailyRoster && in_array($date, $employeeWeekOffDates)) {
                $weekOffDays++;
                continue;
            }

            $attendance = $attendanceData->get($date);

            if ($attendance) {
                $status = 'P';
                $loginTime = Carbon::parse($attendance->punch_in_time);
                $graceEnd = null;

                if ($isFlexibleTiming) {
                    // ADDED: Flexible-timing departments ignore roster shift login/logout/grace
                    // entirely. Evaluate purely on worked duration against a flat 8-hour reference
                    // shift. No late-login concept applies here since no login time is enforced,
                    // so $graceEnd intentionally stays null and the late-detection block below
                    // is skipped naturally for these employees.
                    if (!empty($attendance->punch_out_time)) {
                        $logoutTime = Carbon::parse($attendance->punch_out_time);
                        if ($logoutTime->lt($loginTime)) {
                            $logoutTime->addDay();
                        }

                        $workedMinutes = $loginTime->diffInMinutes($logoutTime);
                        $halfDayMinutes = $flexibleShiftMinutes / 2;

                        if ($workedMinutes < $halfDayMinutes) {
                            $status = 'H';
                        }
                    }
                } elseif ($dailyRoster && !empty($dailyRoster->shift_id)) {
                    $shiftLogin = Carbon::parse($date . ' ' . $dailyRoster->login_time);
                    $shiftLogout = Carbon::parse($date . ' ' . $dailyRoster->logout_time);
                    $graceEnd = $shiftLogin->copy()->addMinutes((int) $dailyRoster->grace_time);

                    if ($shiftLogout->lte($shiftLogin)) {
                        $shiftLogout->addDay();
                    }

                    if (!empty($attendance->punch_out_time)) {
                        $logoutTime = Carbon::parse($attendance->punch_out_time);
                        if ($logoutTime->lt($loginTime)) {
                            $logoutTime->addDay();
                        }

                        $shiftDurationMinutes = $shiftLogin->diffInMinutes($shiftLogout);
                        $halfDayMinutes = $shiftDurationMinutes / 2;
                        $workedMinutes = $loginTime->diffInMinutes($logoutTime);

                        if ($workedMinutes < $halfDayMinutes) {
                            $status = 'H';
                        }
                    }
                }

                if ($status !== 'H' && $graceEnd && $loginTime->gt($graceEnd)) {
                    $lateDays++;
                    $totalLateMinutes += $graceEnd->diffInMinutes($loginTime);
                }

                if ($status === 'H') {
                    $halfDays++;
                } else {
                    $presentDays++;
                }
                continue;
            }

            if (in_array($date, $leaveDates)) {
                $leaveDays++;
                continue;
            }

            $absentDays++;
        }

        $monthlyPaidLeaves = (float) ($paidLeaveRule->monthly ?? 0);

        // --- NEW CLIENT LOGIC: Week Offs absorb Absent days BEFORE LOP calculation ---
        // e.g. 4 week offs + 4 absents = 0 LOP | 4 week offs + 5 absents = 1 LOP | 2 week offs + 3 absents = 1 LOP
        // $weekOffAbsenceOffset = min($absentDays, $weekOffDays);
        // $absentDaysAfterWeekOffOffset = $absentDays - $weekOffAbsenceOffset;

        $weekOffAbsenceOffset = 0;
        $absentDaysAfterWeekOffOffset = $absentDays;

        // CHANGED: half-day's 0.5-day contribution to the LOP pool is suppressed whenever this
        // month/department falls under the deduction exception (October 2026, or a permanently
        // flagged "No Deductions" department). The raw $halfDays count below is untouched and
        // keeps reporting the true figure for display/attendance-grid purposes.
        $halfDayLopContribution = $suppressLateAndHalfDayDeductions ? 0 : ($halfDays * 0.5);

        $totalLeaveAndAbsentDays = $leaveDays + $absentDaysAfterWeekOffOffset + $halfDayLopContribution;

        // --- NEW CLIENT LOGIC: Offset LOP/Absences with Extra OT Shifts ---
        $otOffset = 0;
        $payableOtDays = $otDaysAvailable;

        // NEW: date-wise OT is never consumed by the absence offset (see calculatePayroll()).
        if ($applyOtOffset && $otDaysAvailable > 0 && $totalLeaveAndAbsentDays > 0) {
            $otOffset = min($totalLeaveAndAbsentDays, $otDaysAvailable);
            $totalLeaveAndAbsentDays -= $otOffset;
            $payableOtDays = $otDaysAvailable - $otOffset; // Whatever is remaining gets PAID out
        }

        $paidLeaveDays = min($totalLeaveAndAbsentDays, $monthlyPaidLeaves);
        // Any absent day not covered by Paid Leaves OR Extra OT shifts falls under LOP (Extra Leave Days)
        $extraLeaveDays = max(0, $totalLeaveAndAbsentDays - $monthlyPaidLeaves);

        $totalLateHours = round($totalLateMinutes / 60, 2);
        $lateLopDays = floor($totalLateHours / 8);
        $remainingLateHours = round($totalLateHours - ($lateLopDays * 8), 2);

        return [
            'working_days' => $daysInMonth,
            'present_days' => $presentDays,
            'absent_days' => $absentDays, // raw absent count — unchanged, still shown as-is in attendance grid/export
            'half_days' => $halfDays,
            'late_days' => $lateDays,
            'leave_days' => $leaveDays,
            'paid_leave_days' => $paidLeaveDays,
            'extra_leave_days' => $extraLeaveDays,
            'weekoff_absence_offset' => $weekOffAbsenceOffset,
            'payable_ot_days' => $payableOtDays,
            'week_offs' => $weekOffDays,
            'holidays' => $holidayDays,
            'total_late_minutes' => $totalLateMinutes,
            'total_late_hours' => $totalLateHours,
            'late_lop_days' => $lateLopDays,
            'remaining_late_hours' => $remainingLateHours,
            // ADDED: carried through so calculateDeductions() can skip the Late Login LOP row
            'suppress_late_half_deductions' => $suppressLateAndHalfDayDeductions,
            'is_flexible_timing' => $isFlexibleTiming,
        ];
    }

    /* (OLD, kept for reference — not used anywhere) */
    public function getMonthlyAttendanceGridjok($clientId, $month, $year, $departmentId = null)
    {
        $query = Employee::with(['department'])
            ->where('client_id', $clientId)
            ->where('status', 1)
            ->where('trash', 0);

        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
        }

        $employees = $query->get();
        $groupedEmployees = $employees->sortBy(function ($emp) {
            $priority = $emp->department ? $emp->department->priority : 99999;
            return sprintf('%05d-%s', $priority, $emp->employee_name);
        })->groupBy(function ($emp) {
            return $emp->department ? $emp->department->department_name : 'No Department';
        });

        $departmentPolicies = PaidLeave::where('client_id', $clientId)->where('status', 1)->where('trash', 0)->get()->keyBy('department_id');

        $holidays = Holiday::with('departments')
            ->where('client_id', $clientId)->where('status', 1)->where('trash', 0)
            ->whereMonth('date', $month)->whereYear('date', $year)->get();

        $approvedLeaves = \App\Models\EmployeeLeaveDate::whereHas('leave', function ($q) use ($clientId) {
            $q->where('client_id', $clientId)->where('status', 1)->where('trash', 0);
        })->with('leave')->whereMonth('date', $month)->whereYear('date', $year)->get();

        $leavesMap = [];
        foreach ($approvedLeaves as $al) {
            $dateKey = Carbon::parse($al->date)->format('Y-m-d');
            $leavesMap[$al->leave->employee_id . '_' . $dateKey] = (int) $al->is_paid;
        }

        $rosters = DB::table('rl_employee_rosters_tbl')
            ->leftJoin('rl_shifts_tbl', 'rl_employee_rosters_tbl.shift_id', '=', 'rl_shifts_tbl.id')
            ->where('rl_employee_rosters_tbl.client_id', $clientId)
            ->whereMonth('rl_employee_rosters_tbl.roster_date', $month)
            ->whereYear('rl_employee_rosters_tbl.roster_date', $year)
            ->select('rl_employee_rosters_tbl.*', 'rl_shifts_tbl.login_time', 'rl_shifts_tbl.logout_time', 'rl_shifts_tbl.grace_time')
            ->get()
            ->keyBy(function ($r) {
                return $r->employee_id . '_' . Carbon::parse($r->roster_date)->format('Y-m-d');
            });

        $attendanceData = Attendance::where('client_id', $clientId)
            ->whereMonth('punch_in_time', $month)->whereYear('punch_in_time', $year)->get()
            ->keyBy(function ($row) {
                return $row->emp_id . '_' . Carbon::parse($row->punch_in_time)->format('Y-m-d');
            });

        $attendanceStatus = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        foreach ($employees as $employee) {
            $deptPolicy = $departmentPolicies->get($employee->department_id);

            $employeeHolidays = $holidays->filter(function ($h) use ($employee) {
                if ($h->departments->isEmpty())
                    return true;
                return $h->departments->contains('id', $employee->department_id);
            })->map(function ($h) {
                return Carbon::parse($h->date)->format('Y-m-d');
            })->toArray();

            $legacyWeekOffDates = $this->calculateWeekOffs($deptPolicy, $daysInMonth, $month, $year);

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $key = $employee->id . '_' . $date;

                $attendance = $attendanceData->get($key);
                $dailyRoster = $rosters->get($key);

                if ($dailyRoster && $dailyRoster->is_weekoff == 1) {
                    $attendanceStatus[$employee->id][$date] = ['status' => 'W', 'in' => null, 'out' => null];
                    continue;
                }

                if ($attendance) {
                    $status = 'P';
                    $inTime = Carbon::parse($attendance->punch_in_time)->format('h:i A');
                    $outTime = !empty($attendance->punch_out_time) ? Carbon::parse($attendance->punch_out_time)->format('h:i A') : '--:--';
                    $loginTime = Carbon::parse($attendance->punch_in_time);
                    $graceEnd = null;

                    if ($dailyRoster && !empty($dailyRoster->shift_id)) {
                        $shiftLogin = Carbon::parse($date . ' ' . $dailyRoster->login_time);
                        $graceEnd = $shiftLogin->copy()->addMinutes((int) $dailyRoster->grace_time);
                        $shiftLogout = Carbon::parse($date . ' ' . $dailyRoster->logout_time);

                        if ($shiftLogout->lte($shiftLogin)) {
                            $shiftLogout->addDay();
                        }

                        if (!empty($attendance->punch_out_time)) {
                            $logoutTime = Carbon::parse($attendance->punch_out_time);
                            if ($logoutTime->lt($loginTime)) {
                                $logoutTime->addDay();
                            }
                            $shiftDurationMinutes = $shiftLogin->diffInMinutes($shiftLogout);
                            $halfDayMinutes = $shiftDurationMinutes / 2;
                            $workedMinutes = $loginTime->diffInMinutes($logoutTime);

                            if ($workedMinutes < $halfDayMinutes) {
                                $status = 'H';
                            }
                        }
                    }

                    if ($status !== 'H' && $graceEnd && $loginTime->gt($graceEnd)) {
                        $status = 'L';
                    }

                    $attendanceStatus[$employee->id][$date] = ['status' => $status, 'in' => $inTime, 'out' => $outTime];
                    continue;
                }

                if (isset($leavesMap[$key])) {
                    $attendanceStatus[$employee->id][$date] = ['status' => $leavesMap[$key] === 1 ? 'PL' : 'UL', 'in' => null, 'out' => null];
                    continue;
                }
                if (in_array($date, $employeeHolidays)) {
                    $attendanceStatus[$employee->id][$date] = ['status' => 'HO', 'in' => null, 'out' => null];
                    continue;
                }

                if (!$dailyRoster && in_array($date, $legacyWeekOffDates)) {
                    $attendanceStatus[$employee->id][$date] = ['status' => 'W', 'in' => null, 'out' => null];
                    continue;
                }

                $attendanceStatus[$employee->id][$date] = ['status' => 'A', 'in' => null, 'out' => null];
            }
        }

        return [
            'groupedEmployees' => $groupedEmployees,
            'attendanceStatus' => $attendanceStatus,
            'daysInMonth' => $daysInMonth
        ];
    }

    public function getMonthlyAttendanceGrid($clientId, $month, $year, $departmentId = null)
    {
        $query = Employee::with(['department'])
            ->where('client_id', $clientId)
            ->where('status', 1)
            ->where('trash', 0);

        if (!empty($departmentId)) {
            $query->where('department_id', $departmentId);
        }

        $employees = $query->get();
        $groupedEmployees = $employees->sortBy(function ($emp) {
            $priority = $emp->department ? $emp->department->priority : 99999;
            return sprintf('%05d-%s', $priority, $emp->employee_name);
        })->groupBy(function ($emp) {
            return $emp->department ? $emp->department->department_name : 'No Department';
        });

        // CHANGED: bulk-resolve employee-specific policies with department fallback, 2 queries total
        $policyMap = PaidLeave::resolveMapForEmployees($clientId, $employees);

        $holidays = Holiday::with('departments')
            ->where('client_id', $clientId)->where('status', 1)->where('trash', 0)
            ->whereMonth('date', $month)->whereYear('date', $year)->get();

        $approvedLeaves = \App\Models\EmployeeLeaveDate::whereHas('leave', function ($q) use ($clientId) {
            $q->where('client_id', $clientId)->where('status', 1)->where('trash', 0);
        })->with('leave')->whereMonth('date', $month)->whereYear('date', $year)->get();

        $leavesMap = [];
        foreach ($approvedLeaves as $al) {
            $dateKey = Carbon::parse($al->date)->format('Y-m-d');
            $leavesMap[$al->leave->employee_id . '_' . $dateKey] = (int) $al->is_paid;
        }

        $rosters = DB::table('rl_employee_rosters_tbl')
            ->leftJoin('rl_shifts_tbl', 'rl_employee_rosters_tbl.shift_id', '=', 'rl_shifts_tbl.id')
            ->where('rl_employee_rosters_tbl.client_id', $clientId)
            ->whereMonth('rl_employee_rosters_tbl.roster_date', $month)
            ->whereYear('rl_employee_rosters_tbl.roster_date', $year)
            ->select('rl_employee_rosters_tbl.*', 'rl_shifts_tbl.login_time', 'rl_shifts_tbl.logout_time', 'rl_shifts_tbl.grace_time')
            ->get()
            ->keyBy(function ($r) {
                return $r->employee_id . '_' . Carbon::parse($r->roster_date)->format('Y-m-d');
            });

        $attendanceData = Attendance::where('client_id', $clientId)
            ->whereMonth('punch_in_time', $month)->whereYear('punch_in_time', $year)->get()
            ->keyBy(function ($row) {
                return $row->emp_id . '_' . Carbon::parse($row->punch_in_time)->format('Y-m-d');
            });

        $attendanceStatus = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        foreach ($employees as $employee) {
            // CHANGED: pull this employee's resolved policy (employee-specific or department fallback) from the map
            $deptPolicy = $policyMap[$employee->id] ?? null;

            // ADDED: flexible-timing flag for this employee's department, used in the loop below
            $isFlexibleTimingDept = $this->isFlexibleTimingDepartment($employee);

            $employeeHolidays = $holidays->filter(function ($h) use ($employee) {
                if ($h->departments->isEmpty())
                    return true;
                return $h->departments->contains('id', $employee->department_id);
            })->map(function ($h) {
                return Carbon::parse($h->date)->format('Y-m-d');
            })->toArray();

            $legacyWeekOffDates = $this->calculateWeekOffs($deptPolicy, $daysInMonth, $month, $year);

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $key = $employee->id . '_' . $date;

                $attendance = $attendanceData->get($key);
                $dailyRoster = $rosters->get($key);

                if ($dailyRoster && $dailyRoster->is_weekoff == 1) {
                    $attendanceStatus[$employee->id][$date] = ['status' => 'W', 'in' => null, 'out' => null];
                    continue;
                }

                if ($attendance) {
                    $status = 'P';
                    $inTime = Carbon::parse($attendance->punch_in_time)->format('h:i A');
                    $outTime = !empty($attendance->punch_out_time) ? Carbon::parse($attendance->punch_out_time)->format('h:i A') : '--:--';
                    $loginTime = Carbon::parse($attendance->punch_in_time);
                    $graceEnd = null;

                    // CHANGED: flexible-timing departments ignore roster shift timings entirely
                    // and are evaluated purely on worked duration against a flat 8-hour shift.
                    if ($isFlexibleTimingDept) {
                        if (!empty($attendance->punch_out_time)) {
                            $logoutTime = Carbon::parse($attendance->punch_out_time);
                            if ($logoutTime->lt($loginTime)) {
                                $logoutTime->addDay();
                            }
                            $workedMinutes = $loginTime->diffInMinutes($logoutTime);
                            if ($workedMinutes < (8 * 60) / 2) {
                                $status = 'H';
                            }
                        }
                    } elseif ($dailyRoster && !empty($dailyRoster->shift_id)) {
                        $shiftLogin = Carbon::parse($date . ' ' . $dailyRoster->login_time);
                        $graceEnd = $shiftLogin->copy()->addMinutes((int) $dailyRoster->grace_time);
                        $shiftLogout = Carbon::parse($date . ' ' . $dailyRoster->logout_time);

                        if ($shiftLogout->lte($shiftLogin)) {
                            $shiftLogout->addDay();
                        }

                        if (!empty($attendance->punch_out_time)) {
                            $logoutTime = Carbon::parse($attendance->punch_out_time);
                            if ($logoutTime->lt($loginTime)) {
                                $logoutTime->addDay();
                            }
                            $shiftDurationMinutes = $shiftLogin->diffInMinutes($shiftLogout);
                            $halfDayMinutes = $shiftDurationMinutes / 2;
                            $workedMinutes = $loginTime->diffInMinutes($logoutTime);

                            if ($workedMinutes < $halfDayMinutes) {
                                $status = 'H';
                            }
                        }
                    }

                    if ($status !== 'H' && $graceEnd && $loginTime->gt($graceEnd)) {
                        $status = 'L';
                    }

                    $attendanceStatus[$employee->id][$date] = ['status' => $status, 'in' => $inTime, 'out' => $outTime];
                    continue;
                }

                if (isset($leavesMap[$key])) {
                    $attendanceStatus[$employee->id][$date] = ['status' => $leavesMap[$key] === 1 ? 'PL' : 'UL', 'in' => null, 'out' => null];
                    continue;
                }
                if (in_array($date, $employeeHolidays)) {
                    $attendanceStatus[$employee->id][$date] = ['status' => 'HO', 'in' => null, 'out' => null];
                    continue;
                }

                if (!$dailyRoster && in_array($date, $legacyWeekOffDates)) {
                    $attendanceStatus[$employee->id][$date] = ['status' => 'W', 'in' => null, 'out' => null];
                    continue;
                }

                $attendanceStatus[$employee->id][$date] = ['status' => 'A', 'in' => null, 'out' => null];
            }
        }

        return [
            'groupedEmployees' => $groupedEmployees,
            'attendanceStatus' => $attendanceStatus,
            'daysInMonth' => $daysInMonth
        ];
    }

    // CHANGED: added optional $employee param so flexible-timing departments can force a flat 8-hour working day
    private function calculateSalaryAfterLOP($grossSalary, $attendance, $daysInMonth, $rosters, Employee $employee = null)
    {
        $lopDays = (float) ($attendance['extra_leave_days'] ?? 0);

        $perDaySalary = $daysInMonth > 0 ? round($grossSalary / $daysInMonth, 2) : 0;
        $lopAmount = round($lopDays * $perDaySalary, 2);

        $salaryAfterLop = round(max(0, $grossSalary - $lopAmount), 2);

        $workingHoursPerDay = 8;

        // ADDED: flexible-timing departments always use a flat 8-hour working day, ignoring
        // any roster shift durations that might otherwise be aggregated below.
        $isFlexibleTiming = $employee ? $this->isFlexibleTimingDepartment($employee) : false;

        if (!$isFlexibleTiming) {
            $totalShiftHours = 0;
            $shiftCount = 0;

            if ($rosters && $rosters->isNotEmpty()) {
                foreach ($rosters as $dailyRoster) {
                    if (!empty($dailyRoster->shift_id) && !empty($dailyRoster->login_time) && !empty($dailyRoster->logout_time)) {
                        $startTime = Carbon::parse($dailyRoster->login_time);
                        $endTime = Carbon::parse($dailyRoster->logout_time);

                        if ($endTime->lte($startTime)) {
                            $endTime->addDay();
                        }

                        $minutes = $startTime->diffInMinutes($endTime);
                        if ($minutes > 0) {
                            $totalShiftHours += ($minutes / 60);
                            $shiftCount++;
                        }
                    }
                }
            }

            if ($shiftCount > 0) {
                $workingHoursPerDay = $totalShiftHours / $shiftCount;
            }
        }

        $perHourSalary = $workingHoursPerDay > 0 ? round($perDaySalary / $workingHoursPerDay, 2) : 0;
        $perMinuteSalary = $workingHoursPerDay > 0 ? round($perDaySalary / ($workingHoursPerDay * 60), 2) : 0;

        return [
            'lop_days' => round($lopDays, 2),
            'per_day_salary' => $perDaySalary,
            'per_hour_salary' => $perHourSalary,
            'per_minute_salary' => $perMinuteSalary,
            'lop_amount' => $lopAmount,
            'salary_after_lop' => $salaryAfterLop,
        ];
    }

    private function calculateEarnings(Employee $employee, $ot)
    {
        $earningRows = [];
        $salaryBreakupTotal = 0;
        $basicPay = 0;
        $hra = 0;
        $grossSalary = $employee->gross_salary;

        $earnings = Earnings::where('employee_id', $employee->id)
            ->where('status', 1)
            ->where('trash', 0)
            ->orderBy('id')
            ->get();

        if ($earnings->isEmpty()) {
            $earnings = Earnings::whereNull('employee_id')
                ->where('created_by', $employee->client_id)
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('id')
                ->get();
        }

        foreach ($earnings as $earning) {
            $title = strtoupper(trim($earning->earnings_title));
            $isBasic = str_contains($title, 'BASIC') || str_contains($title, 'BASE');

            if ($isBasic) {
                $amount = $earning->value_type == 1
                    ? ($grossSalary * $earning->earnings_percent) / 100
                    : $earning->earnings_money;

                $basicPay = round($amount, 2);

                $earningRows[] = [
                    'title' => $earning->earnings_title,
                    'amount' => $basicPay,
                    'value_type' => $earning->value_type,
                    'percent' => $earning->earnings_percent,
                ];

                $salaryBreakupTotal += $basicPay;

                $hra = round($basicPay * 0.40, 2);
                $earningRows[] = [
                    'title' => 'HRA',
                    'amount' => $hra,
                    'value_type' => 1,
                    'percent' => 40,
                ];

                $salaryBreakupTotal += $hra;
                continue;
            }

            if (str_contains($title, 'HRA') || str_contains($title, 'HOUSE RENT') || str_contains($title, 'OTHER ALLOWANCE')) {
                continue;
            }

            $amount = $earning->value_type == 1
                ? ($grossSalary * $earning->earnings_percent) / 100
                : $earning->earnings_money;

            $amount = round($amount, 2);

            $earningRows[] = [
                'title' => $earning->earnings_title,
                'amount' => $amount,
                'value_type' => $earning->value_type,
                'percent' => $earning->earnings_percent,
            ];

            $salaryBreakupTotal += $amount;
        }

        $otherAllowance = round(max(0, $grossSalary - $salaryBreakupTotal), 2);

        if ($otherAllowance > 0) {
            $earningRows[] = [
                'title' => 'Other Allowance',
                'amount' => $otherAllowance,
                'value_type' => 2,
                'percent' => 0,
            ];
            $salaryBreakupTotal += $otherAllowance;
        }

        if (($ot['ot_amount'] ?? 0) > 0) {
            $otDays = round((float) ($ot['ot_days'] ?? 0), 2);
            $otHoursDecimal = (float) ($ot['ot_hours'] ?? 0);
            $otHoursFormatted = $this->formatHoursMinutes($otHoursDecimal);

            $otDaysLabel = (floor($otDays) == $otDays) ? (string) (int) $otDays : number_format($otDays, 2);
            $dayWord = ($otDays == 1) ? 'Day' : 'Days';

            $otTitle = $otHoursDecimal > 0
                ? sprintf('Overtime (%s %s / %s)', $otDaysLabel, $dayWord, $otHoursFormatted)
                : sprintf('Overtime (%s %s)', $otDaysLabel, $dayWord);

            $earningRows[] = [
                'title' => $otTitle,
                'amount' => round($ot['ot_amount'], 2),
                'value_type' => 2,
                'percent' => 0,
            ];
        }

        return [
            'basic_pay' => round($basicPay, 2),
            'hra' => round($hra, 2),
            'other_allowance' => round($otherAllowance, 2),
            'earning_rows' => $earningRows,
            'salary_breakup_total' => round($salaryBreakupTotal, 2),
            'total_earnings' => round($salaryBreakupTotal, 2),
        ];
    }

    private function formatHoursMinutes($decimalHours)
    {
        $decimalHours = max(0, (float) $decimalHours);
        $totalMinutes = (int) round($decimalHours * 60);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return "{$hours}h {$minutes}m";
    }

    private function calculateOT($employee, $month, $year)
    {
        $ot = EmployeeOT::where('client_id', $employee->client_id)
            ->where('employee_id', $employee->id)
            ->where('ot_month', $month)
            ->where('ot_year', $year)
            ->where('status', 1)
            ->where('trash', 0)
            ->first();

        if (!$ot) {
            return [
                'ot_days' => 0,
                'ot_hours' => 0,
                'ot_rate' => 0,
                'ot_amount' => 0,
                'ot_source' => 'manual',
            ];
        }

        return [
            'ot_days' => $ot->ot_days,
            'ot_hours' => $ot->ot_hours,
            'ot_rate' => $ot->ot_rate,
            'ot_amount' => round($ot->ot_amount, 2),
            // 'dates' = entered on the Assign OT screen (date-wise, paid in full)
            // 'manual' = entered on the Manage OT screen (day/hour based, legacy offset rule applies)
            'ot_source' => $ot->ot_source ?? 'manual',
        ];
    }

    private function calculateAdvanceRecovery($employee)
    {
        $advanceRecovery = 0;
        $advanceId = null;
        $recoveryType = null;

        $advance = EmployeeAdvance::where('client_id', $employee->client_id)
            ->where('employee_id', $employee->id)
            ->where('status', 1)
            ->where('trash', 0)
            ->where('balance_amount', '>', 0)
            ->oldest('advance_date')
            ->first();

        if (!$advance) {
            return [
                'advance_id' => null,
                'advance_recovery' => 0,
                'recovery_type' => null,
            ];
        }

        $advanceId = $advance->id;
        $recoveryType = $advance->recovery_type;

        if ($advance->recovery_type == 1) {
            $advanceRecovery = min($advance->monthly_recovery_amount, $advance->balance_amount);
        } else {
            $advanceRecovery = $advance->balance_amount;
        }

        return [
            'advance_id' => $advanceId,
            'advance_recovery' => round($advanceRecovery, 2),
            'recovery_type' => $recoveryType,
        ];
    }

    private function calculateTDS(Employee $employee)
    {
        $monthlyGross = (float) ($employee->gross_salary ?? 0);
        $taxRegime = 2;
        $standardDeduction = 75000;
        $annualGross = $monthlyGross * 12;

        $taxableIncome = max(0, $annualGross - $standardDeduction);
        $tax = 0;
        $slabPercentage = 0;

        if ($taxableIncome <= 400000) {
            $slabPercentage = 0;
            $tax = 0;
        } elseif ($taxableIncome <= 800000) {
            $slabPercentage = 5;
            $tax = ($taxableIncome - 400000) * 0.05;
        } elseif ($taxableIncome <= 1200000) {
            $slabPercentage = 10;
            $tax = 20000 + (($taxableIncome - 800000) * 0.10);
        } elseif ($taxableIncome <= 1600000) {
            $slabPercentage = 15;
            $tax = 60000 + (($taxableIncome - 1200000) * 0.15);
        } elseif ($taxableIncome <= 2000000) {
            $slabPercentage = 20;
            $tax = 120000 + (($taxableIncome - 1600000) * 0.20);
        } elseif ($taxableIncome <= 2400000) {
            $slabPercentage = 25;
            $tax = 200000 + (($taxableIncome - 2000000) * 0.25);
        } else {
            $slabPercentage = 30;
            $tax = 300000 + (($taxableIncome - 2400000) * 0.30);
        }

        $rebate = 0;
        if ($taxableIncome <= 1200000) {
            $rebate = min($tax, 60000);
            $tax = max(0, $tax - $rebate);
        }

        $cess = $tax * 0.04;
        $annualTds = $tax + $cess;
        $monthlyTds = $annualTds / 12;

        return [
            'tds' => round($monthlyTds, 2),
            'tds_percentage' => round($slabPercentage, 2),
            'tds_amount' => round($monthlyTds, 2),
            'tax_regime' => $taxRegime,
            'annual_gross' => round($annualGross, 2),
            'standard_deduction' => $standardDeduction,
            'taxable_income' => round($taxableIncome, 2),
            'income_tax' => round($tax, 2),
            'rebate' => round($rebate, 2),
            'cess' => round($cess, 2),
            'annual_tds' => round($annualTds, 2),
            'monthly_tds' => round($monthlyTds, 2),
        ];
    }

    private function calculateDeductions(Employee $employee, $earnings, $advance, $attendance, $tds, $salary)
    {
        $deductionRows = [];
        $totalDeductions = 0;

        $grossSalary = (float) ($employee->gross_salary ?? 0);
        $pfApplicable = (int) ($employee->pf_applicable ?? 0);

        $pf = 0;
        $esi = 0;
        $pt = 0;
        $tdsAmount = 0;
        $lateLoginAmount = 0;

        $deductions = Deductions::where('employee_id', $employee->id)
            ->where('status', 1)
            ->where('trash', 0)
            ->orderBy('id')
            ->get();

        if ($deductions->isEmpty()) {
            $deductions = Deductions::whereNull('employee_id')
                ->where('created_by', $employee->client_id)
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('id')
                ->get();
        }

        foreach ($deductions as $deduction) {
            $title = strtoupper(trim($deduction->deduction_title));

            if (str_contains($title, 'PF') || str_contains($title, 'PROVIDENT FUND')) {
                continue;
            }

            if (str_contains($title, 'PT') || str_contains($title, 'PROFESSIONAL')) {
                if ($grossSalary <= 15000) {
                    $amount = 0;
                } elseif ($grossSalary <= 20000) {
                    $amount = 150;
                } else {
                    $amount = 200;
                }
            } elseif ($deduction->value_type == 1) {
                if (str_contains($title, 'ESI')) {
                    $amount = $grossSalary <= 21000
                        ? ($grossSalary * $deduction->deduction_percent) / 100
                        : 0;
                } else {
                    $amount = ($grossSalary * $deduction->deduction_percent) / 100;
                }
            } else {
                if (str_contains($title, 'ESI')) {
                    $amount = $grossSalary <= 21000
                        ? $deduction->deduction_money
                        : 0;
                } else {
                    $amount = $deduction->deduction_money;
                }
            }

            $amount = round($amount, 2);

            if (str_contains($title, 'ESI')) {
                $esi += $amount;
            } elseif (str_contains($title, 'PT') || str_contains($title, 'PROFESSIONAL')) {
                $pt += $amount;
            }

            $deductionRows[] = [
                'title' => $deduction->deduction_title,
                'amount' => $amount,
                'value_type' => $deduction->value_type,
                'percent' => $deduction->deduction_percent,
            ];

            $totalDeductions += $amount;
        }

        if ($pfApplicable == 1) {
            $salaryAfterLop = (float) ($salary['salary_after_lop'] ?? 0);
            $pfWage = $salaryAfterLop * 0.50;

            if ($pfWage > 25000) {
                $pfWage = 15000;
            }

            $pf = round(($pfWage * 12) / 100, 2);

            $deductionRows[] = [
                'title' => 'PF',
                'amount' => $pf,
                'value_type' => 1,
                'percent' => 12,
            ];

            $totalDeductions += $pf;
        }

        $tdsAmount = round($tds['tds_amount'] ?? 0, 2);
        $deductionRows[] = [
            'title' => 'TDS',
            'amount' => $tdsAmount,
            'value_type' => 1,
            'percent' => $tds['tds_percentage'] ?? 0,
        ];
        $totalDeductions += $tdsAmount;

        if (($advance['advance_recovery'] ?? 0) > 0) {
            $advanceRecovery = round($advance['advance_recovery'], 2);

            $deductionRows[] = [
                'title' => 'Advance Recovery',
                'amount' => $advanceRecovery,
                'value_type' => 2,
                'percent' => 0,
            ];

            $totalDeductions += $advanceRecovery;
        }

        // CHANGED: skip the Late Login LOP row entirely whenever the attendance calc flagged
        // suppression (October 2026 exception, or a permanently flagged "No Deductions" department).
        // total_late_minutes itself stays untouched for accurate reporting elsewhere — only the
        // monetary deduction is withheld.
        $suppressLateDeduction = (bool) ($attendance['suppress_late_half_deductions'] ?? false);
        $totalLateMinutes = (int) ($attendance['total_late_minutes'] ?? 0);
        if ($totalLateMinutes > 0 && !$suppressLateDeduction) {
            $perMinuteSalary = (float) ($salary['per_minute_salary'] ?? 0);
            $lateLoginAmount = round($totalLateMinutes * $perMinuteSalary, 2);

            $deductionRows[] = [
                'title' => 'Late Login LOP',
                'amount' => $lateLoginAmount,
                'value_type' => 2,
                'percent' => 0,
            ];

            $totalDeductions += $lateLoginAmount;
        }

        return [
            'pf' => round($pf, 2),
            'esi' => round($esi, 2),
            'pt' => round($pt, 2),
            'tds' => $tdsAmount,
            'late_login_amount' => round($lateLoginAmount, 2),
            'other_deductions' => round(max(0, $totalDeductions - ($pf + $esi + $pt + $tdsAmount)), 2),
            'deduction_rows' => $deductionRows,
            'total_deductions' => round($totalDeductions, 2),
        ];
    }

    private function calculateNetSalary($salary, $earnings, $ot, $deductions, $advance, $tds)
    {
        $grossEarnings = $salary['salary_after_lop'] + ($ot['ot_amount'] ?? 0);
        $netSalary = $grossEarnings - $deductions['total_deductions'];

        return [
            'gross_earnings' => round($grossEarnings, 2),
            'net_salary' => round(max(0, $netSalary), 2),
        ];
    }

    private function preparePayrollArray($grossSalary, $attendance, $salary, $earnings, $ot, $advance, $tds, $deductions, $netSalary)
    {
        return [
            'gross_salary' => round($grossSalary, 2),
            'basic_pay' => round($earnings['basic_pay'], 2),
            'hra' => round($earnings['hra'], 2),
            'salary_after_lop' => round($salary['salary_after_lop'], 2),
            'working_days' => $attendance['working_days'],
            'present_days' => $attendance['present_days'],
            'absent_days' => $attendance['absent_days'],
            'half_days' => $attendance['half_days'],
            'late_days' => $attendance['late_days'],
            'leave_days' => $attendance['leave_days'],
            'paid_leave_days' => $attendance['paid_leave_days'],
            'extra_leave_days' => $attendance['extra_leave_days'],
            'week_offs' => $attendance['week_offs'],
            'holidays' => $attendance['holidays'],
            'weekoff_absence_offset' => $attendance['weekoff_absence_offset'] ?? 0, // ADDED
            'total_late_minutes' => $attendance['total_late_minutes'],
            'total_late_hours' => $attendance['total_late_hours'],
            'late_lop_days' => $attendance['late_lop_days'],
            'lop_days' => round($salary['lop_days'], 2),
            'lop_amount' => round($salary['lop_amount'], 2),
            'per_day_salary' => round($salary['per_day_salary'], 2),
            'per_hour_salary' => round($salary['per_hour_salary'], 2),
            'per_minute_salary' => round($salary['per_minute_salary'], 2),
            'earning_rows' => $earnings['earning_rows'],
            'total_earnings' => round($earnings['total_earnings'] + ($ot['ot_amount'] ?? 0), 2),
            'ot_days' => $ot['ot_days'],
            'ot_hours' => $ot['ot_hours'],
            'ot_hours_formatted' => $this->formatHoursMinutes((float) ($ot['ot_hours'] ?? 0)),
            'ot_rate' => round($ot['ot_rate'], 2),
            'ot_amount' => round($ot['ot_amount'], 2),
            'ot_source' => $ot['ot_source'] ?? 'manual',
            'advance_id' => $advance['advance_id'],
            'advance_recovery' => round($advance['advance_recovery'], 2),
            'tds' => round($tds['tds'], 2),
            'tds_percentage' => $tds['tds_percentage'],
            'tds_amount' => $tds['tds_amount'],
            'tax_regime' => $tds['tax_regime'],
            'pf' => round($deductions['pf'], 2),
            'esi' => round($deductions['esi'], 2),
            'pt' => round($deductions['pt'], 2),
            'other_deductions' => round($deductions['other_deductions'], 2),
            'total_deductions' => round($deductions['total_deductions'], 2),
            'deduction_rows' => $deductions['deduction_rows'],
            'gross_earnings' => round($netSalary['gross_earnings'], 2),
            'net_salary' => round($netSalary['net_salary'], 2),
        ];
    }

    private function processAdvanceRecovery($employee, $advanceId, $recoverAmount, $payrollId)
    {
        if (!$advanceId || $recoverAmount <= 0) {
            return;
        }

        $advance = EmployeeAdvance::find($advanceId);

        if (!$advance) {
            return;
        }

        AdvanceRecovery::create([
            'client_id' => $employee->client_id,
            'advance_id' => $advance->id,
            'employee_id' => $employee->id,
            'payroll_id' => $payrollId,
            'recovery_date' => now()->toDateString(),
            'recovered_amount' => $recoverAmount,
            'balance_before' => $advance->balance_amount,
            'balance_after' => max(0, $advance->balance_amount - $recoverAmount),
            'recovery_type' => 1,
            'payment_mode' => 5,
            'remarks' => 'Recovered through Payroll',
            'created_by' => $employee->client_id,
        ]);

        $advance->recovered_amount += $recoverAmount;
        $advance->balance_amount -= $recoverAmount;
        $advance->save();
    }
}