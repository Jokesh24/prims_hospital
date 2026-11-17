<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Payslip</title>

    @include('employee.includes.header_links')

    <style>
        .psx-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .psx-box {
            width: 850px;
            border: 1px solid #ccc;
            background: #fff;
            padding: 15px;
            font-family: Arial, sans-serif;
        }

        .psx-title {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            margin: 15px 0;
        }

        .psx-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .psx-table td,
        .psx-table th {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 13px;
        }

        .psx-main td {
            border: none;
            padding: 5px;
        }

        .psx-colon {
            width: 10px;
            text-align: center;
        }

        .psx-value {
            text-align: right;
        }

        .psx-salary-box {
            border: 1px solid #ddd;
            margin-top: 15px;
        }

        .psx-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            font-weight: 700;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .psx-header div {
            padding: 8px;
        }

        .psx-header div:first-child {
            border-right: 1px solid #ddd;
        }

        .psx-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .psx-col {
            padding: 10px;
        }

        .psx-col:first-child {
            border-right: 1px solid #ddd;
        }

        .psx-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .psx-footer {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-top: 1px solid #ddd;
        }

        .psx-footer .psx-col:first-child {
            border-right: 1px solid #ddd;
        }

        .psx-bold {
            font-weight: bold;
        }

        .psx-net {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-top: 1px solid #ddd;
            font-weight: bold;
        }

        .psx-net div {
            padding: 10px;
            text-align: center;
        }

        .psx-net div:first-child {
            border-right: 1px solid #ddd;
        }

        .psx-note {
            margin-top: 15px;
            text-align: center;
            font-size: 12px;
        }

        .equal-cols {
            table-layout: fixed;
        }

        .equal-cols td {
            width: 25%;
        }

        @media print {

            .btn,
            .sidebar,
            .header,
            .navbar,
            .crm-header,
            .footer {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .psx-box {
                width: 100%;
                border: 1px solid #ccc;
            }
        }
    </style>
    ```

</head>

<body>

    ```
    @include('employee.includes.sidebar')

    <div id="main">

        @include('employee.includes.header')

        <div class="content">

            <div class="container-fluid">

                <div class="crm-header d-flex justify-content-between align-items-center mb-4">

                    <h4 class="fw-bold mb-0">
                        Payslip
                    </h4>

                    <a href="javascript:history.back()" class="btn btn-light border">

                        <i class="bi bi-arrow-left"></i>
                        Back

                    </a>

                </div>

                <div class="psx-wrapper">

                    <div class="psx-box">

                        <div style="display:flex;justify-content:center;">

                            <div style="display:flex;align-items:center;gap:15px;">

                                @if(!empty($client->company_logo))

                                    <img src="{{ asset($client->company_logo) }}" width="70">

                                @endif

                                <div>

                                    <strong style="font-size:20px;">

                                        {{ $client->company_name ?? '' }}

                                    </strong>

                                    <br>

                                    <span style="font-size:12px;">

                                        {{ $client->address ?? '' }}

                                    </span>

                                </div>

                            </div>

                        </div>

                        <div class="psx-title">

                            Payslip for
                            {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}

                        </div>

                        <table class="psx-table psx-main">

                            <tr>

                                <td>Employee ID</td>
                                <td class="psx-colon">:</td>
                                <td>{{ $employee->employee_id ?? '-' }}</td>

                                <td>Designation</td>
                                <td class="psx-colon">:</td>
                                <td>{{ $employee->designation ?? '-' }}</td>

                            </tr>

                            <tr>

                                <td>Employee Name</td>
                                <td class="psx-colon">:</td>
                                <td>{{ $employee->employee_name }}</td>

                                <td>Date Of Joining</td>
                                <td class="psx-colon">:</td>
                                <td>

                                    {{ !empty($employee->joining_date)
    ? \Carbon\Carbon::parse($employee->joining_date)->format('d-m-Y')
    : '-' }}

                                </td>

                            </tr>

                        </table>

                        <table class="psx-table equal-cols">

                            <tr>

                                <td>Total Working Days</td>
                                <td class="psx-value">
                                    {{ $totalDays }}
                                </td>

                                <td>Absent Days</td>
                                <td class="psx-value">
                                    {{ $absent }}
                                </td>

                            </tr>

                            <tr>

                                <td>Paid Leaves</td>
                                <td class="psx-value">
                                    {{ $paidLeaves }}
                                </td>

                                <td>LOP Days</td>
                                <td class="psx-value">
                                    {{ $lopDays }}
                                </td>

                            </tr>

                        </table>
                        {{-- SALARY --}}
                        <div class="psx-salary-box">

                            <div class="psx-header">

                                <div>Earnings</div>

                                <div>Deductions</div>

                            </div>

                            <div class="psx-body">

                                {{-- Earnings --}}
                                <div class="psx-col">

                                    @foreach($earningRows as $earning)

                                        <div class="psx-item">

                                            <span>

                                                {{ $earning->title }}


                                            </span>

                                            <span>

                                                ₹{{ number_format($earning->calculated_amount, 2) }}

                                            </span>

                                        </div>

                                    @endforeach

                                </div>

                                {{-- Deductions --}}
                                <div class="psx-col">

                                    @foreach($deductionRows as $deduction)

                                        <div class="psx-item">

                                            <span>

                                                {{ $deduction->title }}


                                            </span>

                                            <span>

                                                ₹{{ number_format($deduction->calculated_amount, 2) }}

                                            </span>

                                        </div>

                                    @endforeach
                                    <div class="psx-item">

                                        <span>

                                            LOP Amount

                                        </span>

                                        <span>

                                            ₹{{ number_format($lopAmount, 2) }}

                                        </span>

                                    </div>

                                </div>

                            </div>

                            <div class="psx-footer">

                                <div class="psx-col">

                                    <div class="psx-item">

                                        <span>

                                            Gross Salary

                                        </span>

                                        <span>

                                            ₹{{ number_format($grossSalary, 2) }}

                                        </span>

                                    </div>





                                </div>

                                <div class="psx-col">



                                    <div class="psx-item psx-bold">

                                        <span>

                                            Total Deductions

                                        </span>

                                        <span>

                                            ₹{{ number_format($totalDeductions, 2) }}

                                        </span>

                                    </div>

                                </div>

                            </div>

                            <div class="psx-net">

                                <div>

                                    Net Pay

                                </div>

                                <div>

                                    @php
                                        $totalDeductionAmount = $totalDeductions + $lopAmount;
                                        $netPay = $grossSalary - $totalDeductionAmount;
                                    @endphp

                                    <div>
                                        ₹{{ number_format($netPay, 2) }}
                                    </div>

                                </div>


                            </div>

                        </div>

                        <!-- <div class="mt-3 text-center">

                            <strong>

                                Amount Payable :

                            </strong>

                            ₹{{ number_format($netPay, 2) }}

                        </div> -->

                        @php
                            use App\Helpers\NumberHelper;
                        @endphp

                        <div class="mt-3 text-center">

                            <strong>Amount Payable :</strong>

                            Rupees {{ NumberHelper::amountInWords($netPay) }}

                            <br>

                        </div>

                        <div class="psx-note">

                            Note: This is a computer generated payslip and does not require a signature.

                        </div>

                        <div class="text-center mt-4">

                            <button onclick="window.print()" class="btn btn-primary">

                                Print Payslip

                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    @include('employee.includes.footer_links')


</body>

</html>