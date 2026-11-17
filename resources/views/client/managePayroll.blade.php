<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Manage Payroll
    </title>

    @include('client.includes.header_links')

    <!-- Premium Loader Styles -->
    <style>
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(255, 255, 255, 0.92);
            z-index: 999999;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .loader-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: slideUpFade 0.6s ease-out;
        }
        .logo-loader-container {
            position: relative;
            width: 90px;
            height: 90px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
        }
        .loader-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: #0d6efd; /* Primary Color */
            border-right-color: rgba(13, 110, 253, 0.3);
            border-bottom-color: rgba(13, 110, 253, 0.1);
            border-left-color: transparent;
            animation: spinRing 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }
        .loader-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 8px;
            animation: pulseLogo 2s ease-in-out infinite;
        }
        .processing-text {
            letter-spacing: 0.5px;
            color: #1e293b;
        }
        .dots::after {
            content: '';
            animation: ellipsis 1.5s infinite;
            display: inline-block;
            width: 20px;
            text-align: left;
        }
        
        /* Keyframe Animations */
        @keyframes spinRing {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes pulseLogo {
            0% { transform: scale(0.95); opacity: 0.85; filter: drop-shadow(0 0 0 rgba(13, 110, 253, 0)); }
            50% { transform: scale(1.05); opacity: 1; filter: drop-shadow(0 0 10px rgba(13, 110, 253, 0.4)); }
            100% { transform: scale(0.95); opacity: 0.85; filter: drop-shadow(0 0 0 rgba(13, 110, 253, 0)); }
        }
        @keyframes ellipsis {
            0% { content: ''; }
            25% { content: '.'; }
            50% { content: '..'; }
            75% { content: '...'; }
            100% { content: ''; }
        }
        @keyframes slideUpFade {
            0% { transform: translateY(20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
    </style>

</head>

<body>

    <!-- Premium Global Full Page Loader -->
<div id="globalLoader" class="loader-overlay" style="display: none;">
        <div class="loader-content">
            <div class="logo-loader-container">
                <div class="loader-ring"></div>
                @php
                    $loaderClient = \App\Models\Client::find(session('ClientUser'));
                @endphp
                @if($loaderClient && $loaderClient->logo)
                    <img src="{{ asset('public/uploads/clients/' . $loaderClient->logo) }}" alt="Company Logo" class="loader-logo">
                @else
                    <div class="loader-logo" style="background:#1e293b; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:bold; font-size:12px; border-radius:8px; width: 50px; height: 50px;">
                        HRMS
                    </div>
                @endif
            </div>
            <h5 class="fw-bold m-0 processing-text">Processing<span class="dots"></span></h5>
            <p class="text-muted small mt-2 mb-0" style="font-weight: 500;">Please wait, this might take a moment.</p>
        </div>
    </div>

    <div id="overlay" onclick="closeMobile()"></div>

    @include('client.includes.sidebar')

    <div id="main">

        @include('client.includes.header')

        <div class="content">

            <div class="container-fluid py-2">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h4 class="fw-bold m-0">
                        Payroll Summary
                    </h4>

                </div>
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert">
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert">
                        </button>
                    </div>
                @endif

                                @if(!empty($deductionExceptionMessage))
                    <div class="alert alert-info border-start border-4 border-info"
                        style="background:#e3f2fd; color:#0d47a1;" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Note:</strong> {{ $deductionExceptionMessage }}
                    </div>
                @endif

                <div class="dashboard-card">

                    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">

                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('managePayroll') }}" class="flex-grow-1" id="filterForm">

                            <div class="row g-3">

                                <div class="col-md-3">
                                    <label class="filter-label">Month</label>
                                    <select id="month" name="month" class="form-control">
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="filter-label">Year</label>
                                    <select id="year" name="year" class="form-control">
                                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        Filter
                                    </button>
                                </div>

                            </div>

                        </form>
                        
                        <a href="{{ route('exportPayroll', ['month' => $month, 'year' => $year]) }}"
                            class="btn btn-primary export-btn">
                            <i class="bi bi-download"></i>
                            Admin Export
                        </a>
                        <a href="{{ route('exportBankPayroll', ['month' => $month, 'year' => $year]) }}"
                            class="btn btn-primary export-btn">
                            <i class="bi bi-bank"></i>
                            Bank Export
                        </a>

                        <!-- Generate Payroll Button -->
                        <form action="{{ route('generatePayrollForMonth') }}" method="POST" class="mb-0"
                            id="generatePayrollForm">

                            @csrf

                            <input type="hidden" id="generateMonth" name="month" value="{{ $month }}">
                            <input type="hidden" id="generateYear" name="year" value="{{ $year }}">

                            <input type="hidden" name="recover_advance" id="recover_advance" value="0">

                            <button type="submit" class="btn btn-success">
                                Generate Payroll
                            </button>

                        </form>
                    </div>

                                        <!-- ADDED: Display-only Search Filter (Department + Employee Name/ID) -->
                    <!-- This is pure client-side JS filtering of the rendered table rows.
                         It does NOT touch generatePayrollForm in any way, so clicking
                         "Generate Payroll" always generates for ALL employees regardless
                         of what is currently filtered/hidden on screen. -->
                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-md-4">
                            <label class="filter-label small fw-bold">Search Employee</label>
                            <input type="text" id="payrollSearchName" class="form-control"
                                placeholder="Search by name or employee ID...">
                        </div>
                        <div class="col-md-4">
                            <label class="filter-label small fw-bold">Department</label>
                            <select id="payrollSearchDept" class="form-select">
                                <option value="">All Departments</option>
                                @foreach($payrollData as $deptName => $deptEmployees)
                                    <option value="{{ strtolower($deptName) }}">{{ $deptName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="payrollSearchClear" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-x-circle me-1"></i> Clear
                            </button>
                        </div>
                        <div class="col-md-2">
                            <!-- ADDED: Refresh button — simple reload preserving the current month/year filter -->
                            <a href="{{ route('managePayroll', ['month' => $month, 'year' => $year]) }}"
                                class="btn btn-outline-primary w-100" id="payrollRefreshBtn">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">

                       <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th>#</th>

                                    <th>
                                        Employee
                                    </th>

                                    <th>
                                        Working Days
                                    </th>

                                    <th>
                                        Present
                                    </th>

                                    <th>
                                        Absent
                                    </th>
                                    <th>Week Offs</th>
                                    <th>
                                        Holidays
                                    </th>

                                    <th>
                                        Half Day
                                    </th>

                                    <th>
                                        Gross Salary
                                    </th>

                                    <th title="Overtime is already included in Gross Salary and Net Salary">
                                        OT
                                    </th>

                                    <th>
                                        LOP
                                    </th>
                                    <th>Other deductions</th>

                                    <th>
                                        Net Salary
                                    </th>
                                    <th width="260">
                                        Actions
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @php $sno = 1; @endphp

                                @forelse($payrollData as $deptName => $deptEmployees)
                                    
                                    <!-- Department Header Row -->
                                                                        <!-- Department Header Row -->
                                    <tr class="table-light payroll-dept-row" data-dept="{{ strtolower($deptName) }}">
                                        <td colspan="14" class="text-start text-primary fw-bold text-uppercase fs-6">
                                            <i class="bi bi-diagram-3-fill me-1"></i> {{ $deptName }}
                                        </td>
                                    </tr>

                                    @foreach($deptEmployees as $row)
                                        @php
                                            // LOP Popover HTML Builder (Strictly Absent / Half Day / Extra Leaves)
                                            $lopHtml = "<div class='text-start' style='min-width:180px; font-size:12px;'>";
                                            $lopHtml .= "<div class='border-bottom mb-2 pb-1 text-secondary'><strong>Rate: </strong> ₹" . number_format($row['per_day_salary'], 2) . "/day</div>";
                                            
                                            if($row['absent_days'] > 0) {
                                                $lopHtml .= "<div class='d-flex justify-content-between mb-1'><span>Absent (".$row['absent_days']."):</span> <span class='text-danger fw-bold'>-₹" . number_format($row['absent_days'] * $row['per_day_salary'], 2) . "</span></div>";
                                            }
                                            if($row['half_days'] > 0) {
                                                $lopHtml .= "<div class='d-flex justify-content-between mb-1'><span>Half Days (".$row['half_days']."):</span> <span class='text-danger fw-bold'>-₹" . number_format(($row['half_days'] * 0.5) *$row['per_day_salary'], 2) . "</span></div>";
                                            }
                                            if($row['extra_leave_days'] > 0) {
                                                $lopHtml .= "<div class='d-flex justify-content-between mb-1'><span>LOP Amount (".$row['extra_leave_days']."):</span> <span class='text-danger fw-bold'>-₹" . number_format($row['extra_leave_days'] *$row['per_day_salary'], 2) . "</span></div>";
                                            }
                                            $lopHtml .= "</div>";

                                            // Deductions Popover HTML Builder (Now includes Late Login LOP natively)
                                            $dedHtml = "<div class='text-start' style='min-width:180px; font-size:12px;'>";
                                            $hasDeds = false;
                                            foreach($row['deduction_rows'] as $dRow) {
                                                if($dRow['amount'] > 0) {
                                                    $hasDeds = true;
                                                    $dedHtml .= "<div class='d-flex justify-content-between mb-1'><span>" . htmlspecialchars($dRow['title']) . ":</span> <span class='text-danger fw-bold'>-₹" . number_format($dRow['amount'], 2) . "</span></div>";
                                                }
                                            }
                                            if(!$hasDeds) {
                                                $dedHtml .= "<div class='text-muted'>No additional deductions</div>";
                                            }
                                            $dedHtml .= "</div>";
                                        @endphp

                                                                                <tr class="payroll-emp-row" data-dept="{{ strtolower($deptName) }}"
                                            data-emp-name="{{ strtolower($row['employee_name']) }}"
                                            data-emp-id="{{ strtolower($row['employee_id']) }}">

                                            <td>
                                                {{ $sno++ }}
                                            </td>

                                            <td>

                                                <div class="fw-semibold">

                                                    {{ $row['employee_name'] }}

                                                </div>

                                                <small class="text-muted">

                                                    {{ $row['employee_id'] }}

                                                </small>

                                            </td>

                                            <td>

                                                {{ $row['working_days'] }}

                                            </td>

                                            <td>

                                                <span class="badge bg-success">

                                                    {{ $row['present_days'] }}

                                                </span>

                                            </td>

                                            <td>

                                                <span class="badge bg-danger">

                                                    {{ $row['absent_days'] }}

                                                </span>

                                            </td>
                                            <td>{{ $row['week_offs'] }}</td>
                                            <td>

                                                <span class="badge bg-info">

                                                    {{ $row['holidays'] }}

                                                </span>

                                            </td>

                                            <td>

                                                <span class="badge bg-warning text-dark">

                                                    {{ $row['half_days'] }}

                                                </span>

                                            </td>

                                            <td>

                                                <i class="bi bi-currency-rupee"></i>{{ number_format($row['gross_salary'], 2) }}

                                            </td>

                                            <!-- Overtime (already included in the Gross Salary above) -->
                                            <td>
                                                @if(($row['ot_amount'] ?? 0) > 0)
                                                    <span class="text-success fw-bold">
                                                        <i class="bi bi-currency-rupee"></i>{{ number_format($row['ot_amount'], 2) }}
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ (int) ($row['ot_days'] ?? 0) }} Day(s)
                                                    </small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            <!-- LOP Row: Rounded -->
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="text-danger fw-bold">
                                                        <i class="bi bi-currency-rupee"></i>{{ number_format(round($row['lop_amount'])) }}
                                                    </span>
                                                    @if($row['lop_amount'] > 0)
                                                        <i class="bi bi-info-circle-fill text-primary ms-1" style="cursor: pointer; font-size: 13px;" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-html="true" data-bs-content="{{ $lopHtml }}"></i>
                                                    @endif
                                                </div>
                                            </td>
                                            
                                            <!-- Other Deductions Row: Rounded -->
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="text-danger fw-bold">
                                                        <i class="bi bi-currency-rupee"></i>{{ number_format(round($row['other_deductions'])) }}
                                                    </span>
                                                    @if($row['other_deductions'] > 0)
                                                        <i class="bi bi-info-circle-fill text-primary ms-1" style="cursor: pointer; font-size: 13px;" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-html="true" data-bs-content="{{ $dedHtml }}"></i>
                                                    @endif
                                                </div>
                                            </td>


                                            <!-- Net Salary Row: Rounded -->
                                            <td>
                                                <span class="text-success fw-bold">
                                                    <i class="bi bi-currency-rupee"></i>{{ number_format(round($row['net_salary'])) }}
                                                </span>
                                            </td>

                                            <td>

                                                <div class="btn-group" role="group">

                                                    <a href="{{ route(
                                        'viewPayslip',
                                        [
                                            'employee' => $row['id'],
                                            'month' => $month,
                                            'year' => $year
                                        ]
                                    ) }}" class="btn btn-sm btn-primary" title="View Payslip">

                                                        <i class="bi bi-file-earmark-text"></i>

                                                    </a>

                                                    <a href="{{ route(
                                        'manageEmployeeOT',
                                        [
                                            'employee' => $row['id'],
                                            'month' => $month,
                                            'year' => $year
                                        ]
                                    ) }}" class="btn btn-sm btn-warning" title="Manage OT">

                                                        <i class="bi bi-clock-history"></i>

                                                    </a>

                                                    <a href="{{ route(
                                        'manageEmployeeTDS',
                                        [
                                            'employee' => $row['id']
                                        ]
                                    ) }}" class="btn btn-sm btn-info text-white" title="Manage TDS">

                                                        <i class="bi bi-percent"></i>

                                                    </a>

                                                </div>

                                            </td>

                                        </tr>
                                    @endforeach
                                @empty

                                    <tr>

                                        <td colspan="14" class="text-center py-5">

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Initialize Bootstrap Popovers globally for the breakdown tooltips
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl, {
                    sanitize: false // Important: Allows our custom HTML layout/colors inside the tooltip
                })
            });

            const month = document.getElementById('month');
            const year = document.getElementById('year');

            const generateMonth = document.getElementById('generateMonth');
            const generateYear = document.getElementById('generateYear');

            const form = document.getElementById('generatePayrollForm');

            /* Sync Month & Year */

            function syncValues() {

                if (
                    month &&
                    year &&
                    generateMonth &&
                    generateYear
                ) {

                    generateMonth.value = month.value;
                    generateYear.value = year.value;

                }

            }

            if (month) {

                month.addEventListener('change', syncValues);

            }

            if (year) {

                year.addEventListener('change', syncValues);

            }

            syncValues();

            /* Setup Loader Triggers */
            const filterForm = document.getElementById('filterForm');
            if (filterForm) {
                filterForm.addEventListener('submit', function () {
                    document.getElementById('globalLoader').style.display = 'flex';
                });
            }

            document.querySelectorAll('.export-btn').forEach(button => {
                button.addEventListener('click', function () {
                    document.getElementById('globalLoader').style.display = 'flex';
                    
                    // Since exports trigger file downloads, the page doesn't reload.
                    // We must automatically hide the loader after a short delay so the UI remains usable.
                    setTimeout(() => {
                        document.getElementById('globalLoader').style.display = 'none';
                    }, 4000); 
                });
            });

            /* Payroll Confirmation */

            if (form) {

                form.addEventListener('submit', function (e) {

                    e.preventDefault();

                    Swal.fire({

                        title: 'Generate Payroll?',

                        html: `
                    <div style="text-align:left">

                        <p>Please review the following before proceeding:</p>

                        <ul style="padding-left:18px">

                            <li>Verify all Overtime (OT) entries.</li>

                            <li>Attendance has been finalized.</li>

                            <li><strong>Once payroll is generated, it cannot be modified.</strong></li>

                        </ul>

                        <div class="form-check mt-3">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="recoverAdvance">

                            <label
                                class="form-check-label"
                                for="recoverAdvance">

                                Recover eligible employee advances in this payroll.

                            </label>

                        </div>

                    </div>
                `,

                        icon: 'warning',

                        showCancelButton: true,

                        confirmButtonText: 'Generate Payroll',

                        cancelButtonText: 'Cancel',

                        confirmButtonColor: '#198754',

                        cancelButtonColor: '#dc3545'

                    }).then((result) => {

                        if (result.isConfirmed) {

                            const recover = document.getElementById('recover_advance');

                            if (recover) {

                                recover.value =
                                    document.getElementById('recoverAdvance').checked ? 1 : 0;

                            }

                            // Show Loader for Generator Process
                            document.getElementById('globalLoader').style.display = 'flex';
                            form.submit();

                        }

                    });

                });

            }

                        /* ADDED: Display-only Search/Filter for Payroll table (Employee + Department) */
            const searchNameInput = document.getElementById('payrollSearchName');
            const searchDeptSelect = document.getElementById('payrollSearchDept');
            const clearBtn = document.getElementById('payrollSearchClear');

            function applyPayrollFilter() {
                const nameQuery = (searchNameInput?.value || '').trim().toLowerCase();
                const deptQuery = (searchDeptSelect?.value || '').trim().toLowerCase();

                const empRows = document.querySelectorAll('.payroll-emp-row');
                const deptRows = document.querySelectorAll('.payroll-dept-row');

                // Track which departments have at least one visible employee row
                const visibleDepts = new Set();

                empRows.forEach(row => {
                    const rowDept = row.getAttribute('data-dept') || '';
                    const rowName = row.getAttribute('data-emp-name') || '';
                    const rowEmpId = row.getAttribute('data-emp-id') || '';

                    const matchesName = !nameQuery || rowName.includes(nameQuery) || rowEmpId.includes(nameQuery);
                    const matchesDept = !deptQuery || rowDept === deptQuery;

                    const isVisible = matchesName && matchesDept;
                    row.style.display = isVisible ? '' : 'none';

                    if (isVisible) {
                        visibleDepts.add(rowDept);
                    }
                });

                // Show a department header row only if it has at least one visible employee row under it
                deptRows.forEach(row => {
                    const rowDept = row.getAttribute('data-dept') || '';
                    row.style.display = visibleDepts.has(rowDept) ? '' : 'none';
                });
            }

            if (searchNameInput) {
                searchNameInput.addEventListener('input', applyPayrollFilter);
            }
            if (searchDeptSelect) {
                searchDeptSelect.addEventListener('change', applyPayrollFilter);
            }
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    if (searchNameInput) searchNameInput.value = '';
                    if (searchDeptSelect) searchDeptSelect.value = '';
                    applyPayrollFilter();
                });
            }

            // Refresh button: show loader while the page reloads (it's a plain link, not a form)
            const refreshBtn = document.getElementById('payrollRefreshBtn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function () {
                    document.getElementById('globalLoader').style.display = 'flex';
                });
            }

        });
    </script>
</body>

</html>