<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip</title>
    @include('client.includes.header_links')

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
            gap: 10px;
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

    @include('client.includes.sidebar')

    <div id="main">
        @include('client.includes.header')

        <div class="content">
            <div class="container-fluid">

                <div class="crm-header d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Payslip</h4>

                    <a href="javascript:history.back()" class="btn btn-light border">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

                <div id="printArea">
                    <div class="psx-wrapper">
                        <div class="psx-box">

                            @php
                                $address = trim($client->address ?? '');
                                $words = preg_split('/\s+/', $address, -1, PREG_SPLIT_NO_EMPTY);
                                $splitIndex = (int) ceil(count($words) * 0.3);
                                $addressLine1 = implode(' ', array_slice($words, 0, $splitIndex));
                                $addressLine2 = implode(' ', array_slice($words, $splitIndex));
                            @endphp

                            <div style="width:100%; display:flex; align-items:flex-start;">

                                <div style="width:20%; text-align:left;">
                                    @if(!empty($client->logo))
                                        <img src="{{ asset('public/uploads/clients/' . $client->logo) }}" width="150"
                                            height="150" alt="Client Logo">
                                    @endif
                                </div>

                                <div style="width:80%; text-align:center;">
                                    <h2 style="margin:35px 0 0; font-size:22px; font-weight:700;">
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

                            <!-- <div class="psx-title">
                                Payslip for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
                            </div> -->

                            <div class="psx-title">
                                Payslip for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
                            </div>

                            @if(!empty($deductionExceptionMessage))
                                <div
                                    style="border:1px solid #90caf9; background:#e3f2fd; color:#0d47a1; padding:10px 14px; border-radius:6px; font-size:12px; margin-bottom:15px;">
                                    <strong>Note:</strong> {{ $deductionExceptionMessage }}
                                </div>
                            @endif

                            <table class="psx-table psx-main">
                                <tr>
                                    <td width="20%"><strong>Name of Employee</strong></td>
                                    <td width="2%">:</td>
                                    <td width="28%">{{ $employee->employee_name ?? '-' }}</td>

                                    <td width="18%"><strong>UAN</strong></td>
                                    <td width="2%">:</td>
                                    <td width="30%">{{ $employee->uan_number ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><strong>Employee ID</strong></td>
                                    <td>:</td>
                                    <td>{{ $employee->employee_id ?? '-' }}</td>

                                    <td><strong>PF No</strong></td>
                                    <td>:</td>
                                    <td>{{ $employee->pf_number ?? '-' }}</td>
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
                                        {{ !empty($employee->joining_date)
    ? \Carbon\Carbon::parse($employee->joining_date)->format('d.m.Y')
    : '-' }}
                                    </td>

                                    <td><strong>Bank A/C No</strong></td>
                                    <td>:</td>
                                    <td>{{ $employee->bank_account_number ?? '-' }}</td>
                                </tr>

                                <tr>
                                    <td><strong>Gross Wage</strong></td>
                                    <td>:</td>
                                    <td>₹{{ number_format($grossSalary, 2) }}</td>

                                    <td><strong>PAN</strong></td>
                                    <td>:</td>
                                    <td>{{ $employee->pan_number ?? '-' }}</td>
                                </tr>
                            </table>

                            <table class="psx-table equal-cols">
                                <tr>
                                    <td>Total Working Days</td>
                                    <td class="psx-value">{{ number_format($totalDays, 0) }}</td>

                                    <td>Present Days</td>
                                    <td class="psx-value">{{ number_format($presentDays, 2) }}</td>
                                </tr>

                                <tr>
                                    <td>Week Offs</td>
                                    <td class="psx-value">{{ number_format($weekOffDays, 2) }}</td>

                                    <td>Holidays</td>
                                    <td class="psx-value">{{ number_format($holidayDays, 2) }}</td>
                                </tr>

                                <tr>
                                    <td>Half Days</td>
                                    <td class="psx-value">{{ number_format($halfDays, 2) }}</td>

                                    <td>Approved Leave Days</td>
                                    <td class="psx-value">{{ number_format($leaveDays, 2) }}</td>
                                </tr>

                                <tr>
                                    <td>Absences Recorded</td>
                                    <td class="psx-value">{{ number_format($absentDaysRaw, 2) }}</td>

                                    <td>Covered by Week-Off Allowance</td>
                                    <td class="psx-value">{{ number_format($weekOffAbsenceOffset, 2) }}</td>
                                </tr>

                                <tr>
                                    <td>Covered by Paid Leave</td>
                                    <td class="psx-value">{{ number_format($paidLeaveDaysUsed, 2) }}</td>

                                    <td>Net Absences After Allowance</td>
                                    <td class="psx-value">{{ number_format($netAbsentAfterOffset, 2) }}</td>
                                </tr>

                                <tr style="font-weight:700;">
                                    <td>LOP Days (Deductible)</td>
                                    <td class="psx-value">{{ number_format($lopDays, 2) }}</td>

                                    <td>Paid Days</td>
                                    <td class="psx-value">{{ number_format($paidDays, 2) }}</td>
                                </tr>

                                <!-- OT: days and amount (date-wise or manual) -->
                                <tr>
                                    <td>OT Days</td>
                                    <td class="psx-value">
                                        {{ rtrim(rtrim(number_format($otDays ?? 0, 2), '0'), '.') }}
                                        @if(($otHoursFormatted ?? '-') !== '-')
                                            <span class="text-muted small">({{ $otHoursFormatted }})</span>
                                        @endif
                                    </td>

                                    <td>OT Amount</td>
                                    <td class="psx-value">₹{{ number_format($otAmount ?? 0, 2) }}</td>
                                </tr>
                            </table>

                            <p style="font-size:11px; color:#64748b; margin:4px 0 0; text-align:center;">
                                Present ({{ number_format($presentDays, 0) }}) + Week Offs
                                ({{ number_format($weekOffDays, 0) }}) +
                                Holidays ({{ number_format($holidayDays, 0) }}) + Half Days
                                ({{ number_format($halfDays, 0) }}) +
                                Approved Leave ({{ number_format($leaveDays, 0) }}) + Absences
                                ({{ number_format($absentDaysRaw, 0) }})
                                = Total Working Days ({{ number_format($totalDays, 0) }})
                            </p>

                            <div class="psx-salary-box">

                                <div class="psx-header">
                                    <div>Earnings</div>
                                    <div>Deductions</div>
                                </div>

                                <div class="psx-body">

                                    <div class="psx-col">
                                        @foreach($earningRows as $earning)
                                            <div class="psx-item">
                                                <span>{{ $earning->title }}</span>
                                                <span>₹{{ number_format($earning->calculated_amount, 2) }}</span>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="psx-col">
                                        @foreach($deductionRows as $deduction)
                                            <div class="psx-item">
                                                <span>{{ $deduction->title }}</span>
                                                <span>₹{{ number_format($deduction->calculated_amount, 2) }}</span>
                                            </div>
                                        @endforeach

                                        @if($lopAmount > 0)
                                            <div class="psx-item">
                                                <span>LOP Amount</span>
                                                <span>₹{{ number_format($lopAmount, 2) }}</span>
                                            </div>
                                        @endif
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
                                                ₹{{ number_format($totalDeductions + $lopAmount, 2) }}
                                            </span>
                                        </div>
                                    </div>

                                </div>

                                <div class="psx-net">
                                    <div>Net Pay</div>
                                    <div>₹{{ number_format($netPay, 2) }}</div>
                                </div>

                            </div>

                            @php
                                use App\Helpers\NumberHelper;
                            @endphp

                            <div class="mt-3 text-center">
                                <strong>Amount Payable:</strong>
                                Rupees {{ NumberHelper::amountInWords($netPay) }}
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

    @include('client.includes.footer_links')

</body>

</html>