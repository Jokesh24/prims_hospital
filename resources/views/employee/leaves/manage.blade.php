<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Leaves - Dashboard</title>
    @include('employee.includes.header_links')

    <style>
        /* Align action buttons */
        .action-group {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 5px;
        }

        /* Ensure table rows look consistent */
        .table td {
            vertical-align: middle !important;
        }

        /* Badge styling */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
    </style>
</head>

<body>

    <div id="overlay" onclick="closeMobile()"></div>
    @include('employee.includes.sidebar')

    <div id="main">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show floating-alert" role="alert"
                style="position: fixed; top: 20px; right: 20px; z-index: 9999; border: none; border-left: 4px solid #059669; background: #fff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); color: #0f172a; font-weight: 500;">
                <i class="bi bi-check-circle-fill" style="color: #059669; margin-right: 8px;"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                    style="font-size: 0.8rem;"></button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert"
                style="position: fixed; top: 20px; right: 20px; z-index: 9999; border: none; border-left: 4px solid #dc2626; background: #fff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); color: #0f172a; font-weight: 500;">
                <i class="bi bi-exclamation-triangle-fill" style="color: #dc2626; margin-right: 8px;"></i>
                {{ session('error') ?? $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                    style="font-size: 0.8rem;"></button>
            </div>
        @endif

        @include('employee.includes.header')

        <div class="content">
            <div class="container-fluid py-2">


                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold m-0">Leave Management</h4>
                    <a href="{{ route('employee.apply_leave') }}" class="btn btn-primary px-4 py-2"
                        style="border-radius:10px;">
                        <i class="bi bi-calendar-plus me-2"></i> Apply New Leave
                    </a>
                </div>

                <!-- Search and Filters Section -->
                <div class="dashboard-card mb-2">
                    <form method="GET" action="{{ route('employee.leaves.manage') }}" class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="filter-label">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="filter-control w-100" placeholder="Search by subject...">
                        </div>
                        <div class="col-md-3">
                            <label class="filter-label">Status</label>
                            <select name="status" class="form-select filter-control">
                                <option value="">All Status</option>
                                @foreach(\App\Models\Leave::getStatusMap() as $key => $val)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $val }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                                <a href="{{ route('employee.leaves.manage') }}" class="btn btn-light border"><i
                                        class="bi bi-arrow-clockwise"></i></a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table Section -->
                <div class="dashboard-card">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-muted text-uppercase small fw-bold">Subject</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">From</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">To</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Status</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaves as $leave)
                                                                <tr>
                                                                    <td class="ps-4 fw-medium text-dark">{{ $leave->subject }}</td>
                                                                    <td class="small">
                                                                        {{ \Carbon\Carbon::parse($leave->leave_date_from)->format('d M Y') }}
                                                                    </td>
                                                                    <td class="small">
                                                                        {{ \Carbon\Carbon::parse($leave->to_leave_date)->format('d M Y') }}
                                                                    </td>
                                                                    <td>
                                                                        @php
                                                                            // Map your statuses to colors
                                                                            $colors = [
                                                                                1 => ['bg' => '#4070af', 'text' => '#fafafa'], // Applied
                                                                                2 => ['bg' => '#8e7201', 'text' => '#ffffff'], // In Progress
                                                                                3 => ['bg' => '#065f46', 'text' => '#ffffff'], // Accepted
                                                                                4 => ['bg' => '#ea8f3f', 'text' => '#ffffff'], // Cancelled
                                                                                5 => ['bg' => '#ee6b6b', 'text' => '#ffffff']  // Rejected
                                                                            ];
                                                                            $style = $colors[$leave->leave_status] ?? ['bg' => '#e5e7eb', 'text' => '#374151'];
                                                                        @endphp

                                                                        <span class="status-badge"
                                                                            style="background: {{ $style['bg'] }}; color: {{ $style['text'] }}; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                                                                            {{ $leave->status_label }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-end pe-4">
                                                                        <div class="action-group">
                                                                            <button type="button" class="btn btn-light btn-sm text-primary" onclick="viewLeaveDetails({{ json_encode([
                                        'subject' => $leave->subject,
                                        'from' => \Carbon\Carbon::parse($leave->leave_date_from)->format('d M Y'),
                                        'to' => \Carbon\Carbon::parse($leave->to_leave_date)->format('d M Y'),
                                        'reason' => $leave->reason,
                                        'status_id' => $leave->leave_status,
                                        'status_text' => $leave->status_label,
                                        'doc' => $leave->documents ? asset($leave->documents) : null
                                    ]) }})">
                                                                                <i class="bi bi-eye"></i>
                                                                            </button>

                                                                            @if($leave->leave_status == \App\Models\Leave::STATUS_APPLIED)
                                                                                <a href="{{ route('employee.leaves.edit', $leave->id) }}"
                                                                                    class="btn btn-light btn-sm text-secondary">
                                                                                    <i class="bi bi-pencil"></i>
                                                                                </a>
                                                                                <button type="button" class="btn btn-light btn-sm text-danger"
                                                                                    onclick="document.getElementById('cancel_id').value = {{ $leave->id }}; new bootstrap.Modal(document.getElementById('cancelModal')).show();">
                                                                                    <i class="bi bi-x-circle"></i>
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No leave requests found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        @include('employee.includes.footer_links')
    </div>

    <!-- View Leave Details Modal -->
    <div class="modal fade" id="viewLeaveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-eye me-2"></i>Leave Details</h5><button
                        type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="modalContent"></div>
            </div>
        </div>
    </div>

    <!-- Cancellation Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('employee.leaves.cancel') }}" method="POST" class="modal-content border-0 shadow">
                @csrf
                <input type="hidden" name="id" id="cancel_id">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Cancel Leave</h5><button
                        type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="fw-bold mb-2">Reason for Cancellation <span class="text-danger">*</span></label>
                    <textarea name="cancel_reason" class="form-control" rows="3" required
                        placeholder="Enter reason..."></textarea>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light"
                        data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-danger">Confirm
                        Cancellation</button></div>
            </form>
        </div>
    </div>

    <script>
        function viewLeaveDetails(data) {
            let statusColors = { 1: '#60a5fa', 2: '#f59e0b', 3: '#10b981', 4: '#6b7280', 5: '#ef4444' };
            let html = `
                <div class="row mb-3"><div class="col-sm-4 text-muted small">Subject</div><div class="col-sm-8 fw-bold">${data.subject}</div></div>
                <div class="row mb-3"><div class="col-sm-4 text-muted small">Duration</div><div class="col-sm-8">${data.from} to ${data.to}</div></div>
                <div class="row mb-3"><div class="col-sm-4 text-muted small">Reason</div><div class="col-sm-8">${data.reason}</div></div>
                <div class="row mb-3"><div class="col-sm-4 text-muted small">Status</div><div class="col-sm-8"><span class="badge" style="background:${statusColors[data.status_id]}">${data.status_text}</span></div></div>
                ${data.doc ? `<div class="row"><div class="col-sm-4 text-muted small">Document</div><div class="col-sm-8"><a href="${data.doc}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> View Attachment</a></div></div>` : ''}
            `;
            document.getElementById('modalContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('viewLeaveModal')).show();
        }
    </script>
</body>

</html>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Find all floating alerts
        let alerts = document.querySelectorAll('.floating-alert');

        if (alerts.length > 0) {
            // Wait 5 seconds, then fade them out
            setTimeout(function () {
                alerts.forEach(function (alertNode) {
                    // Check if Bootstrap JS is loaded and use its Alert API to dismiss smoothly
                    if (typeof bootstrap !== 'undefined') {
                        let bsAlert = new bootstrap.Alert(alertNode);
                        bsAlert.close();
                    } else {
                        // Fallback if bootstrap JS isn't initialized yet
                        alertNode.style.display = 'none';
                    }
                });
            }, 5000); // 5000ms = 5 seconds
        }
    });
</script>