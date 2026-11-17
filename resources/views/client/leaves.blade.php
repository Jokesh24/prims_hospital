<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Employee Leaves Management - PRIMS HRMS</title>
    @include('client.includes.header_links')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- jQuery UI CSS for Datepicker -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <!-- Select2 CSS for Searchable Dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        .badge-paid {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-unpaid {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .date-row {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 8px;
        }

        /* Ensure jQuery UI datepicker displays above Bootstrap modals */
        .ui-datepicker {
            z-index: 1060 !important;
        }

        /* Adjust Select2 width inside Bootstrap modals and filters */
        .select2-container {
            width: 100% !important;
        }

        /* --- ADDED: Premium Loader Styles --- */
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
            /* Primary Color */
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
                    <img src="{{ asset('public/uploads/clients/' . $loaderClient->logo) }}" alt="Company Logo" class="loader-logo">
                @else
                    <div class="loader-logo" style="background:#1e293b; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:bold; font-size:12px; border-radius:8px; width:50px; height:50px;">
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold m-0">Employee Leaves</h4>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary px-3 py-2" data-bs-toggle="modal"
                            data-bs-target="#manageLeaveTypesModal">
                            <i class="bi bi-gear me-1"></i> Manage Leave Types
                        </button>
                        <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal"
                            data-bs-target="#addLeaveModal">
                            <i class="bi bi-plus-circle me-2"></i> Apply Leave
                        </button>
                    </div>
                </div>

                <!-- Filter Card -->
                <div class="dashboard-card mb-3">
                    <form method="GET" action="{{ route('client.leaves') }}" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="filter-label small fw-bold">Filter By Employee</label>
                                <select name="employee_id" id="filter_employee_id"
                                    class="form-select filter-control select2-search">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->employee_name }} ({{ $emp->employee_id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="filter-label small fw-bold">Leave Type</label>
                                <select name="leave_type_id" class="form-select filter-control">
                                    <option value="">All Types</option>
                                    @foreach($leaveTypes as $lt)
                                        <option value="{{ $lt->id }}" {{ request('leave_type_id') == $lt->id ? 'selected' : '' }}>
                                            {{ $lt->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary text-white">
                                        <i class="bi bi-search"></i> Filter
                                    </button>
                                    <a href="{{ route('client.leaves') }}" class="btn btn-refresh border"
                                        id="resetFilterBtn">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Leaves Table -->
                <div class="dashboard-card">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3" style="width:50px;">#</th>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Leave Dates & Status (Click to Toggle)</th>
                                    <th>Remarks</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaves as $leave)
                                    <tr>
                                        <td class="ps-4">
                                            {{ ($leaves->currentPage() - 1) * $leaves->perPage() + $loop->iteration }}
                                        </td>
                                        <td>
                                            <strong>{{ $leave->employee->employee_name ?? 'N/A' }}</strong>
                                            @if(!empty($leave->employee->employee_id))
                                                <br><small class="text-muted">{{ $leave->employee->employee_id }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-light text-dark border">{{ $leave->leaveType->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($leave->dates as $dt)
                                                    <span
                                                        class="badge {{ $dt->is_paid ? 'badge-paid' : 'badge-unpaid' }} d-inline-flex align-items-center gap-1"
                                                        style="cursor: pointer;" title="Click to toggle Paid/Unpaid status"
                                                        onclick="openStatusModal({{ $dt->id }}, {{ $dt->is_paid }}, '{{ $dt->date->format('d-m-Y') }}')">
                                                        <i
                                                            class="bi {{ $dt->is_paid ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                                                        {{ $dt->date->format('d-m-Y') }}
                                                        ({{ $dt->is_paid ? 'Paid' : 'Unpaid' }})
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>{{ $leave->remarks ? Str::limit($leave->remarks, 30) : '-' }}</td>
                                        <td class="text-end pe-4">
                                            <!-- Edit Button -->
                                            <button type="button"
                                                class="btn btn-light btn-sm text-primary me-1 edit-leave-btn"
                                                data-id="{{ $leave->id }}" data-employee-id="{{ $leave->employee_id }}"
                                                data-employee-name="{{ $leave->employee->employee_name ?? '' }} ({{ $leave->employee->employee_id ?? '' }})"
                                                data-leave-type-id="{{ $leave->leave_type_id }}"
                                                data-remarks="{{ $leave->remarks }}"
                                                data-override-reason="{{ $leave->latestLog->reason ?? '' }}"
                                                data-dates='@json($leave->dates->map(fn($d) => ["date" => $d->date->format("d-m-Y"), "is_paid" => $d->is_paid]))'
                                                title="Edit Leave">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <!-- Delete Button -->
                                            <form action="{{ route('leaves.destroy', $leave->id) }}" method="POST"
                                                class="d-inline delete-leave-form"
                                                onsubmit="return confirm('Are you sure you want to delete this leave record?');">
                                                @csrf
                                                <button type="submit" class="btn btn-light btn-sm text-danger"
                                                    title="Delete Leave">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No Leave Records Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-4 px-3">
                        {{ $leaves->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Apply Leave Modal -->
    <div class="modal fade" id="addLeaveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content custom-modal">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Apply Employee Leave</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('leaves.store') }}" method="POST" id="applyLeaveForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Select Employee <span
                                        class="text-danger">*</span></label>
                                <select name="employee_id" id="modal_employee_id"
                                    class="form-select custom-input select2-search" required>
                                    <option value="" selected disabled>Select Employee</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">
                                            {{ $emp->employee_name }} ({{ $emp->employee_id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold m-0">Leave Type <span
                                            class="text-danger">*</span></label>
                                    <a href="javascript:void(0)" class="small text-primary text-decoration-none fw-bold"
                                        id="openLeaveTypeBtn">
                                        <i class="bi bi-plus-circle"></i> Add/Manage Types
                                    </a>
                                </div>
                                <select name="leave_type_id" id="modal_leave_type_id" class="form-select custom-input"
                                    required>
                                    <option value="" selected disabled>Select Type</option>
                                    @foreach($leaveTypes as $lt)
                                        <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Paid Leaves Counter Card -->
                        <div
                            class="alert alert-light border d-flex justify-content-between align-items-center p-2 px-3 mb-3">
                            <div>
                                <span class="small text-muted d-block">Paid Leaves Available this month</span>
                                <strong class="fs-6 text-success" id="balance_display">Select an employee to see
                                    balance</strong>
                            </div>
                            <span class="badge bg-primary px-3 py-2 fs-6" id="balance_badge">0 Left</span>
                        </div>

                        <!-- Dynamic Dates Section -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-bold m-0">Leave Dates & Allocation <span
                                        class="text-danger">*</span></label>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_date">
                                    <i class="bi bi-plus-lg"></i> Add Another Date
                                </button>
                            </div>
                            <div id="dates_container">
                                <!-- First Date Row -->
                                <div class="date-row row g-2 align-items-center">
                                    <div class="col-md-6">
                                        <input type="text" name="dates[]"
                                            class="form-control form-control-sm leave-datepicker"
                                            placeholder="DD-MM-YYYY" readonly required>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="is_paid[]" class="form-select form-select-sm leave-paid-select"
                                            required>
                                            <option value="1">Paid Leave</option>
                                            <option value="0">Unpaid Leave</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button type="button"
                                            class="btn btn-sm btn-light text-danger disabled border-0 remove-date-btn">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Conditional Override Reason Box -->
                        <div class="mb-3 d-none" id="override_reason_box">
                            <label class="form-label small fw-bold text-danger">
                                <i class="bi bi-info-circle me-1"></i> Paid Leave Quota Exceeded Reason <span
                                    class="text-danger">*</span>
                            </label>
                            <textarea name="override_reason" id="override_reason" class="form-control form-control-sm"
                                rows="2"
                                placeholder="Explain why this is approved as paid leave despite 0 balance (Saved to Audit Log)..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Remarks (Optional)</label>
                            <textarea name="remarks" class="form-control custom-input" rows="2"
                                placeholder="Reason for leave..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary py-2 fw-bold w-100">Submit Leave
                            Application</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Leave Modal -->
    <div class="modal fade" id="editLeaveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content custom-modal">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Edit Employee Leave</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="editLeaveForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_leave_id">
                        <input type="hidden" id="edit_employee_id" name="employee_id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Employee</label>
                                <input type="text" id="edit_employee_name" class="form-control custom-input bg-light"
                                    readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Leave Type <span
                                        class="text-danger">*</span></label>
                                <select name="leave_type_id" id="edit_leave_type_id" class="form-select custom-input"
                                    required>
                                    @foreach($leaveTypes as $lt)
                                        <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Paid Leaves Counter Card -->
                        <div
                            class="alert alert-light border d-flex justify-content-between align-items-center p-2 px-3 mb-3">
                            <div>
                                <span class="small text-muted d-block">Paid Leaves Available this month</span>
                                <strong class="fs-6 text-success" id="edit_balance_display">Calculating...</strong>
                            </div>
                            <span class="badge bg-primary px-3 py-2 fs-6" id="edit_balance_badge">0 Left</span>
                        </div>

                        <!-- Dynamic Dates Section -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label small fw-bold m-0">Leave Dates & Allocation <span
                                        class="text-danger">*</span></label>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_edit_add_date">
                                    <i class="bi bi-plus-lg"></i> Add Another Date
                                </button>
                            </div>
                            <div id="edit_dates_container"></div>
                        </div>

                        <!-- Conditional Override Reason Box -->
                        <div class="mb-3 d-none" id="edit_override_reason_box">
                            <label class="form-label small fw-bold text-danger">
                                <i class="bi bi-info-circle me-1"></i> Paid Leave Quota Exceeded Reason <span
                                    class="text-danger">*</span>
                            </label>
                            <textarea name="override_reason" id="edit_override_reason"
                                class="form-control form-control-sm" rows="2"
                                placeholder="Explain why this is approved as paid leave despite 0 balance (Saved to Audit Log)..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Remarks (Optional)</label>
                            <textarea name="remarks" id="edit_remarks" class="form-control custom-input" rows="2"
                                placeholder="Reason for leave..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary py-2 fw-bold w-100">Update Leave
                            Application</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal to Manage Leave Types -->
    <div class="modal fade" id="manageLeaveTypesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Manage Leave Types</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="lt_alert_box"></div>

                    <!-- Create Leave Type -->
                    <form id="createLeaveTypeForm" class="mb-4">
                        <div class="input-group">
                            <input type="text" id="new_leave_type_name" class="form-control"
                                placeholder="Leave Type Name (e.g. Sick Leave)" required>
                            <button class="btn btn-primary" type="submit" id="btn_submit_lt">
                                <i class="bi bi-plus-lg"></i> Add
                            </button>
                        </div>
                    </form>

                    <!-- Types List -->
                    <h6 class="small fw-bold text-muted text-uppercase mb-2">Existing Leave Types</h6>
                    <ul class="list-group" id="leave_types_list" style="max-height: 220px; overflow-y: auto;">
                        @foreach($leaveTypes as $type)
                            <li class="list-group-item d-flex justify-content-between align-items-center"
                                id="lt_item_{{ $type->id }}">
                                <span class="lt-name fw-medium">{{ $type->name }}</span>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-secondary edit-lt-btn" data-id="{{ $type->id }}"
                                        data-name="{{ $type->name }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-danger delete-lt-btn" data-id="{{ $type->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Date Paid/Unpaid Status Modal with Audit Log -->
    <div class="modal fade" id="dateStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content custom-modal">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold fs-6">Change Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Leave Date: <strong id="modal_date_display"></strong></p>
                    <input type="hidden" id="status_date_id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select New Status</label>
                        <select id="modal_is_paid_status" class="form-select">
                            <option value="1">Paid</option>
                            <option value="0">Unpaid</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Reason for Change (Audit Log)</label>
                        <input type="text" id="status_change_reason" class="form-control form-control-sm"
                            placeholder="e.g. Approved by HR">
                    </div>
                    <button type="button" class="btn btn-primary w-100" id="btn_save_date_status">Update & Log</button>
                </div>
            </div>
        </div>
    </div>

    @include('client.includes.footer_links')

    <!-- jQuery & jQuery UI -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            let currentPaidBalance = 0;
            let editPaidBalance = 0;

            /* --- Loader Logic Setup --- */
            const showLoader = () => { document.getElementById('globalLoader').style.display = 'flex'; };
            const hideLoader = () => { document.getElementById('globalLoader').style.display = 'none'; };

            // 1. Filter Form Trigger
            const filterForm = document.getElementById('filterForm');
            if (filterForm) {
                filterForm.addEventListener('submit', showLoader);
            }

            // 2. Reset Button Trigger
            const resetBtn = document.getElementById('resetFilterBtn');
            if (resetBtn) {
                resetBtn.addEventListener('click', showLoader);
            }

            // 3. Delete Buttons
            $('.delete-leave-form').on('submit', function () {
                showLoader(); // Confirm is handled natively in HTML onsubmit, so this only runs if true
            });

            // Initialize Select2 on employee dropdowns
            $('.select2-search').each(function () {
                const $this = $(this);
                $this.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $this.closest('.modal').length ? $this.closest('.modal') : $(document.body)
                });
            });

            const addLeaveModal = new bootstrap.Modal(document.getElementById('addLeaveModal'));
            const editLeaveModal = new bootstrap.Modal(document.getElementById('editLeaveModal'));
            const manageLeaveTypesModal = new bootstrap.Modal(document.getElementById('manageLeaveTypesModal'));

            // Helper: Initialize jQuery UI Datepicker in dd-mm-yy format locking past dates
            function initDatepicker(selector, onSelectCallback) {
                $(selector).datepicker({
                    dateFormat: 'dd-mm-yy',
                    // minDate: 0,
                    changeMonth: true,
                    changeYear: true,
                    onSelect: function (dateText) {
                        if (typeof onSelectCallback === 'function') {
                            onSelectCallback(dateText);
                        }
                    }
                });
            }

            // Initialize datepickers
            initDatepicker('.leave-datepicker', function () {
                fetchBalance();
            });

            // Transition between Apply Leave modal and Manage Leave Types modal
            let openedFromAddModal = false;
            $('#openLeaveTypeBtn').on('click', function () {
                openedFromAddModal = true;
                addLeaveModal.hide();
                manageLeaveTypesModal.show();
            });

            $('#manageLeaveTypesModal').on('hidden.bs.modal', function () {
                if (openedFromAddModal) {
                    openedFromAddModal = false;
                    addLeaveModal.show();
                }
            });

            // Check if reason is required when paid leaves left = 0 or quota is exceeded
            function checkOverrideReason(containerSelector, balance, boxSelector, textareaSelector) {
                let paidSelectedCount = 0;
                $(`${containerSelector} .leave-paid-select`).each(function () {
                    if ($(this).val() === '1') {
                        paidSelectedCount++;
                    }
                });

                if ((balance <= 0 && paidSelectedCount > 0) || paidSelectedCount > balance) {
                    $(boxSelector).removeClass('d-none'); $(textareaSelector).prop('required', true);
                } else {
                    $(boxSelector).addClass('d-none'); $(textareaSelector).prop('required', false);
                }
            }

            // Balance Fetch for Add Modal
            $('#modal_employee_id').on('change', fetchBalance);

            function fetchBalance() {
                const empId = $('#modal_employee_id').val();
                if (!empId) return;

                const firstDate = $('#dates_container .leave-datepicker').first().val();

                $.ajax({
                    url: '{{ route("leaves.paid_left") }}',
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: JSON.stringify({ employee_id: empId, date: firstDate }),
                    contentType: 'application/json',
                    success: function (data) {
                        currentPaidBalance = data.leaves_left;
                        $('#balance_display').text(`${data.leaves_left} out of ${data.monthly_quota} days remaining this month.`);
                        $('#balance_badge').text(`${data.leaves_left} Left`);
                        recalculateAllocations('#dates_container', currentPaidBalance);
                        checkOverrideReason('#dates_container', currentPaidBalance, '#override_reason_box', '#override_reason');
                    }
                });
            }

            function recalculateAllocations(containerSelector, balance) {
                let remaining = balance;
                $(`${containerSelector} .leave-paid-select`).each(function () {
                    if (remaining > 0) {
                        $(this).val('1');
                        remaining--;
                    } else {
                        $(this).val('0');
                    }
                });
            }

            // Add Dynamic Date Row in Add Modal
            $('#btn_add_date').on('click', function () {
                const row = $(`
                    <div class="date-row row g-2 align-items-center">
                        <div class="col-md-6">
                            <input type="text" name="dates[]" class="form-control form-control-sm leave-datepicker" 
                                placeholder="DD-MM-YYYY" readonly required>
                        </div>
                        <div class="col-md-4">
                            <select name="is_paid[]" class="form-select form-select-sm leave-paid-select" required>
                                <option value="1">Paid Leave</option>
                                <option value="0">Unpaid Leave</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-sm btn-light text-danger border-0 remove-date-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                `);

                $('#dates_container').append(row);
                initDatepicker(row.find('.leave-datepicker'), function () {
                    fetchBalance();
                });

                recalculateAllocations('#dates_container', currentPaidBalance);
                checkOverrideReason('#dates_container', currentPaidBalance, '#override_reason_box', '#override_reason');
            });

            $(document).on('click', '#dates_container .remove-date-btn', function () {
                $(this).closest('.date-row').remove();
                recalculateAllocations('#dates_container', currentPaidBalance);
                checkOverrideReason('#dates_container', currentPaidBalance, '#override_reason_box', '#override_reason');
            });

            $(document).on('change', '#dates_container .leave-paid-select', function () {
                checkOverrideReason('#dates_container', currentPaidBalance, '#override_reason_box', '#override_reason');
            });

            // Prevent duplicate dates on Add Form Submit
            $('#applyLeaveForm').on('submit', function (e) {
                const dateVals = $('#dates_container .leave-datepicker').map(function () {
                    return $(this).val();
                }).get().filter(Boolean);

                if (new Set(dateVals).size !== dateVals.length) {
                    e.preventDefault();
                    alert('You cannot select the same date multiple times.');
                    return false;
                }

                showLoader(); // Loader for Add Submit
            });

            // --- EDIT MODAL LOGIC ---
            $('.edit-leave-btn').on('click', function () {
                const leaveId = $(this).data('id');
                const employeeId = $(this).data('employee-id');
                const employeeName = $(this).data('employee-name');
                const leaveTypeId = $(this).data('leave-type-id');
                const remarks = $(this).data('remarks');
                const overrideReason = $(this).data('override-reason') || '';
                const dates = $(this).data('dates');

                $('#edit_leave_id').val(leaveId);
                $('#edit_employee_id').val(employeeId);
                $('#edit_employee_name').val(employeeName);
                $('#edit_leave_type_id').val(leaveTypeId);
                $('#edit_remarks').val(remarks);
                $('#edit_override_reason').val(overrideReason);

                let updateUrl = '{{ route("leaves.update", ":id") }}'.replace(':id', leaveId);
                $('#editLeaveForm').attr('action', updateUrl);

                // Populate Dates Container
                $('#edit_dates_container').empty();
                dates.forEach((d, index) => {
                    const row = $(`
                        <div class="date-row row g-2 align-items-center">
                            <div class="col-md-6">
                                <input type="text" name="dates[]" class="form-control form-control-sm edit-datepicker" 
                                    value="${d.date}" placeholder="DD-MM-YYYY" readonly required>
                            </div>
                            <div class="col-md-4">
                                <select name="is_paid[]" class="form-select form-select-sm leave-paid-select" required>
                                    <option value="1" ${d.is_paid == 1 ? 'selected' : ''}>Paid Leave</option>
                                    <option value="0" ${d.is_paid == 0 ? 'selected' : ''}>Unpaid Leave</option>
                                </select>
                            </div>
                            <div class="col-md-2 text-end">
                                <button type="button" class="btn btn-sm btn-light text-danger border-0 remove-edit-date-btn ${index === 0 && dates.length === 1 ? 'disabled' : ''}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    `);
                    $('#edit_dates_container').append(row);
                });

                initDatepicker('.edit-datepicker', function () {
                    fetchEditBalance();
                });

                fetchEditBalance();
                editLeaveModal.show();
            });

            function fetchEditBalance() {
                const empId = $('#edit_employee_id').val();
                const leaveId = $('#edit_leave_id').val();
                const firstDate = $('#edit_dates_container .edit-datepicker').first().val();

                $.ajax({
                    url: '{{ route("leaves.paid_left") }}',
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: JSON.stringify({ employee_id: empId, date: firstDate, ignore_leave_id: leaveId }),
                    contentType: 'application/json',
                    success: function (data) {
                        editPaidBalance = data.leaves_left;
                        $('#edit_balance_display').text(`${data.leaves_left} out of ${data.monthly_quota} days remaining this month.`);
                        $('#edit_balance_badge').text(`${data.leaves_left} Left`);
                        checkOverrideReason('#edit_dates_container', editPaidBalance, '#edit_override_reason_box', '#edit_override_reason');
                    }
                });
            }

            $('#btn_edit_add_date').on('click', function () {
                const row = $(`
                    <div class="date-row row g-2 align-items-center">
                        <div class="col-md-6">
                            <input type="text" name="dates[]" class="form-control form-control-sm edit-datepicker" 
                                placeholder="DD-MM-YYYY" readonly required>
                        </div>
                        <div class="col-md-4">
                            <select name="is_paid[]" class="form-select form-select-sm leave-paid-select" required>
                                <option value="1">Paid Leave</option>
                                <option value="0">Unpaid Leave</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-sm btn-light text-danger border-0 remove-edit-date-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                `);

                $('#edit_dates_container').append(row);
                initDatepicker(row.find('.edit-datepicker'), function () {
                    fetchEditBalance();
                });

                checkOverrideReason('#edit_dates_container', editPaidBalance, '#edit_override_reason_box', '#edit_override_reason');
            });

            $(document).on('click', '#edit_dates_container .remove-edit-date-btn', function () {
                if ($('#edit_dates_container .date-row').length > 1) {
                    $(this).closest('.date-row').remove();
                    checkOverrideReason('#edit_dates_container', editPaidBalance, '#edit_override_reason_box', '#edit_override_reason');
                }
            });

            $(document).on('change', '#edit_dates_container .leave-paid-select', function () {
                checkOverrideReason('#edit_dates_container', editPaidBalance, '#edit_override_reason_box', '#edit_override_reason');
            });

            $('#editLeaveForm').on('submit', function (e) {
                const dateVals = $('#edit_dates_container .edit-datepicker').map(function () {
                    return $(this).val();
                }).get().filter(Boolean);

                if (new Set(dateVals).size !== dateVals.length) {
                    e.preventDefault();
                    alert('You cannot select the same date multiple times.');
                    return false;
                }

                showLoader(); // Loader for Edit Submit
            });

            // Single Date Paid/Unpaid Status Modal
            window.openStatusModal = function (dateId, currentStatus, dateStr) {
                $('#status_date_id').val(dateId);
                $('#modal_date_display').text(dateStr);
                $('#modal_is_paid_status').val(currentStatus);
                $('#status_change_reason').val('');
                new bootstrap.Modal(document.getElementById('dateStatusModal')).show();
            };

            $('#btn_save_date_status').on('click', function () {
                const dateId = $('#status_date_id').val();
                const isPaid = $('#modal_is_paid_status').val();
                const reason = $('#status_change_reason').val();

                showLoader(); // Loader for inline status update

                $.ajax({
                    url: '{{ route("leaves.update_date_status") }}',
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: JSON.stringify({ date_id: dateId, is_paid: isPaid, reason: reason }),
                    contentType: 'application/json',
                    success: function (res) {
                        if (res.success) {
                            location.reload();
                        } else {
                            hideLoader();
                            alert(res.message);
                        }
                    },
                    error: function () {
                        hideLoader();
                        alert("A network error occurred.");
                    }
                });
            });

            // Create Leave Type AJAX
            $('#createLeaveTypeForm').on('submit', function (e) {
                e.preventDefault();
                const nameInput = $('#new_leave_type_name');
                const name = nameInput.val().trim();
                const alertBox = $('#lt_alert_box');

                if (!name) return;

                $.ajax({
                    url: '{{ route("leave_types.store") }}',
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: JSON.stringify({ name: name, status: 1 }),
                    contentType: 'application/json',
                    success: function (res) {
                        if (res.success) {
                            alertBox.html(`<div class="alert alert-success py-2 small mb-2">${res.message}</div>`);
                            nameInput.val('');

                            const li = $(`
                                <li class="list-group-item d-flex justify-content-between align-items-center" id="lt_item_${res.data.id}">
                                    <span class="lt-name fw-medium">${res.data.name}</span>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-secondary edit-lt-btn" data-id="${res.data.id}" data-name="${res.data.name}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger delete-lt-btn" data-id="${res.data.id}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </li>
                            `);
                            $('#leave_types_list').prepend(li);

                            $('#modal_leave_type_id, #edit_leave_type_id').append(new Option(res.data.name, res.data.id, true, true));

                            if (openedFromAddModal) {
                                setTimeout(() => {
                                    alertBox.empty();
                                    manageLeaveTypesModal.hide();
                                }, 700);
                            }
                        } else {
                            alertBox.html(`<div class="alert alert-danger py-2 small mb-2">${res.message}</div>`);
                        }
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Error creating leave type.';
                        alertBox.html(`<div class="alert alert-danger py-2 small mb-2">${msg}</div>`);
                    }
                });
            });

            // Edit Leave Type
            $(document).on('click', '.edit-lt-btn', function () {
                const id = $(this).data('id');
                const currentName = $(this).data('name');
                const newName = prompt('Update Leave Type Name:', currentName);

                if (newName && newName.trim() !== '' && newName !== currentName) {
                    $.ajax({
                        url: `{{ url('client/leave-types') }}/${id}/update`,
                        type: 'PUT',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        data: JSON.stringify({ name: newName.trim(), status: 1 }),
                        contentType: 'application/json',
                        success: function (res) {
                            if (res.success) {
                                $(`#lt_item_${id} .lt-name`).text(newName);
                                $(`#lt_item_${id} .edit-lt-btn`).data('name', newName);
                                $(`#modal_leave_type_id option[value="${id}"], #edit_leave_type_id option[value="${id}"]`).text(newName);
                            } else {
                                alert(res.message);
                            }
                        }
                    });
                }
            });

            // Delete Leave Type
            $(document).on('click', '.delete-lt-btn', function () {
                if (confirm('Are you sure you want to delete this leave type?')) {
                    const id = $(this).data('id'); $.ajax({
                        url: `{{ url('client/leave-types') }}/${id}/destroy`,
                        type: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        success: function (res) {
                            if (res.success) {
                                $(`#lt_item_${id}`).remove();
                                $(`#modal_leave_type_id option[value="${id}"], #edit_leave_type_id option[value="${id}"]`).remove();
                            } else {
                                alert(res.message);
                            }
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>