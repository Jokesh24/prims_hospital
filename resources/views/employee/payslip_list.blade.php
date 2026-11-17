<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Salary Slips | {{ env('APP_NAME') }}</title>
    @include('employee.includes.header_links')
</head>

<body>

    @include('employee.includes.sidebar')

    <div id="main">

        @include('employee.includes.header')

        <div class="content">
            <div class="container-fluid py-2">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="fw-bold m-0">My Salary Slips</h4>
                    </div>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show floating-alert d-flex align-items-center"
                        role="alert" style="position: fixed; top: 24px; right: 24px; z-index: 9999; min-width: 320px;
                                   background-color: #fef2f2; 
                                   border: 1px solid #fecaca; 
                                   border-left: 5px solid #dc2626; 
                                   box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1); 
                                   color: #7f1d1d; font-weight: 500; border-radius: 10px; padding: 16px 20px;">
                        <i class="bi bi-exclamation-triangle-fill"
                            style="color: #dc2626; font-size: 1.25rem; margin-right: 12px;"></i>
                        <div style="flex-grow: 1;">{{ session('error') }}</div>
                        <button type="button" class="btn-close position-static p-0 ms-3" data-bs-dismiss="alert"
                            aria-label="Close" style="font-size: 0.8rem; opacity: 0.7;"></button>
                    </div>
                @endif

                <div class="dashboard-card bg-white rounded-3 shadow-sm p-4">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-muted text-uppercase small fw-bold">Month / Year</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Working Days</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Gross Salary</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Deductions (Inc. LOP)</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Net Salary</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payrolls as $payroll)
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark">
                                            {{ date('F', mktime(0, 0, 0, $payroll->month, 1)) }} {{ $payroll->year }}
                                            <br>
                                            <span class="badge-soft mt-1 d-inline-block"
                                                style="background:#ecfdf5; color:#10b981; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px;">Generated</span>
                                        </td>
                                        <td>
                                            {{ $payroll->working_days }} Days<br>
                                            <small class="text-muted">P: {{ $payroll->present_days }} | A:
                                                {{ $payroll->absent_days }}</small>
                                        </td>
                                        <td><i
                                                class="bi bi-currency-rupee"></i>{{ number_format($payroll->gross_salary, 2) }}
                                        </td>
                                        <td>
                                            <span class="text-danger fw-bold">
                                                <i
                                                    class="bi bi-currency-rupee"></i>{{ number_format($payroll->lop_amount + $payroll->total_deductions, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-success fw-bold">
                                                <i
                                                    class="bi bi-currency-rupee"></i>{{ number_format($payroll->net_salary, 2) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('employee.payslip.view', [$payroll->month, $payroll->year]) }}"
                                                class="btn btn-primary btn-sm px-3">
                                                <i class="bi bi-file-earmark-text me-1"></i> View Payslip
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                                            No payroll records have been generated for you yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        {{ $payrolls->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>
        </div>

        @include('employee.includes.footer_links')

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let alerts = document.querySelectorAll('.floating-alert');
                if (alerts.length > 0) {
                    setTimeout(function () {
                        alerts.forEach(function (alertNode) {
                            if (typeof bootstrap !== 'undefined') {
                                let bsAlert = new bootstrap.Alert(alertNode);
                                bsAlert.close();
                            } else {
                                alertNode.style.display = 'none';
                            }
                        });
                    }, 5000);
                }
            });
        </script>
    </div>
</body>

</html>