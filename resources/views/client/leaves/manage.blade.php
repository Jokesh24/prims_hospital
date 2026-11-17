<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Leaves - Dashboard</title>
    @include('client.includes.header_links')
    <style>
        .action-group {
            display: flex;
            gap: 5px;
            justify-content: flex-end;
        }

        .table td {
            vertical-align: middle !important;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
            color: #fff !important;
            display: inline-block;
        }
    </style>
</head>

<body>
    @include('client.includes.sidebar')
    <div id="main">
        @include('client.includes.header')
        <div class="content">
            <div class="container-fluid py-2">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4 mt-2 shadow-sm"
                        style="border-radius: 10px; border-left: 5px solid #10b981;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold m-0">Leave Management</h4>
                </div>

                <div class="dashboard-card mb-2">
                    <form method="GET" action="{{ route('client.leaves.manage') }}" class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="filter-label">Search Subject</label>
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
                                <a href="{{ route('client.leaves.manage') }}" class="btn btn-light border"><i
                                        class="bi bi-arrow-clockwise"></i></a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="dashboard-card">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-muted text-uppercase small fw-bold">SL. No</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Employee</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Subject</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Duration</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Status</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaves as $index => $leave)
                                    <tr>
                                        <td class="ps-4 text-muted small">{{ $leaves->firstItem() + $index }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $leave->employee->employee_name ?? 'N/A' }}
                                            </div>
                                            <small class="text-muted">#{{ $leave->employee->employee_id ?? '---' }}</small>
                                        </td>
                                        <td class="fw-medium text-dark">{{ $leave->subject }}</td>
                                        <td class="small">
                                            {{ \Carbon\Carbon::parse($leave->leave_date_from)->format('d M') }} -
                                            {{ \Carbon\Carbon::parse($leave->to_leave_date)->format('d M Y') }}</td>
                                        <td>
                                            @php
                                                $map = [1 => '#0ea5e9', 2 => '#f59e0b', 3 => '#10b981', 4 => '#6b7280', 5 => '#ef4444'];
                                                $c = $map[$leave->leave_status] ?? '#6b7280';
                                            @endphp
                                            <span class="status-badge"
                                                style="background: {{ $c }};">{{ $leave->status_label }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="action-group">
                                                <button type="button" class="btn btn-light btn-sm text-primary"
                                                    data-bs-toggle="modal" data-bs-target="#viewLeaveModal"
                                                    data-subject="{{ $leave->subject }}"
                                                    data-from="{{ \Carbon\Carbon::parse($leave->leave_date_from)->format('d M Y') }}"
                                                    data-to="{{ \Carbon\Carbon::parse($leave->to_leave_date)->format('d M Y') }}"
                                                    data-reason="{{ $leave->reason }}"
                                                    data-cancel_reason="{{ $leave->cancel_reason ?? '' }}"
                                                    data-status="{{ $leave->status_label }}"
                                                    data-status_id="{{ $leave->leave_status }}"
                                                    data-doc="{{ $leave->documents ? asset($leave->documents) : '' }}">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                @if($leave->leave_status == 1)
                                                    <form action="{{ route('client.leaves.updateStatus') }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $leave->id }}">
                                                        <button name="status" value="2"
                                                            class="btn btn-light btn-sm text-warning" title="In Progress"><i
                                                                class="bi bi-clock"></i></button>
                                                        <button name="status" value="3"
                                                            class="btn btn-light btn-sm text-success" title="Accept"><i
                                                                class="bi bi-check-circle"></i></button>
                                                    </form>
                                                    <button type="button" class="btn btn-light btn-sm text-danger"
                                                        onclick="document.getElementById('reject_id').value={{ $leave->id }}; new bootstrap.Modal(document.getElementById('rejectModal')).show();">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No leaves found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @include('client.includes.footer_links')
    </div>

    <div class="modal fade" id="viewLeaveModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Leave Details</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalContent"></div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('client.leaves.updateStatus') }}" method="POST" class="modal-content">@csrf <input
                    type="hidden" name="id" id="reject_id"><input type="hidden" name="status" value="5">
                <div class="modal-header bg-danger text-white">
                    <h5>Reject Leave</h5>
                </div>
                <div class="modal-body"><textarea name="cancel_reason" class="form-control" required
                        placeholder="Rejection reason..."></textarea></div>
                <div class="modal-footer"><button type="submit" class="btn btn-danger">Confirm Reject</button></div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('viewLeaveModal').addEventListener('show.bs.modal', function (e) {
            let b = e.relatedTarget;
            let sId = parseInt(b.getAttribute('data-status_id'));
            let reasonLabel = (sId === 4 || sId === 5) ? "Reason for Cancellation/Rejection" : "Reason";
            let reasonContent = (sId === 4 || sId === 5) ? b.getAttribute('data-cancel_reason') : b.getAttribute('data-reason');

            document.getElementById('modalContent').innerHTML = `
                <p><b>Subject:</b> ${b.getAttribute('data-subject')}</p>
                <p><b>Duration:</b> ${b.getAttribute('data-from')} - ${b.getAttribute('data-to')}</p>
                <p><b>${reasonLabel}:</b> ${reasonContent || 'N/A'}</p>
                <p><b>Status:</b> ${b.getAttribute('data-status')}</p>
                ${b.getAttribute('data-doc') ? `<a href="${b.getAttribute('data-doc')}" target="_blank" class="btn btn-sm btn-primary">View Document</a>` : ''}
            `;
        });
    </script>
</body>

</html>