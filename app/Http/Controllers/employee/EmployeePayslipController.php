<?php

namespace App\Http\Controllers\employee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class EmployeePayslipController extends Controller
{
    // Display the list of actual generated payslips
    public function index(Request $request)
    {
        $employee = Auth::guard('employee')->user();

        // Fetch real payroll records instead of a hardcoded loop
        $payrolls = Payroll::where('employee_id', $employee->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12);

        return view('employee.payslip_list', compact('payrolls', 'employee'));
    }

    // View specific payslip with exact client-side math parity
    public function view($month, $year)
    {
        try {
            $employee = Auth::guard('employee')->user();

            $payroll = Payroll::where('employee_id', $employee->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if (!$payroll) {
                return redirect()->back()->with('error', 'Payslip has not been generated for the selected month.');
            }

            $leaves = $payroll->absent_days;

            $earningRows = PayrollItem::where('payroll_id', $payroll->id)
                ->where('type', 'earning')
                ->orderBy('id')
                ->get();

            $deductionRows = PayrollItem::where('payroll_id', $payroll->id)
                ->where('type', 'deduction')
                ->orderBy('id')
                ->get();

            $client = Client::where('id', $employee->client_id)->first();

            return view('employee.payslips.payslip', [
                'employee' => $employee,
                'client' => $client,
                'month' => $month,
                'year' => $year,
                'grossSalary' => $payroll->gross_salary,
                'lopAmount' => $payroll->lop_amount,
                'salaryAfterLop' => $payroll->salary_after_lop,
                'totalDays' => $payroll->working_days,
                'absent' => $payroll->absent_days,
                'paidLeaves' => $payroll->paid_leave_days,
                'leaves' => $leaves,
                'lopDays' => $payroll->lop_days,
                'earningRows' => $earningRows,
                'deductionRows' => $deductionRows,
                'totalEarnings' => $payroll->total_earnings,
                'totalDeductions' => $payroll->total_deductions,
                'net_pay' => $payroll->net_salary,
            ]);

        } catch (\Exception $e) {
            Log::error('Employee Payslip Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while loading your payslip.');
        }
    }
}