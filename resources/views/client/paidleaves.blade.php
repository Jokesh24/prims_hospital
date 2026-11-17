<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Paid Leaves Management | HRMS</title>
    @include('client.includes.header_links')

    <!-- ADDED: Select2 CSS for the searchable employee dropdown -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- ADDED: Premium Loader Styles -->
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
            border-top-color: #0d6efd;
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

        @keyframes spinRing {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes pulseLogo {
            0% {
                transform: scale(0.95);
                opacity: 0.85;
                filter: drop-shadow(0 0 0 rgba(13, 110, 253, 0));
            }

            50% {
                transform: scale(1.05);
                opacity: 1;
                filter: drop-shadow(0 0 10px rgba(13, 110, 253, 0.4));
            }

            100% {
                transform: scale(0.95);
                opacity: 0.85;
                filter: drop-shadow(0 0 0 rgba(13, 110, 253, 0));
            }
        }

        @keyframes ellipsis {
            0% {
                content: '';
            }

            25% {
                content: '.';
            }

            50% {
                content: '..';
            }

            75% {
                content: '...';
            }

            100% {
                content: '';
            }
        }

        @keyframes slideUpFade {
            0% {
                transform: translateY(20px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* ADDED: Select2 width fix inside modal */
        .select2-container {
            width: 100% !important;
        }

        .ui-datepicker {
            z-index: 1060 !important;
        }
    </style>
</head>

<body>

    <!-- ADDED: Premium Global Full Page Loader -->
    <div id="globalLoader" class="loader-overlay" style="display: none;">
        <div class="loader-content">
            <div class="logo-loader-container">
                <div class="loader-ring"></div>
                @php
                    $loaderClient = \App\Models\Client::find(session('ClientUser'));
                @endphp
                @if($loaderClient && $loaderClient->logo)
                    <img src="{{ asset('public/uploads/clients/' . $loaderClient->logo) }}" alt="Company Logo"
                        class="loader-logo">
                @else
                    <div class="loader-logo"
                        style="background:#1e293b; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:bold; font-size:12px; border-radius:8px; width:50px; height:50px;">
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

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- HEADER & ACTION BAR -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold m-0">Employee Paid Leaves & Week Offs</h4>
                        <p class="text-muted small m-0">Configure week-off frequency and paid leave allowance
                            individually for each employee.</p>
                    </div>
                    <button class="btn btn-primary px-4 py-2 shadow-sm" style="border-radius:10px;"
                        data-bs-toggle="modal" data-bs-target="#manageSettingsModal" id="btnConfigurePolicy">
                        <i class="bi bi-plus-circle me-2"></i> Configure Policy
                    </button>
                </div>

                <!-- EMPLOYEE POLICIES TABLE -->
                <div class="dashboard-card shadow-sm">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white">
                        <h6 class="m-0 fw-bold">Employee Leave Policies Overview</h6>
                        <span class="badge bg-primary-subtle text-primary border">
                            {{ $policies->count() }} Configured
                        </span>
                    </div>
                    <div class="p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-uppercase small fw-bold text-muted">Employee</th>
                                        <th class="py-3 text-uppercase small fw-bold text-muted">Week Off Type</th>
                                        <th class="py-3 text-uppercase small fw-bold text-muted">Week Offs</th>
                                        <th class="py-3 text-uppercase small fw-bold text-muted">Monthly Paid</th>
                                        <th class="py-3 text-uppercase small fw-bold text-muted">Yearly Paid</th>
                                        <th class="py-3 text-uppercase small fw-bold text-muted">Status</th>
                                        <th class="py-3 text-uppercase small fw-bold text-muted text-end pe-4">Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($policies as $policy)
                                        <tr>
                                            <td class="ps-4">
                                                @if($policy->employee)
                                                    <span class="fw-bold">{{ $policy->employee->employee_name }}</span>
                                                    <br>
                                                    <span class="text-muted small">
                                                        {{ $policy->employee->employee_id ?? '' }}
                                                        @if($policy->employee->department)
                                                            &middot; {{ $policy->employee->department->department_name }}
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="fw-bold text-muted">
                                                        {{ $policy->department->department_name ?? 'All Departments' }}
                                                    </span>
                                                    <br>
                                                    <span class="badge bg-secondary-subtle text-secondary border">Legacy
                                                        Department Fallback</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span
                                                    class="badge {{ $policy->week_off_type === 'monthly' ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary' }} text-capitalize border">
                                                    {{ $policy->week_off_type }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold">{{ $policy->week_offs }}</span>
                                                {{ $policy->week_offs == 1 ? 'Day' : 'Days' }}
                                                <span class="text-muted small">/
                                                    {{ $policy->week_off_type === 'monthly' ? 'month' : 'week' }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-success">{{ $policy->monthly }}</span>
                                                {{ $policy->monthly == 1 ? 'Day' : 'Days' }} / month
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-warning">{{ $policy->yearly }}</span>
                                                {{ $policy->yearly == 1 ? 'Day' : 'Days' }} / year
                                            </td>
                                            <td>
                                                <span
                                                    class="badge {{ $policy->status == 1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                    {{ $policy->status == 1 ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                @if($policy->employee)
                                                    <button type="button"
                                                        class="btn btn-light btn-sm text-primary me-1 edit-policy-btn"
                                                        data-id="{{ $policy->id }}"
                                                        data-employee-id="{{ $policy->employee_id }}"
                                                        data-employee-label="{{ $policy->employee->employee_name }} ({{ $policy->employee->employee_id }})"
                                                        data-week-off-type="{{ $policy->week_off_type }}"
                                                        data-week-offs="{{ $policy->week_offs }}"
                                                        data-monthly="{{ $policy->monthly }}"
                                                        data-yearly="{{ $policy->yearly }}" title="Edit Policy">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                @endif

                                                <form action="{{ route('paidleaves.destroy', $policy->id) }}" method="POST"
                                                    class="d-inline delete-form"
                                                    onsubmit="return confirm('Are you sure you want to delete this policy?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-light btn-sm text-danger"
                                                        title="Delete Policy">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                No employee leave policies configured yet. Click "Configure Policy" above.
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
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="manageSettingsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalPolicyTitle">Configure Employee Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <!-- ADDED: Mode toggle between single-employee and whole-department bulk apply -->
                    <ul class="nav nav-pills nav-fill mb-3" id="policyModeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-employee-btn" data-bs-toggle="pill"
                                data-bs-target="#tab-employee" type="button" role="tab">
                                Single Employee
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-department-btn" data-bs-toggle="pill"
                                data-bs-target="#tab-department" type="button" role="tab">
                                Whole Department
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="policyModeTabsContent">

                        <!-- EXISTING single-employee form, unchanged -->
                        <div class="tab-pane fade show active" id="tab-employee" role="tabpanel">
                            <form action="{{ route('paidleaves.store') }}" method="POST" id="policyForm"
                                class="loader-form">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Select Employee <span
                                            class="text-danger">*</span></label>
                                    <select name="employee_id" id="modal_employee_id"
                                        class="form-select custom-input select2-search" required>
                                        <option value="" selected disabled>Search &amp; Select Employee</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}">
                                                {{ $emp->employee_name }} ({{ $emp->employee_id }})
                                                @if($emp->department) - {{ $emp->department->department_name }} @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" style="font-size: 11px;">Configure unique leave &amp;
                                        week-off settings for this employee only.</small>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label small fw-bold">Week Off Frequency <span
                                                class="text-danger">*</span></label>
                                        <select name="week_off_type" id="modal_week_off_type"
                                            class="form-select custom-input" required>
                                            <option value="weekly" selected>Weekly</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label small fw-bold" id="label_week_offs">Week Offs (Per
                                            Week) <span class="text-danger">*</span></label>
                                        <input type="number" name="week_offs" id="modal_week_offs"
                                            class="form-control custom-input" value="1" required min="0">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label small fw-bold">Monthly Paid Leaves <span
                                                class="text-danger">*</span></label>
                                        <input type="number" name="monthly" id="modal_monthly"
                                            class="form-control custom-input" value="1" required min="0">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label small fw-bold">Yearly Paid Leaves</label>
                                        <input type="number" name="yearly" id="modal_yearly"
                                            class="form-control custom-input bg-light" value="12" required min="0"
                                            readonly>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary py-2 fw-bold w-100">Save
                                        Configuration</button>
                                </div>
                            </form>
                        </div>

                        <!-- ADDED: Whole-department bulk apply form -->
                        <div class="tab-pane fade" id="tab-department" role="tabpanel">
                            <form action="{{ route('paidleaves.storeBulkByDepartment') }}" method="POST"
                                id="bulkDeptPolicyForm" class="loader-form"
                                onsubmit="return confirm('This will overwrite the individual leave policy for EVERY active employee currently in the selected department. Continue?');">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Select Department <span
                                            class="text-danger">*</span></label>
                                    <select name="bulk_department_id" id="bulk_department_id"
                                        class="form-select custom-input" required>
                                        <option value="" selected disabled>Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" style="font-size: 11px;">
                                        Applies this policy individually to every active employee currently assigned
                                        to this department. Existing per-employee policies for those employees will
                                        be overwritten.
                                    </small>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label small fw-bold">Week Off Frequency <span
                                                class="text-danger">*</span></label>
                                        <select name="bulk_week_off_type" id="bulk_week_off_type"
                                            class="form-select custom-input" required>
                                            <option value="weekly" selected>Weekly</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label small fw-bold" id="bulk_label_week_offs">Week Offs
                                            (Per Week) <span class="text-danger">*</span></label>
                                        <input type="number" name="bulk_week_offs" id="bulk_week_offs"
                                            class="form-control custom-input" value="1" required min="0">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label small fw-bold">Monthly Paid Leaves <span
                                                class="text-danger">*</span></label>
                                        <input type="number" name="bulk_monthly" id="bulk_monthly"
                                            class="form-control custom-input" value="1" required min="0">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label small fw-bold">Yearly Paid Leaves</label>
                                        <input type="number" name="bulk_yearly" id="bulk_yearly"
                                            class="form-control custom-input bg-light" value="12" required min="0"
                                            readonly>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-warning py-2 fw-bold w-100 text-dark">
                                        <i class="bi bi-people-fill me-1"></i> Apply to Entire Department
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('client.includes.footer_links')

    <!-- ADDED: jQuery + Select2 JS (scoped to this page, same pattern as leaves.blade.php) -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            /* --- Loader Triggers --- */
            const showLoader = () => {document.getElementById('globalLoader').style.display = 'flex';};

            document.querySelectorAll('.loader-form').forEach(form => {
                form.addEventListener('submit', showLoader);
            });

            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function () {
                    showLoader();
                });
            });

            /* --- Select2 init for the employee dropdown --- */
            $('#modal_employee_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#manageSettingsModal'),
                placeholder: 'Search & Select Employee'
            });

            /* --- UI Logic --- */
            const monthlyInput = document.getElementById('modal_monthly');
            const yearlyInput = document.getElementById('modal_yearly');
            const weekOffTypeSelect = document.getElementById('modal_week_off_type');
            const weekOffLabel = document.getElementById('label_week_offs');

            // Dynamic Week Off Label switch
            weekOffTypeSelect.addEventListener('change', function () {
                if (this.value === 'monthly') {
                    weekOffLabel.innerHTML = 'Week Offs (Per Month) <span class="text-danger">*</span>';
                } else {
                    weekOffLabel.innerHTML = 'Week Offs (Per Week) <span class="text-danger">*</span>';
                }
            });

            // Automatically compute yearly = monthly * 12
            if (monthlyInput && yearlyInput) {
                monthlyInput.addEventListener('input', function () {
                    const monthlyValue = parseFloat(this.value) || 0;
                    yearlyInput.value = monthlyValue * 12;
                });
            }

            // Edit button handler
            const editButtons = document.querySelectorAll('.edit-policy-btn');
            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    document.getElementById('modalPolicyTitle').innerText = 'Edit Employee Policy';

                    const empId = this.getAttribute('data-employee-id');
                    const empLabel = this.getAttribute('data-employee-label');

                    // Ensure the option exists in Select2 (it will, since it's in the original list),
                    // then set and trigger so the Select2 UI updates correctly.
                    $('#modal_employee_id').val(empId).trigger('change');

                    const weekOffType = this.getAttribute('data-week-off-type');
                    weekOffTypeSelect.value = weekOffType;
                    if (weekOffType === 'monthly') {
                        weekOffLabel.innerHTML = 'Week Offs (Per Month) <span class="text-danger">*</span>';
                    } else {
                        weekOffLabel.innerHTML = 'Week Offs (Per Week) <span class="text-danger">*</span>';
                    }

                    document.getElementById('modal_week_offs').value = this.getAttribute('data-week-offs');
                    document.getElementById('modal_monthly').value = this.getAttribute('data-monthly');
                    document.getElementById('modal_yearly').value = this.getAttribute('data-yearly');

                    new bootstrap.Modal(document.getElementById('manageSettingsModal')).show();
                });
            });

            // Reset modal on opening new configure
            document.getElementById('btnConfigurePolicy').addEventListener('click', function () {
                document.getElementById('modalPolicyTitle').innerText = 'Configure Employee Policy';
                document.getElementById('policyForm').reset();
                $('#modal_employee_id').val(null).trigger('change');
                weekOffLabel.innerHTML = 'Week Offs (Per Week) <span class="text-danger">*</span>';
                document.getElementById('modal_yearly').value = 12;
            });

            /* ADDED: Same auto-yearly-calc behavior for the Whole Department bulk form */
            const bulkMonthlyInput = document.getElementById('bulk_monthly');
            const bulkYearlyInput = document.getElementById('bulk_yearly');
            const bulkWeekOffTypeSelect = document.getElementById('bulk_week_off_type');
            const bulkWeekOffLabel = document.getElementById('bulk_label_week_offs');

            if (bulkWeekOffTypeSelect) {
                bulkWeekOffTypeSelect.addEventListener('change', function () {
                    if (this.value === 'monthly') {
                        bulkWeekOffLabel.innerHTML = 'Week Offs (Per Month) <span class="text-danger">*</span>';
                    } else {
                        bulkWeekOffLabel.innerHTML = 'Week Offs (Per Week) <span class="text-danger">*</span>';
                    }
                });
            }

            if (bulkMonthlyInput && bulkYearlyInput) {
                bulkMonthlyInput.addEventListener('input', function () {
                    const monthlyValue = parseFloat(this.value) || 0;
                    bulkYearlyInput.value = monthlyValue * 12;
                });
            }

            /* ADDED: loader trigger for the new bulk form (loader-form class already wired below) */
        });
    </script>
</body>

</html>