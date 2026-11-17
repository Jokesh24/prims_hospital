<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pending Approvals - HRMS</title>
    @include('client.includes.header_links')
    <style>
        /* =========================
   Page Base
========================= */
        body {
            background: #f5f7fb;
            color: #1e293b;
            font-family: inherit;
        }

        #main .content {
            min-height: calc(100vh - 70px);
        }

        .content .container-fluid {
            max-width: 1600px;
            padding-left: 28px;
            padding-right: 28px;
        }

        /* =========================
   Page Header
========================= */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 26px;
        }

        .page-heading {
            display: flex;
            align-items: flex-start;
            gap: 13px;
        }

        .page-heading-icon {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eaf2ff;
            color: #2563eb;
            border-radius: 12px;
            font-size: 21px;
            flex-shrink: 0;
        }

        .page-heading h4 {
            font-size: 23px;
            font-weight: 700;
            color: #172033;
            margin: 0 0 5px;
            letter-spacing: -0.45px;
        }

        .page-heading p {
            color: #64748b;
            font-size: 13.5px;
            margin: 0;
            line-height: 1.6;
        }

        /* =========================
   Search
========================= */
        .search-wrapper {
            position: relative;
            width: 340px;
            max-width: 100%;
            flex-shrink: 0;
        }

        .search-wrapper .bi-search {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 15px;
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            min-height: 44px;
            padding: 10px 14px 10px 40px;
            border: 1px solid #dfe5ee;
            border-radius: 9px;
            background: #fff;
            color: #334155;
            font-size: 13px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
            transition: border-color .2s, box-shadow .2s;
        }

        .search-input::placeholder {
            color: #94a3b8;
        }

        .search-input:focus {
            border-color: #93b4ff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .09) !important;
        }

        /* =========================
   Alerts
========================= */
        .alert {
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            padding: 14px 16px;
        }

        /* =========================
   Main Table Card
========================= */
        .dashboard-card {
            background: #fff;
            border: 1px solid #e8edf4 !important;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .035);
            overflow: hidden;
        }

        .table-responsive {
            width: 100%;
        }

        #approvalsTable {
            width: 100%;
            min-width: 900px;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }

        #approvalsTable thead th {
            background: #f8fafc;
            color: #64748b;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .65px;
            text-transform: uppercase;
            padding: 16px 20px;
            height: 50px;
            vertical-align: middle;
            border-top: 0;
            border-bottom: 1px solid #e9eef5 !important;
            white-space: nowrap;
        }

        #approvalsTable tbody td {
            padding: 17px 20px;
            vertical-align: middle;
            color: #475569;
            font-size: 13px;
            background: #fff;
            border-bottom: 1px solid #edf1f6;
            overflow-wrap: anywhere;
        }

        #approvalsTable tbody tr:last-child td {
            border-bottom: 0;
        }

        #approvalsTable tbody tr.approval-row {
            transition: background-color .15s ease;
        }

        #approvalsTable tbody tr.approval-row:hover td {
            background: #fbfcff;
        }

        /* Column widths */
        #approvalsTable th:nth-child(1),
        #approvalsTable td:nth-child(1) {
            width: 25%;
        }

        #approvalsTable th:nth-child(2),
        #approvalsTable td:nth-child(2) {
            width: 17%;
        }

        #approvalsTable th:nth-child(3),
        #approvalsTable td:nth-child(3) {
            width: 25%;
        }

        #approvalsTable th:nth-child(4),
        #approvalsTable td:nth-child(4) {
            width: 21%;
        }

        #approvalsTable th:nth-child(5),
        #approvalsTable td:nth-child(5) {
            width: 12%;
        }

        /* =========================
   Employee Details
========================= */
        .employee-info {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .avatar-circle {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 11px;
            background: #eaf2ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
            flex-shrink: 0;
        }

        .employee-details {
            min-width: 0;
        }

        .employee-name {
            display: block;
            color: #1e293b;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.45;
            margin-bottom: 5px;
            overflow-wrap: anywhere;
        }

        .employee-department {
            display: block;
            color: #8491a5;
            font-size: 11.5px;
            line-height: 1.45;
        }

        /* =========================
   Leave Type
========================= */
        .leave-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            max-width: 100%;
            padding: 7px 10px;
            background: #fff7e6;
            color: #a16207;
            border: 1px solid #fce7b1;
            border-radius: 7px;
            font-size: 11.5px;
            font-weight: 600;
            line-height: 1.4;
            white-space: normal;
        }

        .leave-type-badge i {
            font-size: 13px;
            flex-shrink: 0;
        }

        /* =========================
   Requested Dates
========================= */
        .date-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }

        .date-badge {
            display: inline-block;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 5px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
            line-height: 1.4;
            white-space: nowrap;
        }

        /* =========================
   Remarks
========================= */
        .remarks-text {
            display: block;
            color: #64748b;
            font-size: 12px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        /* =========================
   Review Button
========================= */
        .review-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            min-height: 34px;
            padding: 7px 10px;
            border: 1px solid #dce6f7;
            border-radius: 7px;
            background: #f8faff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            transition: all .2s ease;
        }

        .review-btn:hover,
        .review-btn:focus {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .review-btn i {
            font-size: 16px;
        }

        /* =========================
   Empty States
========================= */
        #defaultEmptyRow td,
        #noResultsRow td {
            padding: 55px 20px !important;
            text-align: center;
        }

        .empty-state-icon {
            color: #94a3b8;
            font-size: 38px;
            opacity: .7;
        }

        .empty-state-title {
            color: #334155;
            font-size: 15px;
            font-weight: 700;
            margin-top: 13px;
        }

        .empty-state-description {
            color: #94a3b8;
            font-size: 12px;
            margin: 6px 0 0;
        }

        /* =========================
   Pagination
========================= */
        .dashboard-card .pagination {
            margin: 0;
            gap: 4px;
            flex-wrap: wrap;
        }

        .dashboard-card .page-link {
            border: 1px solid #e2e8f0;
            border-radius: 6px !important;
            color: #475569;
            font-size: 12px;
            padding: 7px 11px;
            margin: 0;
        }

        .dashboard-card .page-item.active .page-link {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .dashboard-card .page-item.disabled .page-link {
            color: #cbd5e1;
            background: #f8fafc;
        }

        /* =========================
   Modal
========================= */
        #actionModal .modal-dialog {
            max-width: 540px;
            margin: 1.25rem auto;
        }

        #actionModal .modal-content {
            border: 1px solid #e9eef5;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .15);
        }

        #actionModal .modal-header {
            padding: 22px 24px 17px;
            border-bottom: 1px solid #edf1f6;
        }

        #actionModal .modal-title {
            font-size: 17px;
            display: flex;
            align-items: center;
        }

        #actionModal .modal-body {
            padding: 22px 24px;
        }

        #actionModal .form-label {
            font-size: 12px !important;
            margin-bottom: 9px;
        }

        #actionModal textarea {
            min-height: 110px;
            padding: 12px 14px;
            border: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
            color: #334155;
            line-height: 1.6;
        }

        #actionModal textarea:focus {
            background: #fff !important;
            border-color: #93b4ff !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .08) !important;
        }

        #actionModal .modal-body .text-muted {
            font-size: 11.5px !important;
            line-height: 1.6;
        }

        #actionModal .modal-footer {
            padding: 16px 24px 22px;
            border-top: 1px solid #edf1f6;
            gap: 10px;
        }

        #actionModal .btn {
            min-height: 39px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            padding: 9px 13px;
        }

        #actionModal .btn-outline-danger {
            background: #fff;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        #actionModal .btn-outline-danger:hover {
            background: #fef2f2;
            border-color: #fca5a5;
        }

        #actionModal .btn-success {
            background: #059669;
            border: 1px solid #059669;
            color: #fff;
        }

        #actionModal .btn-success:hover {
            background: #047857;
            border-color: #047857;
        }

        /* =========================
   Responsive Layout
========================= */
        @media (max-width: 991.98px) {
            .content .container-fluid {
                padding-left: 20px;
                padding-right: 20px;
            }

            .page-heading h4 {
                font-size: 21px;
            }

            .search-wrapper {
                width: 300px;
            }

            #approvalsTable {
                min-width: 900px;
            }
        }

        @media (max-width: 575.98px) {
            .content .container-fluid {
                padding: 18px 12px;
            }

            .page-header {
                align-items: stretch;
                gap: 15px;
                margin-bottom: 20px;
            }

            .page-heading {
                gap: 10px;
            }

            .page-heading-icon {
                width: 39px;
                height: 39px;
                font-size: 18px;
            }

            .page-heading h4 {
                font-size: 19px;
            }

            .page-heading p {
                font-size: 12px;
            }

            .search-wrapper {
                width: 100%;
            }

            #approvalsTable thead th,
            #approvalsTable tbody td {
                padding: 14px 13px;
            }

            #actionModal .modal-dialog {
                margin: 12px;
            }

            #actionModal .modal-header {
                padding: 18px 17px 14px;
            }

            #actionModal .modal-body {
                padding: 18px 17px;
            }

            #actionModal .modal-footer {
                padding: 14px 17px 18px;
                flex-wrap: wrap;
            }

            #actionModal .modal-footer>div {
                display: flex;
                align-items: center;
                gap: 6px;
            }

            #actionModal .modal-footer>div .me-2 {
                margin-right: 0 !important;
            }
        }
    </style>
</head>

<body>
    <div id="overlay" onclick="closeMobile()"></div>
    @include('client.includes.sidebar')

    <div id="main">
        @include('client.includes.header')

        <div class="content">
            <div class="container-fluid py-4">

                <!-- Header Section -->
                <!-- Page Header -->
                <div class="page-header">
                    <div class="page-heading">
                        <div class="page-heading-icon">
                            <i class="bi bi-check2-square"></i>
                        </div>

                        <div>
                            <h4>Pending Approvals</h4>
                            <p>Review and process leave requests assigned to you.</p>
                        </div>
                    </div>

                    <div class="search-wrapper">
                        <i class="bi bi-search"></i>
                        <input type="text" id="searchInput" class="form-control search-input"
                            placeholder="Search employee, department, or leave type..." autocomplete="off">
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert"
                        style="border-radius: 10px; border-left: 5px solid #10b981;">
                        <i class="bi bi-check-circle-fill me-2" style="color: #10b981;"></i>
                        <strong>{{ session('success') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert"
                        style="border-radius: 10px; border-left: 5px solid #ef4444;">
                        <i class="bi bi-exclamation-triangle-fill me-2" style="color: #ef4444;"></i>
                        <strong>{{ session('error') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="dashboard-card mb-4 border-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="approvalsTable">
                            <thead class="bg-white">
                                <tr>
                                    <th class="text-muted text-uppercase fw-bold border-bottom-0">Applicant Details</th>
                                    <th class="text-muted text-uppercase fw-bold border-bottom-0">Leave Type</th>
                                    <th class="text-muted text-uppercase fw-bold border-bottom-0">Requested Dates</th>
                                    <th class="text-muted text-uppercase fw-bold border-bottom-0">Remarks</th>
                                    <th class="text-end text-muted text-uppercase fw-bold border-bottom-0">Action</th>
                                </tr>
                            </thead>
                            <tbody id="approvalsTableBody">
                                @forelse($leaves as $leave)
                                    <tr class="approval-row bg-white">

                                        <td>
                                            <div class="employee-info">
                                                <div class="avatar-circle">
                                                    {{ strtoupper(substr($leave->employee->employee_name ?? 'U', 0, 1)) }}
                                                </div>

                                                <div class="employee-details">
                                                    <span class="employee-name">
                                                        {{ $leave->employee->employee_name ?? 'Unknown Employee' }}
                                                    </span>

                                                    <span class="employee-department">
                                                        <i class="bi bi-diagram-3 me-1"></i>
                                                        {{ $leave->employee->department->department_name ?? 'N/A' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="leave-type-badge">
                                                <i class="bi bi-calendar-event me-2"></i>
                                                {{ $leave->leaveType->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="date-list">
                                                @forelse($leave->dates as $dt)
                                                    <span class="date-badge">
                                                        {{ \Carbon\Carbon::parse($dt->date)->format('d M, Y') }}
                                                    </span>
                                                @empty
                                                    <span class="text-muted small">No dates available</span>
                                                @endforelse
                                            </div>
                                        </td>


                                        <td>
                                            <span class="remarks-text" title="{{ $leave->remarks }}">
                                                {{ $leave->remarks ? \Illuminate\Support\Str::limit($leave->remarks, 55) : 'No remarks provided' }}
                                            </span>
                                        </td>


                                        <td class="text-end">
                                            @php
                                                $step = \App\Models\LeaveFlowStep::where('flow_id', $leave->flow_id)
                                                    ->where('step_order', $leave->current_step)
                                                    ->first();

                                                $isFinal = $step ? $step->is_final : 0;
                                            @endphp

                                            <button type="button" class="review-btn action-btn" data-id="{{ $leave->id }}"
                                                data-is-final="{{ $isFinal }}" data-bs-toggle="modal"
                                                data-bs-target="#actionModal">
                                                Review
                                                <i class="bi bi-arrow-right-short"></i>
                                            </button>
                                        </td>

                                    </tr>
                                @empty
                                    <tr id="defaultEmptyRow">
                                        <td colspan="5" class="text-center py-5 bg-white">
                                            <div>
                                                <i class="bi bi-inbox empty-state-icon"></i>
                                                <h6 class="empty-state-title">All caught up!</h6>
                                                <p class="empty-state-description">
                                                    You have no pending leave requests to review.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse

                                <!-- JS Search Empty State (Hidden by default) -->
                                <tr id="noResultsRow" style="display: none;">
                                    <td colspan="5" class="text-center py-5 bg-white">
                                        <div>
                                            <i class="bi bi-search empty-state-icon"></i>
                                            <h6 class="empty-state-title">No matches found</h6>
                                            <p class="empty-state-description">
                                                Try a different employee name, department, or leave type.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @if($leaves->hasPages())
                        <div class="px-4 pt-3 pb-2 border-top bg-white" style="border-radius: 0 0 12px 12px;">
                            {{ $leaves->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Action Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <form id="actionForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bi bi-shield-check text-primary me-2"></i>Process Leave Request
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark" style="font-size: 0.9rem;">Reviewer Comments
                                (Optional)</label>
                            <textarea name="remarks" class="form-control bg-light border-0 shadow-none" rows="4"
                                style="border-radius: 8px; font-size: 0.95rem; resize: none;"
                                placeholder="Enter reason for rejection or approval comments here..."></textarea>
                        </div>
                        <div class="text-muted" style="font-size: 0.85rem;">
                            <i class="bi bi-info-circle me-1"></i> These remarks will be visible in the approval logs.
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="submit" name="action" value="reject" class="btn btn-outline-danger bg-white">
                            <i class="bi bi-x-circle me-1"></i> Reject
                        </button>
                        <div>
                            <button type="button" class="btn fw-bold text-dark me-2 border-0 bg-transparent"
                                data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit" name="action" id="positiveActionBtn" value="forward"
                                class="btn btn-success">
                                <i class="bi bi-arrow-right me-1" id="positiveBtnIcon"></i> <span
                                    id="positiveBtnText">Approve & Forward</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('client.includes.footer_links')
    <script>
        $(document).ready(function () {

            // 1. Dynamic Action Button Setup
            $('.action-btn').click(function () {
                let id = $(this).data('id');
                let isFinal = $(this).data('is-final');

                $('#actionForm').attr('action', "{{ url('employee/leave-approvals') }}/" + id + "/action");

                if (isFinal == 1) {
                    $('#positiveActionBtn').val('approve');
                    $('#positiveBtnText').text('Final Approve');
                    $('#positiveBtnIcon').attr('class', 'bi bi-check-lg me-1');
                } else {
                    $('#positiveActionBtn').val('forward');
                    $('#positiveBtnText').text('Approve & Forward');
                    $('#positiveBtnIcon').attr('class', 'bi bi-arrow-right me-1');
                }
            });

            // 2. Real-time Search Functionality

            // Real-time Search
            $('#searchInput').on('input', function () {
                const value = $(this).val().toLowerCase().trim();
                let visibleRows = 0;

                $('#approvalsTableBody .approval-row').each(function () {
                    const text = $(this).text().toLowerCase();
                    const matches = text.includes(value);

                    $(this).toggle(matches);

                    if (matches) {
                        visibleRows++;
                    }
                });

                const hasApprovalRows =
                    $('#approvalsTableBody .approval-row').length > 0;

                $('#defaultEmptyRow').toggle(!hasApprovalRows);
                $('#noResultsRow').toggle(
                    hasApprovalRows && visibleRows === 0
                );
            });


        });
    </script>
</body>

</html>