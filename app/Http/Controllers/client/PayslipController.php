<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeOT;
use App\Models\EmployeeOTDate;
use App\Models\PaidLeave;
use App\Models\Holiday;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\OfficeTiming;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Leave;
use App\Models\Earnings;
use App\Models\Deductions;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PayrollService;

class PayslipController extends Controller
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

    public function viewPayslipjok($employeeId, $month, $year)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $employee = Employee::where('client_id', $user->id)->findOrFail($employeeId);

            $payroll = Payroll::where('client_id', $user->id)
                ->where('employee_id', $employeeId)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if (!$payroll) {
                return redirect()->route('managePayroll')
                    ->with('error', 'Payroll not generated for selected month.');
            }

            $leaves = abs((float) $payroll->absent_days);
            $lopDays = abs((float) $payroll->lop_days);
            $totalDays = (float) $payroll->working_days;
            $paidDays = max(0, $totalDays - $lopDays);

            $earningRows = PayrollItem::where('payroll_id', $payroll->id)
                ->where('type', 'earning')
                ->orderBy('id')
                ->get();

            $deductionRows = PayrollItem::where('payroll_id', $payroll->id)
                ->where('type', 'deduction')
                ->orderBy('id')
                ->get();

            // FETCH ACTUAL OT DIRECTLY TO BYPASS LOP OFFSETS OR CACHE
            $employeeOtRecord = EmployeeOT::where('client_id', $user->id)
                ->where('employee_id', $employeeId)
                ->where('ot_month', $month)
                ->where('ot_year', $year)
                ->where('trash', 0)
                ->first();

            $otHoursFormatted = '-';
            $otAmount = 0;

            if ($employeeOtRecord) {
                $otAmount = (float) $employeeOtRecord->ot_amount;

                $decimalHours = max(0, (float) $employeeOtRecord->ot_hours);
                $totalMinutes = (int) round($decimalHours * 60);
                $hours = intdiv($totalMinutes, 60);
                $minutes = $totalMinutes % 60;

                if ($hours > 0 || $minutes > 0) {
                    $otHoursFormatted = "{$hours}h {$minutes}m";
                }
            }

            $client = Client::where('id', $user->id)->first();

            // ADDED: October 2026 one-time deduction-exception banner message.
            // Purely a display-layer check — the suppression itself already happened
            // inside PayrollService when this payroll was generated.
            $deductionExceptionMessage = ((int) $month === 10 && (int) $year === 2026)
                ? 'Deductions for Late Logins and Half Days have not been applied to this month\'s payroll, as employees were not informed of this policy before the start of the October 2026 payroll cycle. This policy will be enforced from November 2026 onward.'
                : null;

            return view('client.payslip', [
                'client' => $client,
                'employee' => $employee,
                'month' => $month,
                'year' => $year,
                'grossSalary' => $payroll->gross_salary,
                'lopAmount' => $payroll->lop_amount,
                'salaryAfterLop' => $payroll->salary_after_lop,
                'totalDays' => $totalDays,
                'paidDays' => $paidDays,
                'absent' => $payroll->absent_days,
                'paidLeaves' => $payroll->paid_leave_days,
                'leaves' => $leaves,
                'lopDays' => $lopDays,
                'otHoursFormatted' => $otHoursFormatted,
                'otAmount' => $otAmount,
                'earningRows' => $earningRows,
                'deductionRows' => $deductionRows,
                'totalEarnings' => $payroll->total_earnings,
                'totalDeductions' => $payroll->total_deductions,
                'netPay' => $payroll->net_salary,
                'deductionExceptionMessage' => $deductionExceptionMessage, // ADDED
            ]);
        } catch (\Throwable $e) {
            Log::error('Payslip Error', [
                'employee_id' => $employeeId,
                'month' => $month,
                'year' => $year,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('managePayroll')
                ->with('error', 'Something went wrong while loading the payslip.');
        }
    }

    public function viewPayslip($employeeId, $month, $year)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $employee = Employee::where('client_id', $user->id)->findOrFail($employeeId);

            $payroll = Payroll::where('client_id', $user->id)
                ->where('employee_id', $employeeId)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if (!$payroll) {
                return redirect()->route('managePayroll')
                    ->with('error', 'Payroll not generated for selected month.');
            }

            $leaves = abs((float) $payroll->absent_days);
            $lopDays = abs((float) $payroll->lop_days);
            $totalDays = (float) $payroll->working_days;
            $paidDays = max(0, $totalDays - $lopDays);

            // ADDED: reconciliation breakdown — every bucket below sums exactly to
            // $totalDays (working_days), since the day-by-day attendance loop in
            // PayrollService assigns each calendar day to exactly one bucket.
            $presentDays = (float) ($payroll->present_days ?? 0);
            $halfDays = (float) ($payroll->half_days ?? 0);
            $leaveDays = (float) ($payroll->leave_days ?? 0);
            $absentDaysRaw = (float) ($payroll->absent_days ?? 0);
            $weekOffDays = (float) ($payroll->week_offs ?? 0);
            $holidayDays = (float) ($payroll->holidays ?? 0);
            $paidLeaveDaysUsed = (float) ($payroll->paid_leave_days ?? 0);
            $weekOffAbsenceOffset = (float) ($payroll->weekoff_absence_offset ?? 0);

            // Legacy fallback: payroll rows generated BEFORE this fix won't have
            // week_offs/holidays/weekoff_absence_offset persisted (they default to 0).
            // For those older rows only, derive a combined "non-working" figure so the
            // breakdown still visibly reconciles to working_days, rather than showing
            // misleading zeros.
            $isLegacyRow = ($weekOffDays == 0 && $holidayDays == 0 && $weekOffAbsenceOffset == 0
                && $totalDays > ($presentDays + $halfDays + $leaveDays + $absentDaysRaw));
            if ($isLegacyRow) {
                $weekOffDays = max(0, $totalDays - $presentDays - $halfDays - $leaveDays - $absentDaysRaw);
                $holidayDays = 0;
                $weekOffAbsenceOffset = max(0, min($absentDaysRaw, $weekOffDays));
            }

            $netAbsentAfterOffset = max(0, $absentDaysRaw - $weekOffAbsenceOffset);

            $earningRows = PayrollItem::where('payroll_id', $payroll->id)
                ->where('type', 'earning')
                ->orderBy('id')
                ->get();

            $deductionRows = PayrollItem::where('payroll_id', $payroll->id)
                ->where('type', 'deduction')
                ->orderBy('id')
                ->get();

            // FETCH ACTUAL OT DIRECTLY TO BYPASS LOP OFFSETS OR CACHE
            $employeeOtRecord = EmployeeOT::where('client_id', $user->id)
                ->where('employee_id', $employeeId)
                ->where('ot_month', $month)
                ->where('ot_year', $year)
                ->where('trash', 0)
                ->first();

            $otHoursFormatted = '-';
            $otAmount = 0;
            $otDays = 0;

            if ($employeeOtRecord) {
                $otAmount = (float) $employeeOtRecord->ot_amount;
                $otDays = (float) $employeeOtRecord->ot_days;

                $decimalHours = max(0, (float) $employeeOtRecord->ot_hours);
                $totalMinutes = (int) round($decimalHours * 60);
                $hours = intdiv($totalMinutes, 60);
                $minutes = $totalMinutes % 60;

                if ($hours > 0 || $minutes > 0) {
                    $otHoursFormatted = "{$hours}h {$minutes}m";
                }
            }

            $client = Client::where('id', $user->id)->first();

            // ADDED: October 2026 one-time deduction-exception banner message.
            $deductionExceptionMessage = ((int) $month === 10 && (int) $year === 2026)
                ? 'Deductions for Late Logins and Half Days have not been applied to this month\'s payroll, as employees were not informed of this policy before the start of the October 2026 payroll cycle. This policy will be enforced from November 2026 onward.'
                : null;

            return view('client.payslip', [
                'client' => $client,
                'employee' => $employee,
                'month' => $month,
                'year' => $year,
                'grossSalary' => $payroll->gross_salary,
                'lopAmount' => $payroll->lop_amount,
                'salaryAfterLop' => $payroll->salary_after_lop,
                'totalDays' => $totalDays,
                'paidDays' => $paidDays,
                'absent' => $payroll->absent_days,
                'paidLeaves' => $payroll->paid_leave_days,
                'leaves' => $leaves,
                'lopDays' => $lopDays,
                'otHoursFormatted' => $otHoursFormatted,
                'otAmount' => $otAmount,
                'otDays' => $otDays,
                'earningRows' => $earningRows,
                'deductionRows' => $deductionRows,
                'totalEarnings' => $payroll->total_earnings,
                'totalDeductions' => $payroll->total_deductions,
                'netPay' => $payroll->net_salary,
                'deductionExceptionMessage' => $deductionExceptionMessage,
                // ADDED: full day-accounting breakdown for the payslip
                'presentDays' => $presentDays,
                'halfDays' => $halfDays,
                'leaveDays' => $leaveDays,
                'absentDaysRaw' => $absentDaysRaw,
                'weekOffDays' => $weekOffDays,
                'holidayDays' => $holidayDays,
                'paidLeaveDaysUsed' => $paidLeaveDaysUsed,
                'weekOffAbsenceOffset' => $weekOffAbsenceOffset,
                'netAbsentAfterOffset' => $netAbsentAfterOffset,
            ]);
        } catch (\Throwable $e) {
            Log::error('Payslip Error', [
                'employee_id' => $employeeId,
                'month' => $month,
                'year' => $year,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('managePayroll')
                ->with('error', 'Something went wrong while loading the payslip.');
        }
    }

    public function generatePayrollForMonth(
        Request $request,
        PayrollService $payrollService
    ) {
        try {

            $this->authorizeAccess();

            $user = $this->getLoggedInUser();

            $request->validate([
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2000|max:2100',
                'recover_advance' => 'nullable|boolean',
            ]);

            $payrollService->generatePayroll(
                $user->id,
                $request->month,
                $request->year,
                $request->boolean('recover_advance')
            );

            return redirect()->back()->with(
                'success',
                $request->boolean('recover_advance')
                ? 'Payroll generated successfully and employee advance recoveries have been processed.'
                : 'Payroll generated successfully.'
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Generate Payroll Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'client_id' => $user->id ?? null,
            ]);

            return redirect()->back()->with(
                'error',
                'Something went wrong while generating payroll.'
            );
        }
    }

    public function exportPayrolljok(Request $request, \App\Services\PayrollService $payrollService)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $month = (int) ($request->month ?? date('m'));
            $year = (int) ($request->year ?? date('Y'));

            $employeesByDept = \App\Models\Employee::with(['department', 'designationInfo'])
                ->where('client_id', $user->id)
                ->where('status', 1)
                ->where('trash', 0)
                ->get()
                ->sortBy(function ($emp) {
                    $priority = $emp->department ? $emp->department->priority : 99999;
                    $designation = $emp->designationInfo ? $emp->designationInfo->designation_name : 'ZZZZZ';
                    return sprintf('%05d-%s-%s', $priority, $designation, $emp->employee_name);
                })
                ->groupBy(function ($emp) {
                    return $emp->department ? $emp->department->department_name : 'General Administration';
                });

            $clientName = \App\Models\Client::find($user->id)->client_name ?? 'PRIMS HRMS';
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            $filename = "Salary_Statement_{$monthName}_{$year}.xls";

            header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            $xml = fn($value) => htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

            $stringCell = function ($value, $style = 'DataCenter') use ($xml) {
                return '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . $xml($value) . '</Data></Cell>';
            };

            $numberCell = function ($value, $style = 'DataCenter') {
                return '<Cell ss:StyleID="' . $style . '"><Data ss:Type="Number">' . (is_numeric($value) ? $value : 0) . '</Data></Cell>';
            };

            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<?mso-application progid="Excel.Sheet"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:html="http://www.w3.org/TR/REC-html40">';

            echo '<Styles>
            <Style ss:ID="Default" ss:Name="Normal">
                <Alignment ss:Vertical="Center"/>
                <Borders/>
                <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
                <Interior/>
                <NumberFormat/>
                <Protection/>
            </Style>
            <Style ss:ID="Title">
                <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="16" ss:Bold="1"/>
            </Style>
            <Style ss:ID="SubTitle">
                <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="12" ss:Bold="1"/>
            </Style>
            <Style ss:ID="Header">
                <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
                <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Bold="1"/>
                <Interior ss:Color="#E5E7EB" ss:Pattern="Solid"/>
            </Style>
            <Style ss:ID="DataCenter">
                <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
            </Style>
            <Style ss:ID="DataLeft">
                <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
            </Style>
            <Style ss:ID="CurrencyData">
                <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
                <NumberFormat ss:Format="#,##0"/>
            </Style>
            <Style ss:ID="DecimalData">
                <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
                <NumberFormat ss:Format="0.00"/>
            </Style>
        </Styles>';

            foreach ($employeesByDept as $deptName => $employees) {

                $processedEmployees = [];
                $dynamicDeductionHeaders = [];

                foreach ($employees as $employee) {
                    $data = $payrollService->calculatePayroll($employee, $month, $year);

                    if (isset($data['error'])) {
                        \Illuminate\Support\Facades\Log::warning('Payroll Export Employee Calculation Failed', [
                            'employee_id' => $employee->id,
                            'message' => $data['error']
                        ]);
                        continue;
                    }

                    $customDeductionsMap = [];
                    foreach ($data['deduction_rows'] as $dRow) {
                        $title = strtoupper(trim($dRow['title']));
                        $amt = (float) $dRow['amount'];

                        if (
                            !str_contains($title, 'PF') &&
                            !str_contains($title, 'PROVIDENT FUND') &&
                            !str_contains($title, 'ESI') &&
                            !str_contains($title, 'PT') &&
                            !str_contains($title, 'PROFESSIONAL') &&
                            !str_contains($title, 'TDS') &&
                            !str_contains($title, 'ADVANCE RECOVERY') &&
                            $title !== 'LATE LOGIN LOP'
                        ) {
                            if (!in_array($title, $dynamicDeductionHeaders)) {
                                $dynamicDeductionHeaders[] = $title;
                            }
                            $customDeductionsMap[$title] = $amt;
                        }
                    }

                    // FETCH EXACT OT TO BYPASS CACHE IN EXPORT
                    $otRecord = EmployeeOT::where('client_id', $user->id)
                        ->where('employee_id', $employee->id)
                        ->where('ot_month', $month)
                        ->where('ot_year', $year)
                        ->where('trash', 0)
                        ->first();

                    $data['export_ot_amount'] = 0;
                    $data['export_ot_formatted'] = '-';

                    if ($otRecord) {
                        $data['export_ot_amount'] = (float) $otRecord->ot_amount;
                        $decimalHours = max(0, (float) $otRecord->ot_hours);
                        $totalMinutes = (int) round($decimalHours * 60);
                        $hours = intdiv($totalMinutes, 60);
                        $minutes = $totalMinutes % 60;
                        if ($hours > 0 || $minutes > 0) {
                            $data['export_ot_formatted'] = "{$hours}h {$minutes}m";
                        } elseif ((int) $otRecord->ot_days > 0) {
                            // Date-wise OT stores days, not hours.
                            $data['export_ot_formatted'] = (int) $otRecord->ot_days . ' Day(s)';
                        }
                    }

                    $processedEmployees[] = [
                        'employee' => $employee,
                        'data' => $data,
                        'customDeductionsMap' => $customDeductionsMap
                    ];
                }

                $sheetName = substr(preg_replace('/[\/\\\\?*\[\]:]/', '_', $deptName), 0, 31);

                echo '<Worksheet ss:Name="' . $xml($sheetName) . '">';
                echo '<Table>';

                $headersList = [
                    'Sl.No',
                    'EMPLOYEE NAME',
                    'DESIGNATION',
                    'DEPARTMENT',
                    'GROSS SAL',
                    'PER DAY',
                    'PER MINUTE',
                    'T.D',
                    'W.D',
                    'P.D',
                    'CL',
                    'LOP',
                    'LOP AMT',
                    'Net Gross Sal.',
                    'OT Time',
                    'OT Amount',
                    'Total Late Mins',
                    'Late LOP Amt',
                    'PF Wages',
                    'PF DEDUCT.',
                    'ESI DEDUCT.',
                    'Proff. Tax.',
                    'TDS',
                    'Adv. Recovery'
                ];

                foreach ($dynamicDeductionHeaders as $dynHeader) {
                    $headersList[] = $dynHeader;
                }

                $headersList[] = 'NET SALARY';

                $baseWidths = [
                    40,
                    160,
                    140,
                    100,
                    80,
                    60,
                    70,
                    40,
                    40,
                    40,
                    40,
                    40,
                    70,
                    70,
                    80,
                    80,
                    90,
                    80,
                    70,
                    60,
                    60,
                    60,
                    60,
                    80
                ];

                foreach ($baseWidths as $width) {
                    echo '<Column ss:Width="' . $width . '"/>';
                }
                foreach ($dynamicDeductionHeaders as $dynHeader) {
                    echo '<Column ss:Width="80"/>';
                }
                echo '<Column ss:Width="90"/>';

                $totalColsMerge = count($headersList) - 1;

                echo '<Row ss:Height="25">';
                echo '<Cell ss:MergeAcross="' . $totalColsMerge . '" ss:StyleID="Title">';
                echo '<Data ss:Type="String">' . $xml(strtoupper($clientName)) . '</Data>';
                echo '</Cell></Row>';

                echo '<Row ss:Height="20">';
                echo '<Cell ss:MergeAcross="' . $totalColsMerge . '" ss:StyleID="SubTitle">';
                echo '<Data ss:Type="String">SALARY STATEMENT FOR THE MONTH OF ' .
                    $xml(strtoupper($monthName) . ' ' . $year . ' - ' . strtoupper($deptName)) .
                    '</Data></Cell></Row>';

                echo '<Row><Cell ss:MergeAcross="' . $totalColsMerge . '"><Data ss:Type="String"></Data></Cell></Row>';

                echo '<Row ss:Height="40">';
                foreach ($headersList as $header) {
                    echo '<Cell ss:StyleID="Header"><Data ss:Type="String">' . $xml($header) . '</Data></Cell>';
                }
                echo '</Row>';

                $sno = 1;

                foreach ($processedEmployees as $procEmp) {
                    $employee = $procEmp['employee'];
                    $data = $procEmp['data'];
                    $customDeductionsMap = $procEmp['customDeductionsMap'];

                    $grossSalary = (float) ($data['gross_salary'] ?? 0);
                    $perDayAmount = (float) ($data['per_day_salary'] ?? 0);
                    $perMinuteAmount = (float) ($data['per_minute_salary'] ?? 0);

                    $totalDays = (float) ($data['working_days'] ?? 0);
                    $holidays = (float) ($data['holidays'] ?? 0);
                    $weekOffs = (float) ($data['week_offs'] ?? 0);
                    $workingDays = max(0, $totalDays - ($holidays + $weekOffs));

                    $presentDays = (float) ($data['present_days'] ?? 0);
                    $clDays = (float) ($data['paid_leave_days'] ?? 0);
                    $lopDays = (float) ($data['lop_days'] ?? 0);
                    $lopAmount = (float) ($data['lop_amount'] ?? 0);
                    $netGrossSal = (float) ($data['salary_after_lop'] ?? 0);

                    // Use the securely fetched OT values
                    $otHoursFormatted = $data['export_ot_formatted'];
                    $otAmount = $data['export_ot_amount'];

                    $totalLateMins = (float) ($data['total_late_minutes'] ?? 0);

                    $pfDeduct = (float) ($data['pf'] ?? 0);
                    $esiDeduct = (float) ($data['esi'] ?? 0);
                    $ptDeduct = (float) ($data['pt'] ?? 0);
                    $tdsDeduct = (float) ($data['tds'] ?? 0);
                    $advRecovery = (float) ($data['advance_recovery'] ?? 0);

                    $pfWages = $pfDeduct > 0 ? round($pfDeduct / 0.12) : 0;

                    $lateLopAmt = 0;
                    foreach ($data['deduction_rows'] as $dRow) {
                        if (strtoupper(trim($dRow['title'])) === 'LATE LOGIN LOP') {
                            $lateLopAmt = (float) $dRow['amount'];
                            break;
                        }
                    }

                    $netSalary = (float) ($data['net_salary'] ?? 0);

                    echo '<Row>';
                    echo $numberCell($sno++);
                    echo $stringCell($employee->employee_name ?? '', 'DataLeft');
                    echo $stringCell($employee->designationInfo->designation_name ?? '--', 'DataLeft');
                    echo $stringCell($deptName);

                    echo $numberCell(round($grossSalary), 'CurrencyData');
                    echo $numberCell(round($perDayAmount, 2), 'DecimalData');
                    echo $numberCell(round($perMinuteAmount, 2), 'DecimalData');

                    echo $numberCell($totalDays);
                    echo $numberCell($workingDays);
                    echo $numberCell($presentDays);
                    echo $numberCell($clDays);
                    echo $numberCell(round($lopDays, 2));

                    echo $lopAmount > 0 ? $numberCell(round($lopAmount), 'CurrencyData') : $stringCell('-');

                    echo $numberCell(round($netGrossSal), 'CurrencyData');

                    echo $otHoursFormatted !== '-' ? $stringCell($otHoursFormatted) : $stringCell('-');
                    echo $otAmount > 0 ? $numberCell(round($otAmount), 'CurrencyData') : $stringCell('-');

                    echo $numberCell(round($totalLateMins, 2), 'DecimalData');

                    echo $lateLopAmt > 0 ? $numberCell(round($lateLopAmt), 'CurrencyData') : $stringCell('-');

                    echo $pfWages > 0 ? $numberCell(round($pfWages), 'CurrencyData') : $stringCell('-');
                    echo $pfDeduct > 0 ? $numberCell(round($pfDeduct), 'CurrencyData') : $stringCell('-');
                    echo $esiDeduct > 0 ? $numberCell(round($esiDeduct), 'CurrencyData') : $stringCell('-');
                    echo $ptDeduct > 0 ? $numberCell(round($ptDeduct), 'CurrencyData') : $stringCell('-');
                    echo $tdsDeduct > 0 ? $numberCell(round($tdsDeduct), 'CurrencyData') : $stringCell('-');
                    echo $advRecovery > 0 ? $numberCell(round($advRecovery), 'CurrencyData') : $stringCell('-');

                    foreach ($dynamicDeductionHeaders as $dynHeader) {
                        $dynAmt = $customDeductionsMap[$dynHeader] ?? 0;
                        echo $dynAmt > 0 ? $numberCell(round($dynAmt), 'CurrencyData') : $stringCell('-');
                    }

                    echo $numberCell(round($netSalary), 'CurrencyData');
                    echo '</Row>';
                }

                echo '</Table>';
                echo '</Worksheet>';
            }

            echo '</Workbook>';
            exit;

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Payroll Export Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()->with('error', 'Unable to export payroll.');
        }
    }

    public function exportPayroll(Request $request, \App\Services\PayrollService $payrollService)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $month = (int) ($request->month ?? date('m'));
            $year = (int) ($request->year ?? date('Y'));

            $employeesByDept = \App\Models\Employee::with(['department', 'designationInfo'])
                ->where('client_id', $user->id)
                ->where('status', 1)
                ->where('trash', 0)
                ->get()
                ->sortBy(function ($emp) {
                    $priority = $emp->department ? $emp->department->priority : 99999;
                    $designation = $emp->designationInfo ? $emp->designationInfo->designation_name : 'ZZZZZ';
                    return sprintf('%05d-%s-%s', $priority, $designation, $emp->employee_name);
                })
                ->groupBy(function ($emp) {
                    return $emp->department ? $emp->department->department_name : 'General Administration';
                });

            $clientName = \App\Models\Client::find($user->id)->client_name ?? 'PRIMS HRMS';
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            $filename = "Salary_Statement_{$monthName}_{$year}.xls";

            header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            $xml = fn($value) => htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

            $stringCell = function ($value, $style = 'DataCenter') use ($xml) {
                return '<Cell ss:StyleID="' . $style . '"><Data ss:Type="String">' . $xml($value) . '</Data></Cell>';
            };

            $numberCell = function ($value, $style = 'DataCenter') {
                return '<Cell ss:StyleID="' . $style . '"><Data ss:Type="Number">' . (is_numeric($value) ? $value : 0) . '</Data></Cell>';
            };

            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<?mso-application progid="Excel.Sheet"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:o="urn:schemas-microsoft-com:office:office"
            xmlns:x="urn:schemas-microsoft-com:office:excel"
            xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
            xmlns:html="http://www.w3.org/TR/REC-html40">';

            echo '<Styles>
            <Style ss:ID="Default" ss:Name="Normal">
                <Alignment ss:Vertical="Center"/>
                <Borders/>
                <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
                <Interior/>
                <NumberFormat/>
                <Protection/>
            </Style>
            <Style ss:ID="Title">
                <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="16" ss:Bold="1"/>
            </Style>
            <Style ss:ID="SubTitle">
                <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="12" ss:Bold="1"/>
            </Style>
            <Style ss:ID="Header">
                <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
                <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Bold="1"/>
                <Interior ss:Color="#E5E7EB" ss:Pattern="Solid"/>
            </Style>
            <Style ss:ID="DataCenter">
                <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
            </Style>
            <Style ss:ID="DataLeft">
                <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
            </Style>
            <Style ss:ID="CurrencyData">
                <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
                <NumberFormat ss:Format="#,##0"/>
            </Style>
            <Style ss:ID="DecimalData">
                <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                </Borders>
                <NumberFormat ss:Format="0.00"/>
            </Style>
            <Style ss:ID="TotalRow">
                <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
                </Borders>
                <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Bold="1"/>
                <Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/>
                <NumberFormat ss:Format="#,##0"/>
            </Style>
            <Style ss:ID="TotalLabel">
                <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
                <Borders>
                    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
                    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
                </Borders>
                <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Bold="1"/>
                <Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/>
            </Style>
        </Styles>';

            foreach ($employeesByDept as $deptName => $employees) {

                $processedEmployees = [];
                $dynamicDeductionHeaders = [];

                foreach ($employees as $employee) {
                    $data = $payrollService->calculatePayroll($employee, $month, $year);

                    if (isset($data['error'])) {
                        \Illuminate\Support\Facades\Log::warning('Payroll Export Employee Calculation Failed', [
                            'employee_id' => $employee->id,
                            'message' => $data['error']
                        ]);
                        continue;
                    }

                    $customDeductionsMap = [];
                    foreach ($data['deduction_rows'] as $dRow) {
                        $title = strtoupper(trim($dRow['title']));
                        $amt = (float) $dRow['amount'];

                        if (
                            !str_contains($title, 'PF') &&
                            !str_contains($title, 'PROVIDENT FUND') &&
                            !str_contains($title, 'ESI') &&
                            !str_contains($title, 'PT') &&
                            !str_contains($title, 'PROFESSIONAL') &&
                            !str_contains($title, 'TDS') &&
                            !str_contains($title, 'ADVANCE RECOVERY') &&
                            $title !== 'LATE LOGIN LOP'
                        ) {
                            if (!in_array($title, $dynamicDeductionHeaders)) {
                                $dynamicDeductionHeaders[] = $title;
                            }
                            $customDeductionsMap[$title] = $amt;
                        }
                    }

                    // FETCH EXACT OT TO BYPASS CACHE IN EXPORT
                    $otRecord = EmployeeOT::where('client_id', $user->id)
                        ->where('employee_id', $employee->id)
                        ->where('ot_month', $month)
                        ->where('ot_year', $year)
                        ->where('trash', 0)
                        ->first();

                    $data['export_ot_amount'] = 0;
                    $data['export_ot_formatted'] = '-';

                    if ($otRecord) {
                        $data['export_ot_amount'] = (float) $otRecord->ot_amount;
                        $decimalHours = max(0, (float) $otRecord->ot_hours);
                        $totalMinutes = (int) round($decimalHours * 60);
                        $hours = intdiv($totalMinutes, 60);
                        $minutes = $totalMinutes % 60;
                        if ($hours > 0 || $minutes > 0) {
                            $data['export_ot_formatted'] = "{$hours}h {$minutes}m";
                        } elseif ((int) $otRecord->ot_days > 0) {
                            // Date-wise OT stores days, not hours.
                            $data['export_ot_formatted'] = (int) $otRecord->ot_days . ' Day(s)';
                        }
                    }

                    $processedEmployees[] = [
                        'employee' => $employee,
                        'data' => $data,
                        'customDeductionsMap' => $customDeductionsMap
                    ];
                }

                // Dynamic deduction headers can differ per-employee discovery order within a dept,
                // so gather them a second time across ALL processed employees of this department
                // before building the header row, to ensure every column is present for totals too.
                $dynamicDeductionHeaders = [];
                foreach ($processedEmployees as $procEmp) {
                    foreach (array_keys($procEmp['customDeductionsMap']) as $title) {
                        if (!in_array($title, $dynamicDeductionHeaders)) {
                            $dynamicDeductionHeaders[] = $title;
                        }
                    }
                }

                $sheetName = substr(preg_replace('/[\/\\\\?*\[\]:]/', '_', $deptName), 0, 31);

                echo '<Worksheet ss:Name="' . $xml($sheetName) . '">';
                echo '<Table>';

                // ADDED: EMP ID (right after Sl.No) and JOINING DATE (right after DEPARTMENT)
                $headersList = [
                    'Sl.No',
                    'EMP ID',
                    'EMPLOYEE NAME',
                    'DESIGNATION',
                    'DEPARTMENT',
                    'JOINING DATE',
                    'GROSS SAL',
                    'PER DAY',
                    'PER MINUTE',
                    'T.D',
                    'W.D',
                    'P.D',
                    'CL',
                    'LOP',
                    'LOP AMT',
                    'Net Gross Sal.',
                    'OT Time',
                    'OT Amount',
                    'Total Late Mins',
                    'Late LOP Amt',
                    'PF Wages',
                    'PF DEDUCT.',
                    'ESI DEDUCT.',
                    'Proff. Tax.',
                    'TDS',
                    'Adv. Recovery'
                ];

                foreach ($dynamicDeductionHeaders as $dynHeader) {
                    $headersList[] = $dynHeader;
                }

                $headersList[] = 'NET SALARY';

                // ADDED: two extra widths for EMP ID and JOINING DATE columns
                $baseWidths = [
                    40,   // Sl.No
                    80,   // EMP ID
                    160,  // EMPLOYEE NAME
                    140,  // DESIGNATION
                    100,  // DEPARTMENT
                    90,   // JOINING DATE
                    80,   // GROSS SAL
                    60,   // PER DAY
                    70,   // PER MINUTE
                    40,   // T.D
                    40,   // W.D
                    40,   // P.D
                    40,   // CL
                    40,   // LOP
                    70,   // LOP AMT
                    70,   // Net Gross Sal.
                    80,   // OT Time
                    80,   // OT Amount
                    90,   // Total Late Mins
                    80,   // Late LOP Amt
                    70,   // PF Wages
                    60,   // PF DEDUCT.
                    60,   // ESI DEDUCT.
                    60,   // Proff. Tax.
                    60,   // TDS
                    80    // Adv. Recovery
                ];

                foreach ($baseWidths as $width) {
                    echo '<Column ss:Width="' . $width . '"/>';
                }
                foreach ($dynamicDeductionHeaders as $dynHeader) {
                    echo '<Column ss:Width="80"/>';
                }
                echo '<Column ss:Width="90"/>';

                $totalColsMerge = count($headersList) - 1;

                echo '<Row ss:Height="25">';
                echo '<Cell ss:MergeAcross="' . $totalColsMerge . '" ss:StyleID="Title">';
                echo '<Data ss:Type="String">' . $xml(strtoupper($clientName)) . '</Data>';
                echo '</Cell></Row>';

                echo '<Row ss:Height="20">';
                echo '<Cell ss:MergeAcross="' . $totalColsMerge . '" ss:StyleID="SubTitle">';
                echo '<Data ss:Type="String">SALARY STATEMENT FOR THE MONTH OF ' .
                    $xml(strtoupper($monthName) . ' ' . $year . ' - ' . strtoupper($deptName)) .
                    '</Data></Cell></Row>';

                echo '<Row><Cell ss:MergeAcross="' . $totalColsMerge . '"><Data ss:Type="String"></Data></Cell></Row>';

                echo '<Row ss:Height="40">';
                foreach ($headersList as $header) {
                    echo '<Cell ss:StyleID="Header"><Data ss:Type="String">' . $xml($header) . '</Data></Cell>';
                }
                echo '</Row>';

                // ADDED: October 2026 one-time deduction-exception note in the exported sheet
                if ((int) $month === 10 && (int) $year === 2026) {
                    echo '<Row ss:Height="20">';
                    echo '<Cell ss:MergeAcross="' . $totalColsMerge . '"><Data ss:Type="String">NOTE: Deductions for Late Logins and Half Days have not been applied this month, as employees were not informed of this policy before the October 2026 payroll cycle began. This policy will be enforced from November 2026 onward.</Data></Cell>';
                    echo '</Row>';
                }

                $sno = 1;

                // ADDED: running totals accumulator for every amount column
                $totals = [
                    'gross_salary' => 0,
                    'lop_amount' => 0,
                    'net_gross_sal' => 0,
                    'ot_amount' => 0,
                    'late_lop_amt' => 0,
                    'pf_wages' => 0,
                    'pf_deduct' => 0,
                    'esi_deduct' => 0,
                    'pt_deduct' => 0,
                    'tds_deduct' => 0,
                    'adv_recovery' => 0,
                    'net_salary' => 0,
                ];
                $dynamicTotals = array_fill_keys($dynamicDeductionHeaders, 0);

                foreach ($processedEmployees as $procEmp) {
                    $employee = $procEmp['employee'];
                    $data = $procEmp['data'];
                    $customDeductionsMap = $procEmp['customDeductionsMap'];

                    $grossSalary = (float) ($data['gross_salary'] ?? 0);
                    $perDayAmount = (float) ($data['per_day_salary'] ?? 0);
                    $perMinuteAmount = (float) ($data['per_minute_salary'] ?? 0);

                    $totalDays = (float) ($data['working_days'] ?? 0);
                    $holidays = (float) ($data['holidays'] ?? 0);
                    $weekOffs = (float) ($data['week_offs'] ?? 0);
                    $workingDays = max(0, $totalDays - ($holidays + $weekOffs));

                    $presentDays = (float) ($data['present_days'] ?? 0);
                    $clDays = (float) ($data['paid_leave_days'] ?? 0);
                    $lopDays = (float) ($data['lop_days'] ?? 0);
                    $lopAmount = (float) ($data['lop_amount'] ?? 0);
                    $netGrossSal = (float) ($data['salary_after_lop'] ?? 0);

                    $otHoursFormatted = $data['export_ot_formatted'];
                    $otAmount = $data['export_ot_amount'];

                    $totalLateMins = (float) ($data['total_late_minutes'] ?? 0);

                    $pfDeduct = (float) ($data['pf'] ?? 0);
                    $esiDeduct = (float) ($data['esi'] ?? 0);
                    $ptDeduct = (float) ($data['pt'] ?? 0);
                    $tdsDeduct = (float) ($data['tds'] ?? 0);
                    $advRecovery = (float) ($data['advance_recovery'] ?? 0);

                    $pfWages = $pfDeduct > 0 ? round($pfDeduct / 0.12) : 0;

                    $lateLopAmt = 0;
                    foreach ($data['deduction_rows'] as $dRow) {
                        if (strtoupper(trim($dRow['title'])) === 'LATE LOGIN LOP') {
                            $lateLopAmt = (float) $dRow['amount'];
                            break;
                        }
                    }

                    $netSalary = (float) ($data['net_salary'] ?? 0);

                    // ADDED: accumulate totals
                    $totals['gross_salary'] += $grossSalary;
                    $totals['lop_amount'] += $lopAmount;
                    $totals['net_gross_sal'] += $netGrossSal;
                    $totals['ot_amount'] += $otAmount;
                    $totals['late_lop_amt'] += $lateLopAmt;
                    $totals['pf_wages'] += $pfWages;
                    $totals['pf_deduct'] += $pfDeduct;
                    $totals['esi_deduct'] += $esiDeduct;
                    $totals['pt_deduct'] += $ptDeduct;
                    $totals['tds_deduct'] += $tdsDeduct;
                    $totals['adv_recovery'] += $advRecovery;
                    $totals['net_salary'] += $netSalary;
                    foreach ($dynamicDeductionHeaders as $dynHeader) {
                        $dynamicTotals[$dynHeader] += ($customDeductionsMap[$dynHeader] ?? 0);
                    }

                    $joiningDate = !empty($employee->joining_date)
                        ? \Carbon\Carbon::parse($employee->joining_date)->format('d-m-Y')
                        : '-';

                    echo '<Row>';
                    echo $numberCell($sno++);
                    echo $stringCell($employee->employee_id ?? '-', 'DataLeft');
                    echo $stringCell($employee->employee_name ?? '', 'DataLeft');
                    echo $stringCell($employee->designationInfo->designation_name ?? '--', 'DataLeft');
                    echo $stringCell($deptName);
                    echo $stringCell($joiningDate);

                    echo $numberCell(round($grossSalary), 'CurrencyData');
                    echo $numberCell(round($perDayAmount, 2), 'DecimalData');
                    echo $numberCell(round($perMinuteAmount, 2), 'DecimalData');

                    echo $numberCell($totalDays);
                    echo $numberCell($workingDays);
                    echo $numberCell($presentDays);
                    echo $numberCell($clDays);
                    echo $numberCell(round($lopDays, 2));

                    echo $lopAmount > 0 ? $numberCell(round($lopAmount), 'CurrencyData') : $stringCell('-');

                    echo $numberCell(round($netGrossSal), 'CurrencyData');

                    echo $otHoursFormatted !== '-' ? $stringCell($otHoursFormatted) : $stringCell('-');
                    echo $otAmount > 0 ? $numberCell(round($otAmount), 'CurrencyData') : $stringCell('-');

                    echo $numberCell(round($totalLateMins, 2), 'DecimalData');

                    echo $lateLopAmt > 0 ? $numberCell(round($lateLopAmt), 'CurrencyData') : $stringCell('-');

                    echo $pfWages > 0 ? $numberCell(round($pfWages), 'CurrencyData') : $stringCell('-');
                    echo $pfDeduct > 0 ? $numberCell(round($pfDeduct), 'CurrencyData') : $stringCell('-');
                    echo $esiDeduct > 0 ? $numberCell(round($esiDeduct), 'CurrencyData') : $stringCell('-');
                    echo $ptDeduct > 0 ? $numberCell(round($ptDeduct), 'CurrencyData') : $stringCell('-');
                    echo $tdsDeduct > 0 ? $numberCell(round($tdsDeduct), 'CurrencyData') : $stringCell('-');
                    echo $advRecovery > 0 ? $numberCell(round($advRecovery), 'CurrencyData') : $stringCell('-');

                    foreach ($dynamicDeductionHeaders as $dynHeader) {
                        $dynAmt = $customDeductionsMap[$dynHeader] ?? 0;
                        echo $dynAmt > 0 ? $numberCell(round($dynAmt), 'CurrencyData') : $stringCell('-');
                    }

                    echo $numberCell(round($netSalary), 'CurrencyData');
                    echo '</Row>';
                }

                // ADDED: Total row — one per department worksheet
                if (count($processedEmployees) > 0) {
                    echo '<Row>';
                    // Sl.No, EMP ID, EMPLOYEE NAME merged as "TOTAL" label
                    echo '<Cell ss:MergeAcross="2" ss:StyleID="TotalLabel"><Data ss:Type="String">TOTAL</Data></Cell>';
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // DESIGNATION
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // DEPARTMENT
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // JOINING DATE

                    echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($totals['gross_salary']) . '</Data></Cell>'; // GROSS SAL
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // PER DAY
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // PER MINUTE
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // T.D
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // W.D
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // P.D
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // CL
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // LOP days

                    echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($totals['lop_amount']) . '</Data></Cell>'; // LOP AMT
                    echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($totals['net_gross_sal']) . '</Data></Cell>'; // Net Gross Sal.
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // OT Time
                    echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($totals['ot_amount']) . '</Data></Cell>'; // OT Amount
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // Total Late Mins
                    echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($totals['late_lop_amt']) . '</Data></Cell>'; // Late LOP Amt
                    echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($totals['pf_wages']) . '</Data></Cell>'; // PF Wages
                    echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($totals['pf_deduct']) . '</Data></Cell>'; // PF DEDUCT.
                    echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($totals['esi_deduct']) . '</Data></Cell>'; // ESI DEDUCT.
                    echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($totals['pt_deduct']) . '</Data></Cell>'; // Proff. Tax.
                    echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($totals['tds_deduct']) . '</Data></Cell>'; // TDS
                    echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($totals['adv_recovery']) . '</Data></Cell>'; // Adv. Recovery

                    foreach ($dynamicDeductionHeaders as $dynHeader) {
                        echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($dynamicTotals[$dynHeader]) . '</Data></Cell>';
                    }

                    echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($totals['net_salary']) . '</Data></Cell>'; // NET SALARY
                    echo '</Row>';
                }

                echo '</Table>';
                echo '</Worksheet>';
            }

            echo '</Workbook>';
            exit;

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Payroll Export Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()->with('error', 'Unable to export payroll.');
        }
    }

    public function exportBankPayrolljok(Request $request, \App\Services\PayrollService $payrollService)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $month = $request->month ?? date('m');
            $year = $request->year ?? date('Y');

            $employeesByDept = \App\Models\Employee::with(['department', 'designationInfo'])
                ->where('client_id', $user->id)
                ->where('status', 1)
                ->where('trash', 0)
                ->get()
                ->sortBy(function ($emp) {
                    $priority = $emp->department ? $emp->department->priority : 99999;
                    $designation = $emp->designationInfo ? $emp->designationInfo->designation_name : 'ZZZZZ';

                    return sprintf('%05d-%s-%s', $priority, $designation, $emp->employee_name);
                })
                ->groupBy(function ($emp) {
                    return $emp->department ? $emp->department->department_name : 'No Department';
                });

            $clientName = \App\Models\Client::find($user->id)->client_name ?? 'PRIMS HRMS';
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            $filename = "Bank_Statement_{$monthName}_{$year}.xls";

            header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo '<?xml version="1.0"?>';
            echo '<?mso-application progid="Excel.Sheet"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';

            echo '<Styles>
                    <Style ss:ID="Default" ss:Name="Normal">
                        <Alignment ss:Vertical="Center"/>
                        <Borders/>
                        <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
                        <Interior/>
                        <NumberFormat/>
                        <Protection/>
                    </Style>
                    <Style ss:ID="Title">
                        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                        <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="16" ss:Bold="1"/>
                    </Style>
                    <Style ss:ID="SubTitle">
                        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                        <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="12" ss:Bold="1"/>
                    </Style>
                    <Style ss:ID="Header">
                        <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
                        <Borders>
                            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                        </Borders>
                        <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Bold="1"/>
                        <Interior ss:Color="#E5E7EB" ss:Pattern="Solid"/>
                    </Style>
                    <Style ss:ID="DeptHeader">
                        <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
                        <Borders>
                            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                        </Borders>
                        <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="12" ss:Bold="1"/>
                        <Interior ss:Color="#F3F4F6" ss:Pattern="Solid"/>
                    </Style>
                    <Style ss:ID="DataCenter">
                        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                        <Borders>
                            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                        </Borders>
                    </Style>
                    <Style ss:ID="DataLeft">
                        <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
                        <Borders>
                            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                        </Borders>
                    </Style>
                    <Style ss:ID="StringData">
                        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                        <Borders>
                            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                        </Borders>
                        <NumberFormat ss:Format="@"/>
                    </Style>
                    <Style ss:ID="CurrencyData">
                        <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
                        <Borders>
                            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                        </Borders>
                        <NumberFormat ss:Format="#,##0"/>
                    </Style>
                  </Styles>';

            $totalColumnsCount = 10;

            foreach ($employeesByDept as $deptName => $employees) {
                $sheetName = substr(str_replace(['/', '\\', '?', '*', ':', '[', ']'], '_', $deptName), 0, 31);
                echo '<Worksheet ss:Name="' . htmlspecialchars($sheetName) . '">';
                echo '<Table>';

                echo '<Column ss:Width="40"/>';
                echo '<Column ss:Width="100"/>';
                echo '<Column ss:Width="180"/>';
                echo '<Column ss:Width="160"/>';
                echo '<Column ss:Width="100"/>';
                echo '<Column ss:Width="100"/>';
                echo '<Column ss:Width="160"/>';
                echo '<Column ss:Width="140"/>';
                echo '<Column ss:Width="120"/>';
                echo '<Column ss:Width="120"/>';

                echo '<Row ss:Height="25">';
                echo '<Cell ss:MergeAcross="' . ($totalColumnsCount - 1) . '" ss:StyleID="Title"><Data ss:Type="String">' . strtoupper(htmlspecialchars($clientName)) . '</Data></Cell>';
                echo '</Row>';

                echo '<Row ss:Height="20">';
                echo '<Cell ss:MergeAcross="' . ($totalColumnsCount - 1) . '" ss:StyleID="SubTitle"><Data ss:Type="String">BANK STATEMENT FOR THE MONTH OF ' . strtoupper($monthName) . ' ' . $year . ' - ' . strtoupper(htmlspecialchars($deptName)) . '</Data></Cell>';
                echo '</Row>';

                echo '<Row><Cell ss:MergeAcross="' . ($totalColumnsCount - 1) . '"><Data ss:Type="String"></Data></Cell></Row>';

                echo '<Row ss:Height="30">';
                $headersList = [
                    'Sl.No',
                    'EMPLOYEE ID',
                    'EMPLOYEE NAME',
                    'DESIGNATION',
                    'NET SALARY',
                    'PF AMOUNT',
                    'BANK NAME',
                    'ACCOUNT NUMBER',
                    'IFSC CODE',
                    'ACCOUNT TYPE'
                ];

                foreach ($headersList as $colHeader) {
                    echo '<Cell ss:StyleID="Header"><Data ss:Type="String">' . htmlspecialchars($colHeader) . '</Data></Cell>';
                }
                echo '</Row>';

                $sno = 1;

                echo '<Row ss:Height="20">';
                echo '<Cell ss:StyleID="DataCenter"><Data ss:Type="String"></Data></Cell>';
                echo '<Cell ss:StyleID="DataCenter"><Data ss:Type="String"></Data></Cell>';
                echo '<Cell ss:StyleID="DataCenter"><Data ss:Type="String"></Data></Cell>';
                echo '<Cell ss:MergeAcross="' . ($totalColumnsCount - 4) . '" ss:StyleID="DeptHeader"><Data ss:Type="String">' . strtoupper(htmlspecialchars($deptName)) . '</Data></Cell>';
                echo '</Row>';

                foreach ($employees as $employee) {
                    $data = $payrollService->calculatePayroll($employee, $month, $year);

                    $pfAmount = 0;
                    foreach ($data['deduction_rows'] ?? [] as $deduction) {
                        $title = strtolower(trim($deduction['title']));
                        if ($title === 'pf' || $title === 'provident fund' || $title === 'providentfund') {
                            $pfAmount = $deduction['amount'];
                            break;
                        }
                    }

                    $netSalary = (float) ($data['net_salary'] ?? 0);

                    echo '<Row>';
                    echo '<Cell ss:StyleID="DataCenter"><Data ss:Type="Number">' . $sno++ . '</Data></Cell>';
                    echo '<Cell ss:StyleID="StringData"><Data ss:Type="String">' . htmlspecialchars($employee->employee_id) . '</Data></Cell>';
                    echo '<Cell ss:StyleID="DataLeft"><Data ss:Type="String">' . htmlspecialchars($employee->employee_name) . '</Data></Cell>';
                    echo '<Cell ss:StyleID="DataLeft"><Data ss:Type="String">' . htmlspecialchars($employee->designationInfo->designation_name ?? '--') . '</Data></Cell>';

                    echo '<Cell ss:StyleID="CurrencyData"><Data ss:Type="Number">' . round($netSalary) . '</Data></Cell>';
                    echo '<Cell ss:StyleID="CurrencyData"><Data ss:Type="Number">' . round($pfAmount) . '</Data></Cell>';

                    echo '<Cell ss:StyleID="DataLeft"><Data ss:Type="String">' . htmlspecialchars($employee->bank_name ?? '--') . '</Data></Cell>';
                    echo '<Cell ss:StyleID="StringData"><Data ss:Type="String">' . htmlspecialchars($employee->bank_account_number ?? '--') . '</Data></Cell>';
                    echo '<Cell ss:StyleID="DataCenter"><Data ss:Type="String">' . htmlspecialchars($employee->ifsc_code ?? '--') . '</Data></Cell>';
                    echo '<Cell ss:StyleID="DataCenter"><Data ss:Type="String">' . htmlspecialchars($employee->account_type ?? '--') . '</Data></Cell>';

                    echo '</Row>';
                }

                echo '</Table>';
                echo '</Worksheet>';
            }

            echo '</Workbook>';
            exit;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Bank Payroll Export Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()->with('error', 'Unable to export bank payroll.');
        }
    }

    public function exportBankPayroll(Request $request, \App\Services\PayrollService $payrollService)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $month = $request->month ?? date('m');
            $year = $request->year ?? date('Y');

            $employeesByDept = \App\Models\Employee::with(['department', 'designationInfo'])
                ->where('client_id', $user->id)
                ->where('status', 1)
                ->where('trash', 0)
                ->get()
                ->sortBy(function ($emp) {
                    $priority = $emp->department ? $emp->department->priority : 99999;
                    $designation = $emp->designationInfo ? $emp->designationInfo->designation_name : 'ZZZZZ';

                    return sprintf('%05d-%s-%s', $priority, $designation, $emp->employee_name);
                })
                ->groupBy(function ($emp) {
                    return $emp->department ? $emp->department->department_name : 'No Department';
                });

            $clientName = \App\Models\Client::find($user->id)->client_name ?? 'PRIMS HRMS';
            $monthName = date('F', mktime(0, 0, 0, $month, 1));
            $filename = "Bank_Statement_{$monthName}_{$year}.xls";

            header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo '<?xml version="1.0"?>';
            echo '<?mso-application progid="Excel.Sheet"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';

            echo '<Styles>
                    <Style ss:ID="Default" ss:Name="Normal">
                        <Alignment ss:Vertical="Center"/>
                        <Borders/>
                        <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>
                        <Interior/>
                        <NumberFormat/>
                        <Protection/>
                    </Style>
                    <Style ss:ID="Title">
                        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                        <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="16" ss:Bold="1"/>
                    </Style>
                    <Style ss:ID="SubTitle">
                        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                        <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="12" ss:Bold="1"/>
                    </Style>
                    <Style ss:ID="Header">
                        <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
                        <Borders>
                            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                        </Borders>
                        <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Bold="1"/>
                        <Interior ss:Color="#E5E7EB" ss:Pattern="Solid"/>
                    </Style>
                    <Style ss:ID="DeptHeader">
                        <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
                        <Borders>
                            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                        </Borders>
                        <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="12" ss:Bold="1"/>
                        <Interior ss:Color="#F3F4F6" ss:Pattern="Solid"/>
                    </Style>
                    <Style ss:ID="DataCenter">
                        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                        <Borders>
                            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                        </Borders>
                    </Style>
                    <Style ss:ID="DataLeft">
                        <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
                        <Borders>
                            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                        </Borders>
                    </Style>
                    <Style ss:ID="StringData">
                        <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
                        <Borders>
                            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                        </Borders>
                        <NumberFormat ss:Format="@"/>
                    </Style>
                    <Style ss:ID="CurrencyData">
                        <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
                        <Borders>
                            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
                        </Borders>
                        <NumberFormat ss:Format="#,##0"/>
                    </Style>
                    <Style ss:ID="TotalRow">
                        <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
                        <Borders>
                            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
                        </Borders>
                        <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Bold="1"/>
                        <Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/>
                        <NumberFormat ss:Format="#,##0"/>
                    </Style>
                    <Style ss:ID="TotalLabel">
                        <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
                        <Borders>
                            <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2"/>
                        </Borders>
                        <Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Bold="1"/>
                        <Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/>
                    </Style>
                  </Styles>';

            // ADDED: one extra column for JOINING DATE
            $totalColumnsCount = 11;

            foreach ($employeesByDept as $deptName => $employees) {
                $sheetName = substr(str_replace(['/', '\\', '?', '*', ':', '[', ']'], '_', $deptName), 0, 31);
                echo '<Worksheet ss:Name="' . htmlspecialchars($sheetName) . '">';
                echo '<Table>';

                echo '<Column ss:Width="40"/>';   // Sl.No
                echo '<Column ss:Width="100"/>';  // EMPLOYEE ID
                echo '<Column ss:Width="180"/>';  // EMPLOYEE NAME
                echo '<Column ss:Width="160"/>';  // DESIGNATION
                echo '<Column ss:Width="90"/>';   // JOINING DATE (ADDED)
                echo '<Column ss:Width="100"/>';  // NET SALARY
                echo '<Column ss:Width="100"/>';  // PF AMOUNT
                echo '<Column ss:Width="160"/>';  // BANK NAME
                echo '<Column ss:Width="140"/>';  // ACCOUNT NUMBER
                echo '<Column ss:Width="120"/>';  // IFSC CODE
                echo '<Column ss:Width="120"/>';  // ACCOUNT TYPE

                echo '<Row ss:Height="25">';
                echo '<Cell ss:MergeAcross="' . ($totalColumnsCount - 1) . '" ss:StyleID="Title"><Data ss:Type="String">' . strtoupper(htmlspecialchars($clientName)) . '</Data></Cell>';
                echo '</Row>';

                echo '<Row ss:Height="20">';
                echo '<Cell ss:MergeAcross="' . ($totalColumnsCount - 1) . '" ss:StyleID="SubTitle"><Data ss:Type="String">BANK STATEMENT FOR THE MONTH OF ' . strtoupper($monthName) . ' ' . $year . ' - ' . strtoupper(htmlspecialchars($deptName)) . '</Data></Cell>';
                echo '</Row>';

                echo '<Row><Cell ss:MergeAcross="' . ($totalColumnsCount - 1) . '"><Data ss:Type="String"></Data></Cell></Row>';

                echo '<Row ss:Height="30">';
                $headersList = [
                    'Sl.No',
                    'EMPLOYEE ID',
                    'EMPLOYEE NAME',
                    'DESIGNATION',
                    'JOINING DATE',   // ADDED
                    'NET SALARY',
                    'PF AMOUNT',
                    'BANK NAME',
                    'ACCOUNT NUMBER',
                    'IFSC CODE',
                    'ACCOUNT TYPE'
                ];

                foreach ($headersList as $colHeader) {
                    echo '<Cell ss:StyleID="Header"><Data ss:Type="String">' . htmlspecialchars($colHeader) . '</Data></Cell>';
                }
                echo '</Row>';

                $sno = 1;

                echo '<Row ss:Height="20">';
                echo '<Cell ss:StyleID="DataCenter"><Data ss:Type="String"></Data></Cell>';
                echo '<Cell ss:StyleID="DataCenter"><Data ss:Type="String"></Data></Cell>';
                echo '<Cell ss:StyleID="DataCenter"><Data ss:Type="String"></Data></Cell>';
                echo '<Cell ss:MergeAcross="' . ($totalColumnsCount - 4) . '" ss:StyleID="DeptHeader"><Data ss:Type="String">' . strtoupper(htmlspecialchars($deptName)) . '</Data></Cell>';
                echo '</Row>';

                // ADDED: totals accumulator
                $totalNetSalary = 0;
                $totalPfAmount = 0;
                $rowCount = 0;

                foreach ($employees as $employee) {
                    $data = $payrollService->calculatePayroll($employee, $month, $year);

                    if (isset($data['error'])) {
                        continue;
                    }

                    $pfAmount = 0;
                    foreach ($data['deduction_rows'] ?? [] as $deduction) {
                        $title = strtolower(trim($deduction['title']));
                        if ($title === 'pf' || $title === 'provident fund' || $title === 'providentfund') {
                            $pfAmount = $deduction['amount'];
                            break;
                        }
                    }

                    $netSalary = (float) ($data['net_salary'] ?? 0);

                    $totalNetSalary += $netSalary;
                    $totalPfAmount += $pfAmount;
                    $rowCount++;

                    $joiningDate = !empty($employee->joining_date)
                        ? \Carbon\Carbon::parse($employee->joining_date)->format('d-m-Y')
                        : '-';

                    echo '<Row>';
                    echo '<Cell ss:StyleID="DataCenter"><Data ss:Type="Number">' . $sno++ . '</Data></Cell>';
                    echo '<Cell ss:StyleID="StringData"><Data ss:Type="String">' . htmlspecialchars($employee->employee_id) . '</Data></Cell>';
                    echo '<Cell ss:StyleID="DataLeft"><Data ss:Type="String">' . htmlspecialchars($employee->employee_name) . '</Data></Cell>';
                    echo '<Cell ss:StyleID="DataLeft"><Data ss:Type="String">' . htmlspecialchars($employee->designationInfo->designation_name ?? '--') . '</Data></Cell>';
                    echo '<Cell ss:StyleID="DataCenter"><Data ss:Type="String">' . htmlspecialchars($joiningDate) . '</Data></Cell>';

                    echo '<Cell ss:StyleID="CurrencyData"><Data ss:Type="Number">' . round($netSalary) . '</Data></Cell>';
                    echo '<Cell ss:StyleID="CurrencyData"><Data ss:Type="Number">' . round($pfAmount) . '</Data></Cell>';

                    echo '<Cell ss:StyleID="DataLeft"><Data ss:Type="String">' . htmlspecialchars($employee->bank_name ?? '--') . '</Data></Cell>';
                    echo '<Cell ss:StyleID="StringData"><Data ss:Type="String">' . htmlspecialchars($employee->bank_account_number ?? '--') . '</Data></Cell>';
                    echo '<Cell ss:StyleID="DataCenter"><Data ss:Type="String">' . htmlspecialchars($employee->ifsc_code ?? '--') . '</Data></Cell>';
                    echo '<Cell ss:StyleID="DataCenter"><Data ss:Type="String">' . htmlspecialchars($employee->account_type ?? '--') . '</Data></Cell>';

                    echo '</Row>';
                }

                // ADDED: Total row
                if ($rowCount > 0) {
                    echo '<Row>';
                    echo '<Cell ss:MergeAcross="3" ss:StyleID="TotalLabel"><Data ss:Type="String">TOTAL</Data></Cell>'; // Sl.No..JOINING DATE merged
                    echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($totalNetSalary) . '</Data></Cell>'; // NET SALARY
                    echo '<Cell ss:StyleID="TotalRow"><Data ss:Type="Number">' . round($totalPfAmount) . '</Data></Cell>'; // PF AMOUNT
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // BANK NAME
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // ACCOUNT NUMBER
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // IFSC CODE
                    echo '<Cell ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>'; // ACCOUNT TYPE
                    echo '</Row>';
                }

                echo '</Table>';
                echo '</Worksheet>';
            }

            echo '</Workbook>';
            exit;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Bank Payroll Export Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return redirect()->back()->with('error', 'Unable to export bank payroll.');
        }
    }

    public function exportMonthlyAttendance(Request $request)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $month = $request->month ?? date('m');
            $year = $request->year ?? date('Y');
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            $employees = Employee::with('department')
                ->where('client_id', $user->id)
                ->where('status', 1)
                ->where('trash', 0)
                ->get();

            $groupedEmployees = $employees->sortBy(function ($emp) {
                $priority = $emp->department ? $emp->department->priority : 99999;
                return sprintf('%05d-%s', $priority, $emp->employee_name);
            })->groupBy(function ($emp) {
                return $emp->department ? $emp->department->department_name : 'No Department';
            });

            $officeTiming = OfficeTiming::where('client_id', $user->id)->first();

            $paidLeave = PaidLeave::where('client_id', $user->id)
                ->where('status', 1)
                ->where('trash', 0)
                ->latest('id')
                ->first();

            $weekOffs = $paidLeave->week_offs ?? 0;

            $holidays = Holiday::where('client_id', $user->id)
                ->where('status', 1)
                ->where('trash', 0)
                ->pluck('date')
                ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'))
                ->toArray();

            $attendanceData = Attendance::where('client_id', $user->id)
                ->whereMonth('punch_in_time', $month)
                ->whereYear('punch_in_time', $year)
                ->get()
                ->keyBy(fn($row) => $row->emp_id . '_' . Carbon::parse($row->punch_in_time)->format('Y-m-d'));

            $fileName = "Monthly_Attendance_{$month}_{$year}.xls";

            return response()->streamDownload(function () use ($groupedEmployees, $attendanceData, $officeTiming, $holidays, $weekOffs, $month, $year, $daysInMonth, $user) {

                $companyName = $user->client_name ?? 'Company Name';
                $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');

                echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
                echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
                echo '<style>
                        body { font-family: "Segoe UI", Arial, sans-serif; font-size: 11px; }
                        .title-header { font-size: 16px; font-weight: bold; background: #0f172a; color: #ffffff; padding: 10px; text-align: left; }
                        .dept-header { background-color: #e2e8f0; color: #0f172a; font-weight: bold; font-size: 13px; padding: 8px 10px; text-align: left; text-transform: uppercase; }
                        th { background-color: #f1f5f9; color: #334155; font-weight: bold; border: 1px solid #cbd5e1; padding: 7px; text-align: center; vertical-align: middle; }
                        td { border: 1px solid #cbd5e1; padding: 5px; text-align: center; vertical-align: middle; }
                        .text-start { text-align: left; }
                        .str-format { mso-number-format:"\@"; }
                        .time-cell { font-size: 10px; color: #555555; white-space: nowrap; } 
                      </style></head><body>';

                echo '<table>';
                $totalCols = $daysInMonth + 10;
                echo '<tr><td colspan="' . $totalCols . '" class="title-header">' . htmlspecialchars($companyName) . ' — Attendance Report (' . $monthName . ')</td></tr>';
                echo '<tr><td colspan="' . $totalCols . '" style="height: 10px; border:none;"></td></tr>';

                $sno = 1;

                foreach ($groupedEmployees as $deptName => $deptEmployees) {
                    echo '<tr><td colspan="' . $totalCols . '" class="dept-header">DEPARTMENT: ' . htmlspecialchars($deptName) . '</td></tr>';

                    echo '<tr>';
                    echo '<th style="width: 45px;">S.No</th>';
                    echo '<th class="text-start" style="width: 200px;">Employee Name</th>';
                    echo '<th style="width: 40px; background:#E8F5E9;color:#2E7D32;">P</th>';
                    echo '<th style="width: 40px; background:#FFEBEE;color:#C62828;">A</th>';
                    echo '<th style="width: 40px; background:#FFF8E1;color:#F57F17;">H</th>';
                    echo '<th style="width: 40px; background:#E3F2FD;color:#1565C0;">L</th>';
                    echo '<th style="width: 40px; background:#E8F5E9;color:#1B5E20;">PL</th>';
                    echo '<th style="width: 40px; background:#FFEBEE;color:#B71C1C;">UL</th>';
                    echo '<th style="width: 40px; background:#E0F7FA;color:#006064;">HO</th>';
                    echo '<th style="width: 40px; background:#F5F5F5;color:#616161;">W</th>';

                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $cDate = Carbon::create($year, $month, $day);
                        $bg = $cDate->isSunday() ? 'background:#f8d7da; color:#dc3545;' : '';
                        echo '<th style="width: 70px; ' . $bg . '">' . $day . '</th>';
                    }
                    echo '</tr>';

                    foreach ($deptEmployees as $employee) {
                        $present = 0;
                        $absent = 0;
                        $half = 0;
                        $late = 0;
                        $paidLeaves = 0;
                        $unpaidLeaves = 0;
                        $holidaysCount = 0;
                        $weekOffsCount = 0;
                        $dailyHtml = [];

                        for ($day = 1; $day <= $daysInMonth; $day++) {
                            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                            $attendance = $attendanceData->get($employee->id . '_' . $date);

                            if ($attendance) {
                                $status = 'P';
                                $loginTimeStr = Carbon::parse($attendance->punch_in_time)->format('h:i A');
                                $logoutTimeStr = !empty($attendance->punch_out_time) ? Carbon::parse($attendance->punch_out_time)->format('h:i A') : '--:--';

                                if ($officeTiming) {
                                    $loginTime = Carbon::parse($attendance->punch_in_time);
                                    $officeStart = Carbon::parse($date . ' ' . Carbon::parse($officeTiming->start_time)->format('H:i:s'));
                                    $graceEnd = $officeStart->copy()->addMinutes($officeTiming->grace_time);

                                    if (!empty($attendance->punch_out_time)) {
                                        $workedHours = $loginTime->diffInMinutes(Carbon::parse($attendance->punch_out_time)) / 60;
                                        if ($workedHours < $officeTiming->half_day_limit)
                                            $status = 'H';
                                    }

                                    if ($status != 'H' && $loginTime->gt($graceEnd))
                                        $status = 'L';
                                }

                                if ($status == 'P')
                                    $present++;
                                elseif ($status == 'H')
                                    $half++;
                                elseif ($status == 'L') {
                                    $late++;
                                    $present++;
                                }

                                $dailyHtml[] = "<strong>{$status}</strong><br style='mso-data-placement:same-cell;'/><span class='time-cell str-format' style='white-space:nowrap;'>{$loginTimeStr}<br style='mso-data-placement:same-cell;'/>{$logoutTimeStr}</span>";
                                continue;
                            }

                            if (in_array($date, $holidays)) {
                                $holidaysCount++;
                                $dailyHtml[] = "<strong>HO</strong>";
                                continue;
                            }

                            $cDate = Carbon::parse($date);
                            $isWeekOff = false;
                            if ($weekOffs == 1)
                                $isWeekOff = $cDate->isSunday();
                            elseif ($weekOffs == 2)
                                $isWeekOff = $cDate->isSaturday() || $cDate->isSunday();

                            if ($isWeekOff) {
                                $weekOffsCount++;
                                $dailyHtml[] = "<strong>W</strong>";
                            } else {
                                $absent++;
                                $dailyHtml[] = "<strong style='color:#C62828;'>A</strong>";
                            }
                        }

                        echo '<tr>';
                        echo '<td>' . $sno++ . '</td>';
                        echo '<td class="text-start"><strong>' . htmlspecialchars($employee->employee_name) . '</strong><br style="mso-data-placement:same-cell;"/><span style="font-size:9px;color:#666;" class="str-format">' . htmlspecialchars($employee->employee_id) . '</span></td>';
                        echo '<td>' . $present . '</td>';
                        echo '<td>' . $absent . '</td>';
                        echo '<td>' . $half . '</td>';
                        echo '<td>' . $late . '</td>';
                        echo '<td>' . $paidLeaves . '</td>';
                        echo '<td>' . $unpaidLeaves . '</td>';
                        echo '<td>' . $holidaysCount . '</td>';
                        echo '<td>' . $weekOffsCount . '</td>';

                        foreach ($dailyHtml as $html) {
                            echo '<td>' . $html . '</td>';
                        }
                        echo '</tr>';
                    }
                    echo '<tr><td colspan="' . $totalCols . '" style="border:none; height: 10px;"></td></tr>';
                }

                echo '</table></body></html>';
            }, $fileName, [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ]);

        } catch (\Exception $e) {
            Log::error('Monthly Attendance Export Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return redirect()->back()->with('error', 'Unable to export monthly attendance.');
        }
    }

    public function attendanceLogs($attendanceId)
    {
        try {
            $this->authorizeAccess();

            $attendance = Attendance::findOrFail($attendanceId);

            $officeTiming = OfficeTiming::where(
                'client_id',
                $attendance->client_id
            )->first();

            $allowedBreak = $officeTiming->break_time ?? 30;

            $logs = AttendanceLog::where(
                'client_id',
                $attendance->client_id
            )
                ->where(
                    'emp_id',
                    $attendance->emp_id
                )
                ->whereDate(
                    'punch_time',
                    Carbon::parse($attendance->punch_in_time)->toDateString()
                )
                ->groupBy('punch_time')
                ->orderBy('punch_time')
                ->get(['punch_time']);

            $timeline = [];
            $totalBreak = 0;
            $totalWorking = 0;

            if ($logs->count() >= 2) {
                $firstPunch = Carbon::parse($logs->first()->punch_time);
                $lastPunch = Carbon::parse($logs->last()->punch_time);
                $totalWorking = $firstPunch->diffInMinutes($lastPunch);
            }

            for ($i = 0; $i < $logs->count(); $i++) {
                $current = Carbon::parse($logs[$i]->punch_time);

                $timeline[] = [
                    'type' => ($i % 2 == 0) ? 'In' : 'Out',
                    'time' => $current->format('h:i A')
                ];

                if (isset($logs[$i + 1])) {
                    $next = Carbon::parse($logs[$i + 1]->punch_time);
                    $minutes = $current->diffInMinutes($next);

                    if ($i % 2 == 0) {
                        $timeline[] = [
                            'type' => 'Working',
                            'duration' => floor($minutes / 60) . 'h ' . ($minutes % 60) . 'm'
                        ];
                    } else {
                        $timeline[] = [
                            'type' => 'Break',
                            'duration' => floor($minutes / 60) . 'h ' . ($minutes % 60) . 'm'
                        ];
                        $totalBreak += $minutes;
                    }
                }
            }

            if ($totalBreak > $allowedBreak) {
                $totalWorking -= ($totalBreak - $allowedBreak);
            }

            $oddLog = ($logs->count() % 2 != 0);

            return response()->json([
                'status' => true,
                'odd_log' => $oddLog,
                'total_punches' => $logs->count(),
                'working_time' => floor($totalWorking / 60) . 'h ' . ($totalWorking % 60) . 'm',
                'break_time' => floor($totalBreak / 60) . 'h ' . ($totalBreak % 60) . 'm',
                'timeline' => $timeline
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function manageEmployeeOT($employeeId, $month, $year)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $employee = Employee::where('client_id', $user->id)
                ->where('status', 1)
                ->where('trash', 0)
                ->findOrFail($employeeId);

            $employeeOT = EmployeeOT::with('dates')
                ->where('client_id', $user->id)
                ->where('employee_id', $employeeId)
                ->where('ot_month', $month)
                ->where('ot_year', $year)
                ->where('trash', 0)
                ->first();

            $ot = EmployeeOT::where('client_id', $user->id)
                ->where('employee_id', $employeeId)
                ->where('ot_month', $month)
                ->where('ot_year', $year)
                ->first();

            return view('client.manageEmployeeOT', compact('employeeOT', 'employee', 'ot', 'month', 'year'));

        } catch (\Exception $e) {
            Log::error('Manage Employee OT', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->with('error', 'Unable to load OT details.');
        }
    }

    public function storeEmployeeOT(Request $request)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $request->validate([
                'employee_id' => 'required|exists:rl_employees_tbl,id',
                'ot_month' => 'required|digits_between:1,2',
                'ot_year' => 'required|digits:4',
                'ot_days' => 'required|numeric|min:0',
                'ot_hours' => 'required|numeric|min:0',
                'ot_rate' => 'required|numeric|min:0',
                'ot_amount' => 'required|numeric|min:0',
                'remarks' => 'nullable|max:1000',
            ]);

            $employee = Employee::where('client_id', $user->id)
                ->where('id', $request->employee_id)
                ->firstOrFail();

            $existing = EmployeeOT::where('client_id', $user->id)
                ->where('employee_id', $request->employee_id)
                ->where('ot_month', $request->ot_month)
                ->where('ot_year', $request->ot_year)
                ->where('trash', 0)
                ->first();

            if ($existing) {
                if (($existing->ot_source ?? 'manual') === 'dates') {
                    return redirect()->back()->withInput()->with(
                        'error',
                        'This month already has date-wise OT for this employee. Please manage the dates from the Assign OT screen.'
                    );
                }

                return redirect()->back()->withInput()->with('error', 'OT already exists for this employee and month.');
            }

            EmployeeOT::create([
                'client_id' => $user->id,
                'employee_id' => $employee->id,
                'payroll_id' => null,
                'ot_month' => (int) $request->ot_month,
                'ot_year' => (int) $request->ot_year,
                'ot_days' => $request->ot_days,
                'ot_hours' => $request->ot_hours,
                'ot_rate' => $request->ot_rate,
                'ot_amount' => $request->ot_amount,
                'remarks' => $request->remarks,
                'status' => 1,
                'trash' => 0,
                'created_by' => $user->id,
            ]);

            return redirect()->route('manageEmployeeOT', [
                'employee' => $employee->id,
                'month' => $request->ot_month,
                'year' => $request->ot_year,
            ])->with('success', 'Employee OT saved successfully.');

        } catch (\Exception $e) {
            Log::error('Store Employee OT Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->withInput()->with('error', 'Unable to save OT details.');
        }
    }

    public function updateEmployeeOT(Request $request, $id)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $request->validate([
                'employee_id' => 'required|exists:rl_employees_tbl,id',
                'ot_month' => 'required|digits_between:1,2',
                'ot_year' => 'required|digits:4',
                'ot_days' => 'required|numeric|min:0',
                'ot_hours' => 'required|numeric|min:0',
                'ot_rate' => 'required|numeric|min:0',
                'ot_amount' => 'required|numeric|min:0',
                'remarks' => 'nullable|max:1000',
            ]);

            $employee = Employee::where('client_id', $user->id)
                ->where('id', $request->employee_id)
                ->firstOrFail();

            $employeeOT = EmployeeOT::where('client_id', $user->id)->findOrFail($id);

            // Date-wise OT is owned by the Assign OT screen. Editing it here would break the
            // per-date snapshots, so it is protected instead of silently desynchronised.
            if (($employeeOT->ot_source ?? 'manual') === 'dates') {
                return redirect()->back()->withInput()->with(
                    'error',
                    'This OT was assigned date-wise. Please edit or remove the dates from the Assign OT screen.'
                );
            }

            $duplicate = EmployeeOT::where('client_id', $user->id)
                ->where('employee_id', $request->employee_id)
                ->where('ot_month', $request->ot_month)
                ->where('ot_year', $request->ot_year)
                ->where('id', '!=', $id)
                ->where('trash', 0)
                ->exists();

            if ($duplicate) {
                return redirect()->back()->withInput()->with('error', 'OT already exists for this employee and month.');
            }

            $employeeOT->update([
                'employee_id' => $employee->id,
                'ot_month' => (int) $request->ot_month,
                'ot_year' => (int) $request->ot_year,
                'ot_days' => $request->ot_days,
                'ot_hours' => $request->ot_hours,
                'ot_rate' => $request->ot_rate,
                'ot_amount' => $request->ot_amount,
                'remarks' => $request->remarks,
                'updated_by' => $user->id,
            ]);

            return redirect()->route('manageEmployeeOT', [
                'employee' => $employee->id,
                'month' => $request->ot_month,
                'year' => $request->ot_year,
            ])->with('success', 'Employee OT updated successfully.');

        } catch (\Exception $e) {
            Log::error('Update Employee OT Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->withInput()->with('error', 'Unable to update OT details.');
        }
    }

    public function otEmployeesList(Request $request)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $month = $request->month ?? date('m');
            $year = $request->year ?? date('Y');

            $otRecords = EmployeeOT::with(['employee.department', 'employee.designationInfo', 'dates'])
                ->where('client_id', $user->id)
                ->where('ot_month', $month)
                ->where('ot_year', $year)
                ->where('trash', 0)
                ->whereHas('employee', function ($query) {
                    $query->where('status', 1)->where('trash', 0);
                })
                ->orderBy('id', 'DESC')
                ->get();

            return view('client.ot_employees_list', compact('otRecords', 'month', 'year'));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OT Employees List Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->with('error', 'Unable to load OT employees list.');
        }
    }

    /**
     * NEW: Date-wise OT assignment screen.
     *
     * Lets the admin pick a department, then an employee (searchable), enter the
     * OT amount for ONE day, the reason and the actual OT dates. Every selected
     * date becomes its own row in rl_employee_ot_dates_tbl, while the existing
     * rl_employee_ot_tbl record for that employee/month keeps the totals that
     * PayrollService and the payslip already read.
     */
    public function assignOT(Request $request)
    {
        $this->authorizeAccess();
        $user = $this->getLoggedInUser();

        $month = (int) ($request->month ?? date('m'));
        $year = (int) ($request->year ?? date('Y'));

        if ($month < 1 || $month > 12) {
            $month = (int) date('m');
        }
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        $departments = Department::where('client_id', $user->id)
            ->where('status', 1)
            ->where('trash', 0)
            ->orderByRaw('ISNULL(priority), priority ASC')
            ->orderBy('department_name', 'asc')
            ->get();

        $departmentId = $request->department_id;

        if ($departmentId !== null && $departmentId !== '' && !$departments->contains('id', (int) $departmentId)) {
            $departmentId = null;
        }

        $employees = collect();

        if (!empty($departmentId)) {
            $employees = Employee::where('client_id', $user->id)
                ->where('status', 1)
                ->where('trash', 0)
                ->where('department_id', (int) $departmentId)
                ->orderBy('employee_name', 'asc')
                ->get(['id', 'employee_id', 'employee_name', 'department_id']);
        }

        $otRecords = EmployeeOT::with(['employee.department', 'employee.designationInfo', 'dates'])
            ->where('client_id', $user->id)
            ->where('ot_month', $month)
            ->where('ot_year', $year)
            ->where('trash', 0)
            ->when(!empty($departmentId), function ($query) use ($departmentId) {
                $query->whereHas('employee', function ($q) use ($departmentId) {
                    $q->where('department_id', (int) $departmentId);
                });
            })
            ->orderBy('id', 'desc')
            ->get();

        $selectedEmployeeId = $request->employee_id;

        return view('client.assignEmployeeOT', compact(
            'departments',
            'employees',
            'departmentId',
            'otRecords',
            'month',
            'year',
            'selectedEmployeeId'
        ));
    }

    /**
     * NEW: Persist the date-wise OT assignment.
     *
     * All selected dates must fall inside one calendar month, because the OT
     * record itself is stored per employee per month. Re-submitting a date that
     * already has an OT entry updates that day instead of duplicating it.
     */
    public function storeAssignedOT(Request $request)
    {
        $this->authorizeAccess();
        $user = $this->getLoggedInUser();

        $request->validate(
            [
                'department_id' => 'required|exists:rl_departments_tbl,id',
                'employee_id' => 'required|exists:rl_employees_tbl,id',
                'ot_amount_per_day' => 'required|numeric|min:0.01|max:9999999.99',
                'reason' => 'required|string|max:500',
                'ot_dates' => 'required|array|min:1',
                'ot_dates.*' => 'required|date_format:Y-m-d',
            ],
            [
                'department_id.required' => 'Please select a department.',
                'employee_id.required' => 'Please select an employee.',
                'ot_amount_per_day.required' => 'Please enter the OT amount for one day.',
                'ot_amount_per_day.min' => 'The OT amount for one day must be greater than zero.',
                'reason.required' => 'Please enter the reason for this OT.',
                'ot_dates.required' => 'Please select at least one OT date.',
                'ot_dates.min' => 'Please select at least one OT date.',
                'ot_dates.*.date_format' => 'One of the selected OT dates is not a valid date.',
            ]
        );

        try {
            $department = Department::where('client_id', $user->id)
                ->where('id', $request->department_id)
                ->first();

            if (!$department) {
                return back()->withInput()->with('error', 'The selected department was not found.');
            }

            $employee = Employee::where('client_id', $user->id)
                ->where('status', 1)
                ->where('trash', 0)
                ->where('department_id', $department->id)
                ->where('id', $request->employee_id)
                ->first();

            if (!$employee) {
                return back()->withInput()->with(
                    'error',
                    'The selected employee does not belong to the selected department.'
                );
            }

            $perDayAmount = round((float) $request->ot_amount_per_day, 2);
            $reason = trim($request->reason);

            // Normalise, de-duplicate and sort the selected dates.
            $dates = collect($request->ot_dates)
                ->map(function ($value) {
                    return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
                })
                ->unique(function ($date) {
                    return $date->format('Y-m-d');
                })
                ->sortBy(function ($date) {
                    return $date->format('Y-m-d');
                })
                ->values();

            $firstDate = $dates->first();
            $singleMonth = $dates->every(function ($date) use ($firstDate) {
                return $date->month === $firstDate->month && $date->year === $firstDate->year;
            });

            if (!$singleMonth) {
                return back()->withInput()->with(
                    'error',
                    'All OT dates must belong to the same month. Please assign one month at a time.'
                );
            }

            $otMonth = (int) $firstDate->month;
            $otYear = (int) $firstDate->year;

            $existingHeader = EmployeeOT::where('client_id', $user->id)
                ->where('employee_id', $employee->id)
                ->where('ot_month', $otMonth)
                ->where('ot_year', $otYear)
                ->first();

            if ($existingHeader && ($existingHeader->ot_source ?? 'manual') !== 'dates') {
                return back()->withInput()->with(
                    'error',
                    'A manual (day/hour based) OT entry already exists for ' . $employee->employee_name
                    . ' in ' . $firstDate->format('F Y') . '. Please remove it from Manage OT before assigning date-wise OT.'
                );
            }

            $created = 0;
            $updated = 0;
            $headerId = null;

            DB::transaction(function () use ($user, $employee, $dates, $perDayAmount, $reason, $otMonth, $otYear, &$created, &$updated, &$headerId) {
                $header = EmployeeOT::where('client_id', $user->id)
                    ->where('employee_id', $employee->id)
                    ->where('ot_month', $otMonth)
                    ->where('ot_year', $otYear)
                    ->lockForUpdate()
                    ->first();

                if (!$header) {
                    $header = EmployeeOT::create([
                        'client_id' => $user->id,
                        'employee_id' => $employee->id,
                        'payroll_id' => null,
                        'ot_month' => $otMonth,
                        'ot_year' => $otYear,
                        'ot_days' => 0,
                        'ot_hours' => 0,
                        'ot_rate' => 0,
                        'ot_amount' => 0,
                        'ot_source' => 'dates',
                        'remarks' => $reason,
                        'status' => 1,
                        'trash' => 0,
                        'created_by' => $user->id,
                    ]);
                }

                foreach ($dates as $date) {
                    $dateKey = $date->format('Y-m-d');

                    $row = EmployeeOTDate::where('client_id', $user->id)
                        ->where('employee_id', $employee->id)
                        ->where('ot_date', $dateKey)
                        ->lockForUpdate()
                        ->first();

                    if ($row) {
                        $row->update([
                            'ot_id' => $header->id,
                            'ot_amount' => $perDayAmount,
                            'remarks' => $reason,
                            'status' => 1,
                            'trash' => 0,
                            'updated_by' => $user->id,
                        ]);
                        $updated++;
                    } else {
                        EmployeeOTDate::create([
                            'client_id' => $user->id,
                            'employee_id' => $employee->id,
                            'ot_id' => $header->id,
                            'ot_date' => $dateKey,
                            'ot_amount' => $perDayAmount,
                            'remarks' => $reason,
                            'status' => 1,
                            'trash' => 0,
                            'created_by' => $user->id,
                        ]);
                        $created++;
                    }
                }

                $this->syncDateWiseOTHeader($header, $user->id);

                $headerId = $header->id;
            });

            $header = $headerId ? EmployeeOT::find($headerId) : null;
            $totalDays = $header ? (int) $header->ot_days : 0;
            $totalAmount = $header ? (float) $header->ot_amount : 0;

            return redirect()->route('assignOT', [
                'month' => $otMonth,
                'year' => $otYear,
                'department_id' => $department->id,
                'employee_id' => $employee->id,
            ])->with(
                'success',
                'OT saved for ' . $employee->employee_name . ': ' . $created . ' date(s) added, '
                . $updated . ' date(s) updated. Total ' . $totalDays . ' day(s) = ₹'
                . number_format($totalAmount, 2) . '.'
            );

        } catch (\Throwable $e) {
            Log::error('Assign Employee OT Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'client_id' => $user->id ?? null,
                'employee_id' => $request->employee_id,
            ]);

            return back()->withInput()->with(
                'error',
                'Unable to save the OT assignment. Please check the selected dates and try again.'
            );
        }
    }

    /**
     * NEW: Remove a single assigned OT date and re-sync the monthly OT header.
     * If no dates remain, the monthly OT record is removed as well.
     */
    public function destroyAssignedOTDate($id)
    {
        $this->authorizeAccess();
        $user = $this->getLoggedInUser();

        try {
            $otDate = EmployeeOTDate::where('client_id', $user->id)->findOrFail($id);
            $date = Carbon::parse($otDate->ot_date);
            $employeeId = $otDate->employee_id;

            $employee = Employee::where('client_id', $user->id)->find($employeeId);

            DB::transaction(function () use ($otDate, $user) {
                $headerId = $otDate->ot_id;
                $otDate->delete();

                $header = EmployeeOT::where('client_id', $user->id)->where('id', $headerId)->first();

                if ($header) {
                    $this->syncDateWiseOTHeader($header, $user->id);
                }
            });

            return redirect()->route('assignOT', [
                'month' => (int) $date->month,
                'year' => (int) $date->year,
                'department_id' => $employee->department_id ?? null,
                'employee_id' => $employeeId,
            ])->with('success', 'OT date ' . $date->format('d M Y') . ' removed successfully.');

        } catch (\Throwable $e) {
            Log::error('Delete Assigned OT Date Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'client_id' => $user->id ?? null,
                'ot_date_id' => $id,
            ]);

            return redirect()->back()->with('error', 'Unable to remove this OT date.');
        }
    }

    /**
     * Recalculate the monthly OT header from its date rows.
     *
     * ot_days   = number of OT dates
     * ot_amount = sum of the per-date amounts (this is exactly what payroll pays)
     * ot_rate   = average amount per day (kept in sync for the existing OT screens)
     */
    private function syncDateWiseOTHeader(EmployeeOT $header, $userId)
    {
        $rows = EmployeeOTDate::where('client_id', $header->client_id)
            ->where('employee_id', $header->employee_id)
            ->where('trash', 0)
            ->orderBy('ot_date')
            ->get();

        if ($rows->isEmpty()) {
            $header->delete();
            return;
        }

        $totalAmount = round($rows->sum(function ($row) {
            return (float) $row->ot_amount;
        }), 2);

        $days = $rows->count();

        $reasons = $rows->pluck('remarks')
            ->filter(function ($value) {
                return $value !== null && trim((string) $value) !== '';
            })
            ->unique()
            ->values();

        $header->update([
            'ot_days' => $days,
            'ot_hours' => 0,
            'ot_rate' => $days > 0 ? round($totalAmount / $days, 2) : 0,
            'ot_amount' => $totalAmount,
            'ot_source' => 'dates',
            'remarks' => $reasons->isNotEmpty() ? $reasons->implode(' | ') : $header->remarks,
            'status' => 1,
            'trash' => 0,
            'updated_by' => $userId,
        ]);
    }

    public function manageEmployeeTDS($employeeId)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $employee = Employee::where('client_id', $user->id)
                ->where('status', 1)
                ->where('trash', 0)
                ->where('id', $employeeId)
                ->firstOrFail();

            return view('client.manageEmployeeTDS', compact('employee'));

        } catch (\Exception $e) {
            return redirect()->route('managePayroll')->with('error', 'Unable to load employee TDS details.');
        }
    }

    public function updateEmployeeTDS(Request $request, $employeeId)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $request->validate([
                'tax_regime' => 'required|in:2',
                'tds_percentage' => 'required|numeric|min:0|max:100',
                'tds_amount' => 'required|numeric|min:0',
            ]);

            $employee = Employee::where('client_id', $user->id)
                ->where('id', $employeeId)
                ->firstOrFail();

            $employee->update([
                'tax_regime' => 2,
                'tds_percentage' => $request->tds_percentage,
                'tds_amount' => $request->tds_amount,
                'updated_by' => $user->id,
            ]);

            return redirect()->route('manageEmployeeTDS', $employee->id)->with('success', 'Employee TDS updated successfully.');

        } catch (\Exception $e) {
            Log::error('Update Employee TDS Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->withInput()->with('error', 'Unable to update TDS.');
        }
    }
}