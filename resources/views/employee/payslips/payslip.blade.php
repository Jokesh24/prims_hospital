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
            padding: 50px;
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
            body * {
                visibility: hidden;
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .btn {
                display: none !important;
            }

            @page {
                margin: 10mm;
            }
        }
    </style>

</head>

<body>

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
                <div id="printArea">
                    <div class="psx-wrapper">

                        <div class="psx-box">

                            @php
                                $address = trim($client->address ?? '');
                                $words = preg_split('/\s+/', $address);
                                $splitIndex = ceil(count($words) * 0.3);
                                $addressLine1 = implode(' ', array_slice($words, 0, $splitIndex));
                                $addressLine2 = implode(' ', array_slice($words, $splitIndex));
                            @endphp

                            <div style="width:100%; display:flex; align-items:flex-start;">

                                <!-- Logo -->
                                <div style="width:20%; text-align:left;">
                                    @if(!empty($client->logo))
                                        <img src="{{ asset('public/uploads/client_logos/' . $client->logo) }}" width="150" height="150"
                                            alt="Client Logo">
                                    @endif
                                </div>

                                <!-- Company Details -->
                                <div style="width:80%; text-align:center;">

                                    <h2 style="margin:0; font-size:22px; font-weight:700;margin-top: 35px;">
                                        {{ $client->client_name }}
                                    </h2>

                                    <p style="margin:6px 0 0; font-size:14px;">
                                        {{ $addressLine1 }}
                                    </p>

                                    @if($addressLine2)
                                        <p style="margin:2px 0 0; font-size:14px;">
                                            {{ $addressLine2 }}
                                        </p>
                                    @endif

                                </div>

                            </div>
                            <hr>

                            <div class="psx-title">
                                Payslip for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
                            </div>

                            <table class="psx-table psx-main">

                                <tr>
                                    <!-- Left Side -->
                                    <td width="20%"><strong>Name of Employee</strong></td>
                                    <td width="2%">:</td>
                                    <td width="28%">{{ $employee->employee_name ?? '-' }}</td>

                                    <!-- Right Side -->
                                    <td width="18%"><strong>UAN</strong></td>
                                    <td width="2%">:</td>
                                    <td width="30%">{{ $employee->uan_number ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><strong>Employee ID</strong></td>
                                    <td>:</td>
                                    <td>RL000{{ $employee->employee_id ?? '-' }}</td>

                                    <td><strong>PF No</strong></td>
                                    <td>:</td>
                                    <td>{{ $employee->uan_number ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><strong>Designation</strong></td>
                                    <td>:</td>
                                    <td>{{ $employee->designation ?? '-' }}</td>

                                    <td><strong>ESI No</strong></td>
                                    <td>:</td>
                                    <td>{{ $employee->esi_number ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><strong>Department</strong></td>
                                    <td>:</td>
                                    <td>{{ $employee->department->department_name ?? '-' }}</td>

                                    <td><strong>Bank Name</strong></td>
                                    <td>:</td>
                                    <td>{{ $employee->bank_name ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><strong>DOJ</strong></td>
                                    <td>:</td>
                                    <td>
                                        {{ !empty($employee->joining_date) ? \Carbon\Carbon::parse($employee->joining_date)->format('d.m.Y') : '-' }}
                                    </td>

                                    <td><strong>Bank A/C No</strong></td>
                                    <td>:</td>
                                    <td>{{ $employee->bank_account_number ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><strong>Gross Wage</strong></td>
                                    <td>:</td>
                                    <td>₹{{ number_format($employee->gross_salary ?? 0, 2) }}</td>

                                    <td><strong>PAN</strong></td>
                                    <td>:</td>
                                    <td>{{ $employee->pan_number ?? '-' }}</td>
                                </tr>
                            </table>

                            <table class="psx-table equal-cols">

                                <tr>
                                    <td>Total Working Days</td>
                                    <td class="psx-value">
                                        {{ $totalDays }}
                                    </td>

                                    <td>Paid Days</td>
                                    <td class="psx-value">
                                        {{ $totalDays }}
                                    </td>
                                </tr>

                                <tr>
                                    <td>LOP Days</td>
                                    <td class="psx-value">
                                        {{ (int) $lopDays }}
                                    </td>

                                    <td>Leaves Taken</td>
                                    <td class="psx-value">
                                        {{ (int) $leaves }}
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
                                                <span>{{ $earning->title }}</span>
                                                <span>₹{{ number_format($earning->calculated_amount, 2) }}</span>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Deductions --}}
                                    <div class="psx-col">
                                        @foreach($deductionRows as $deduction)
                                            <div class="psx-item">
                                                <span>{{ $deduction->title }}</span>
                                                <span>₹{{ number_format($deduction->calculated_amount, 2) }}</span>
                                            </div>
                                        @endforeach
                                        <div class="psx-item">
                                            <span>LOP Amount</span>
                                            <span>₹{{ number_format($lopAmount, 2) }}</span>
                                        </div>
                                    </div>

                                </div>

                                <div class="psx-footer">

                                    <div class="psx-col">
                                        <div class="psx-item psx-bold">
                                            <span>Gross Salary</span>
                                            <span>₹{{ number_format($grossSalary, 2) }}</span>
                                        </div>
                                    </div>

                                    <div class="psx-col">
                                        <div class="psx-item psx-bold">
                                            <span>Total Deductions</span>
                                            <span>
                                                @php
                                                    $totalDeductionAmount = $totalDeductions + $lopAmount;
                                                @endphp
                                                ₹{{ number_format($totalDeductionAmount, 2) }}
                                            </span>
                                        </div>
                                    </div>

                                </div>

                                <div class="psx-net">

                                    <div>Net Pay</div>

                                    @php
                                        $totalDeductionAmount = $totalDeductions + $lopAmount;
                                        $netPay = $grossSalary - $totalDeductionAmount;
                                    @endphp

                                    <div>
                                        ₹{{ number_format($netPay, 2) }}
                                    </div>

                                </div>

                            </div>

                            @php
                                use App\Helpers\NumberHelper;
                            @endphp

                            <div class="mt-3 text-center">
                                <strong>Amount Payable :</strong>
                                Rupees {{ NumberHelper::amountInWords($netPay) }}
                                <br>
                            </div>

                            <div class="psx-note">
                                Note: This is a computer-generated payslip; hence, it does not require a signature.
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
    </div>

    @include('employee.includes.footer_links')

</body>

</html>