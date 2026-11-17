<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Payroll Preview
    </title>

    @include('client.includes.header_links')

</head>

<body>

    <div
        id="overlay"
        onclick="closeMobile()"></div>

    @include('client.includes.sidebar')

    <div id="main">

        @include('client.includes.header')

        <div class="content">

            <div class="container-fluid py-2">

                <div
                    class="d-flex justify-content-between align-items-center mb-3">

                    <h4
                        class="fw-bold m-0">

                        Payroll Preview

                    </h4>

                    <a
                        href="{{ url()->previous() }}"
                        class="btn btn-outline-secondary">

                        <i class="bi bi-arrow-left me-1"></i>

                        Back

                    </a>

                </div>

                <div class="dashboard-card">

                    <div
                        class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                            <strong>
                                Payroll Month :
                            </strong>

                            {{ date('F', mktime(0,0,0,$month,1)) }}
                            {{ $year }}

                        </div>

                    </div>

                    <div class="table-responsive">

                        <table
                            class="table table-hover align-middle">

                            <thead
                                class="table-light">

                                <tr>

                                    <th>#</th>

                                    <th>
                                        Employee
                                    </th>

                                    <th>
                                        Working
                                    </th>

                                    <th>
                                        Present
                                    </th>

                                    <th>
                                        Absent
                                    </th>

                                    <th>
                                        Half Day
                                    </th>

                                    <th>
                                        Paid Leave
                                    </th>

                                    <th>
                                        LOP Days
                                    </th>

                                    <th>
                                        Gross Salary
                                    </th>

                                    <th>
                                        Earnings
                                    </th>

                                    <th>
                                        Deductions
                                    </th>

                                    <th>
                                        LOP Amount
                                    </th>

                                    <th>
                                        Net Salary
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @forelse(
                                    $payrollData as $key => $row
                                )

                                <tr>

                                    <td>

                                        {{ $key + 1 }}

                                    </td>

                                    <td>

                                        <div
                                            class="fw-semibold">

                                            {{ $row['employee_name'] }}

                                        </div>

                                        <small
                                            class="text-muted">

                                            ID :
                                            {{ $row['employee_id'] }}

                                        </small>

                                    </td>

                                    <td>

                                        {{ $row['working_days'] }}

                                    </td>

                                    <td>

                                        <span
                                            class="badge bg-success">

                                            {{ $row['present_days'] }}

                                        </span>

                                    </td>

                                    <td>

                                        <span
                                            class="badge bg-danger">

                                            {{ $row['absent_days'] }}

                                        </span>

                                    </td>

                                    <td>

                                        <span
                                            class="badge bg-warning text-dark">

                                            {{ $row['half_days'] }}

                                        </span>

                                    </td>

                                    <td>

                                        <span
                                            class="badge bg-info">

                                            {{ $row['paid_leave_days'] }}

                                        </span>

                                    </td>

                                    <td>

                                        <span
                                            class="badge bg-danger">

                                            {{ $row['lop_days'] }}

                                        </span>

                                    </td>

                                    <td>

                                        <i class="bi bi-currency-rupee"></i> {{ number_format(
                                            $row['gross_salary'],
                                            2
                                        ) }}

                                    </td>

                                    <td
                                        class="text-success fw-bold">

                                        <i class="bi bi-currency-rupee"></i> {{ number_format(
                                            $row['total_earnings'],
                                            2
                                        ) }}

                                    </td>

                                    <td
                                        class="text-danger fw-bold">

                                        <i class="bi bi-currency-rupee"></i> {{ number_format(
                                            $row['total_deductions'],
                                            2
                                        ) }}

                                    </td>

                                    <td
                                        class="text-danger fw-bold">

                                        <i class="bi bi-currency-rupee"></i> {{ number_format(
                                            $row['lop_amount'],
                                            2
                                        ) }}

                                    </td>

                                    <td
                                        class="fw-bold text-success">

                                        <i class="bi bi-currency-rupee"></i> {{ number_format(
                                            $row['net_salary'],
                                            2
                                        ) }}

                                    </td>

                                </tr>

                                @empty

                                <tr>

                                    <td
                                        colspan="13"
                                        class="text-center py-5">

                                        No payroll data found

                                    </td>

                                </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

    @include('client.includes.footer_links')

</body>

</html>