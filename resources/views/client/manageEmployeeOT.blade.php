<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>

        Employee OT

    </title>

    @include('client.includes.header_links')

</head>

<body>

    <div id="overlay" onclick="closeMobile()">
    </div>

    @include('client.includes.sidebar')

    <div id="main">

        @include('client.includes.header')

        <div class="content">

            <div class="container py-2">

                @if(session('success'))

                    <div class="alert alert-success alert-dismissible fade show">

                        {{ session('success') }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert">
                        </button>

                    </div>

                @endif

                @if(session('error'))

                    <div class="alert alert-danger alert-dismissible fade show">

                        {{ session('error') }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert">
                        </button>

                    </div>

                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h4 class="fw-bold mb-1">

                            Employee OT

                        </h4>

                        <small class="text-muted">

                            {{ date('F', mktime(0, 0, 0, $month, 1)) }}

                            {{ $year }}

                        </small>

                    </div>

                    <a href="{{ route('managePayroll', ['month' => $month, 'year' => $year]) }}"
                        class="btn btn-light border">

                        <i class="bi bi-arrow-left"></i>

                        Back

                    </a>

                </div>

                <div class="row mb-4">

                    <div class="col-md-3">

                        <div class="dashboard-card">

                            <small>

                                Employee ID

                            </small>

                            <h6 class="mt-2">

                                {{ $employee->employee_id }}

                            </h6>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="dashboard-card">

                            <small>

                                Employee Name

                            </small>

                            <h6 class="mt-2">

                                {{ $employee->employee_name }}

                            </h6>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="dashboard-card">

                            <small>

                                Department

                            </small>

                            <h6 class="mt-2">

                                {{ $employee->department->department_name ?? '-' }}

                            </h6>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="dashboard-card">

                            <small>

                                Gross Salary

                            </small>

                            <h6 class="mt-2">

                                <i class="bi bi-currency-rupee"></i> {{ number_format($employee->gross_salary, 2) }}

                            </h6>

                        </div>

                    </div>

                </div>

                @if($employeeOT && ($employeeOT->ot_source ?? 'manual') === 'dates')

                    {{-- Date-wise OT is owned by the Assign OT screen. --}}
                    <div class="dashboard-card mb-3">

                        <div class="alert alert-info mb-3">
                            <i class="bi bi-calendar2-check-fill me-1"></i>
                            This month's OT was assigned <strong>date-wise</strong>. It is paid in full and is
                            added to payroll, net salary and the payslip. Edit or remove the dates on the
                            Assign OT screen.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-3">
                                <thead class="table-light">
                                    <tr>
                                        <th width="6%">#</th>
                                        <th width="30%">OT Date</th>
                                        <th width="20%" class="text-end">Amount (Per Day)</th>
                                        <th>Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employeeOT->dates as $index => $otDate)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $otDate->ot_date->format('d M Y (D)') }}</td>
                                            <td class="text-end">
                                                ₹{{ number_format((float) $otDate->ot_amount, 2) }}
                                            </td>
                                            <td>{{ $otDate->remarks ?: '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">No dates found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="table-light fw-bold">
                                        <td colspan="2" class="text-end">Total</td>
                                        <td class="text-end">
                                            ₹{{ number_format((float) $employeeOT->ot_amount, 2) }}
                                            <div class="small text-muted fw-normal">
                                                {{ (int) $employeeOT->ot_days }} Day(s)
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <a href="{{ route('assignOT', ['month' => $month, 'year' => $year, 'department_id' => $employee->department_id, 'employee_id' => $employee->id]) }}"
                            class="btn btn-primary">
                            <i class="bi bi-calendar2-plus me-1"></i> Manage OT Dates
                        </a>

                    </div>

                @else

                <div class="dashboard-card">

                    <form method="POST"
                        action="{{ $employeeOT ? route('updateEmployeeOT', $employeeOT->id) : route('storeEmployeeOT') }}">

                        @csrf

                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">

                        <input type="hidden" name="ot_month" value="{{ $month }}">

                        <input type="hidden" name="ot_year" value="{{ $year }}">

                        <div class="row g-3">

                            <div class="col-md-3">
                                <label class="form-label">
                                    OT Days
                                </label>

                                <input type="number" min="0" name="ot_days" class="form-control"
                                    value="{{ old('ot_days', $employeeOT->ot_days ?? 0) }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    OT Hours
                                </label>

                                <input type="number" step="0.50" min="0" name="ot_hours" class="form-control"
                                    value="{{ old('ot_hours', $employeeOT->ot_hours ?? 0) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    OT Rate / Hour
                                </label>

                                <input type="number" step="0.01" min="0" name="ot_rate" class="form-control"
                                    value="{{ old('ot_rate', $employeeOT->ot_rate ?? 0) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    OT Amount
                                </label>

                                <input type="number" step="0.01" min="0" name="ot_amount" class="form-control"
                                    value="{{ old('ot_amount', $employeeOT->ot_amount ?? 0) }}" required>
                            </div>
                            <div class="col-md-12">

                                <label class="form-label">

                                    Remarks

                                </label>

                                <textarea name="remarks" rows="3"
                                    class="form-control">{{ old('remarks', $employeeOT->remarks ?? '') }}</textarea>

                            </div>

                            <div class="col-md-12 text-end">

                                <button class="btn btn-primary px-4">

                                    <i class="bi bi-check-circle me-2"></i>

                                    {{ $employeeOT ? 'Update OT' : 'Save OT' }}

                                </button>

                            </div>

                        </div>

                    </form>

                </div>

                @endif
            </div>

        </div>

    </div>

    </div>

    </div>

    @include('client.includes.footer_links')

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('input[type="number"]').forEach(function (input) {

                input.addEventListener('keypress', function (e) {
                    if (e.key === '-') {
                        e.preventDefault();
                    }
                });

                input.addEventListener('input', function () {
                    if (parseFloat(this.value) < 0) {
                        this.value = 0;
                    }
                });

            });

        });

    </script>

</body>

</html>