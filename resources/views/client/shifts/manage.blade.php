<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Shifts - Dashboard</title>
    @include('client.includes.header_links')

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .custom-input {
            font-size: 0.9rem;
        }
    </style>
</head>

<body>

    <div id="overlay" onclick="closeMobile()"></div>

    @include('client.includes.sidebar')

    <div id="main">

        @include('client.includes.header')

        <div class="content">
            <div class="container-fluid py-2">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4 mt-2 shadow-sm" role="alert"
                        style="border-radius: 10px; border-left: 5px solid #10b981;">
                        <i class="bi bi-check-circle-fill me-2" style="color: #10b981;"></i>
                        <strong>{{ session('success') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="fw-bold m-0">Master Shifts</h4>
                        <p class="text-muted small mb-0">Manage hospital working shifts for the roster</p>
                    </div>
                    <button class="btn btn-primary px-4 py-2" style="border-radius:10px;" data-bs-toggle="modal"
                        data-bs-target="#addShiftModal">
                        <i class="bi bi-plus-circle me-2"></i> Add Shift
                    </button>
                </div>

                <!-- Filters -->
                <div class="dashboard-card mb-2 p-3">
                    <form method="GET" action="{{ route('shifts.manage') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="filter-label small fw-bold">Search Shift</label>
                                <input type="text" name="search" class="filter-control w-100"
                                    value="{{ request('search') }}" placeholder="Search by name (e.g. Morning)">
                            </div>
                            <div class="col-md-3">
                                <label class="filter-label small fw-bold">Status</label>
                                <select name="status" class="form-select filter-control" onchange="this.form.submit()">
                                    <option value="all">All Status</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-search-trigger bg-dark text-white"><i
                                            class="bi bi-search"></i></button>
                                    <a href="{{ route('shifts.manage') }}" class="btn btn-refresh border"><i
                                            class="bi bi-arrow-clockwise"></i></a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Data Table -->
                <div class="dashboard-card">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3" style="width:60px">#</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Shift Name</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Login Time</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Logout Time</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Grace Mins</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Type</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Status</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shifts as $shift)
                                    <tr>
                                        <td class="ps-4">
                                            {{ ($shifts->currentPage() - 1) * $shifts->perPage() + $loop->iteration }}</td>
                                        <td><span class="text-dark fw-bold">{{ $shift->shift_name }}</span></td>
                                        <td><span
                                                class="badge bg-light text-dark border">{{ \Carbon\Carbon::parse($shift->login_time)->format('h:i A') }}</span>
                                        </td>
                                        <td><span
                                                class="badge bg-light text-dark border">{{ \Carbon\Carbon::parse($shift->logout_time)->format('h:i A') }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted small">{{ $shift->grace_time }}m</span>
                                            @if($shift->grace_time_for_logout)
                                                <span class="badge bg-secondary ms-1" style="font-size:0.65rem;">+ Logout</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($shift->is_overnight)
                                                <span class="badge bg-danger text-white">Overnight</span>
                                            @else
                                                <span class="badge bg-info text-dark">Standard</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($shift->status == 1)
                                                <span class="badge-soft"
                                                    style="background:#ecfdf5; color:#10b981;">Active</span>
                                            @else
                                                <span class="badge-soft"
                                                    style="background:#fef2f2; color:#ef4444;">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-light btn-sm text-primary me-1 edit-btn"
                                                data-bs-toggle="modal" data-bs-target="#editShiftModal"
                                                data-id="{{ $shift->id }}" data-name="{{ $shift->shift_name }}"
                                                data-login="{{ \Carbon\Carbon::parse($shift->login_time)->format('H:i') }}"
                                                data-logout="{{ \Carbon\Carbon::parse($shift->logout_time)->format('H:i') }}"
                                                data-grace="{{ $shift->grace_time }}"
                                                data-grace_logout="{{ $shift->grace_time_for_logout }}"
                                                data-overnight="{{ $shift->is_overnight }}"
                                                data-status="{{ $shift->status }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <form action="{{ route('shifts.destroy', $shift->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to trash this shift?');">
                                                @csrf
                                                <button type="submit" class="btn btn-light btn-sm text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No Shifts Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 d-flex justify-content-end">
                        {{ $shifts->links('pagination::bootstrap-4') }}
                    </div>
                </div>

                <!-- ADD MODAL -->
                <div class="modal fade" id="addShiftModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content custom-modal">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">Create New Shift</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('shifts.store') }}" method="POST">
                                    @csrf
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Shift Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="shift_name" class="form-control custom-input"
                                                placeholder="e.g. Night Shift" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Status <span
                                                    class="text-danger">*</span></label>
                                            <select name="status" class="form-select custom-input" required>
                                                <option value="1" selected>Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Login Time <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="login_time"
                                                class="form-control custom-input timepicker" placeholder="Select Time"
                                                required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Logout Time <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="logout_time"
                                                class="form-control custom-input timepicker" placeholder="Select Time"
                                                required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Grace Time (Minutes)</label>
                                            <input type="number" name="grace_time" class="form-control custom-input"
                                                value="0" min="0" required>
                                        </div>

                                        <div class="col-md-6 d-flex align-items-center gap-4 mt-4">
                                            <div class="form-check">
                                                <input type="hidden" name="grace_time_for_logout" value="0">
                                                <input class="form-check-input" type="checkbox" id="add_grace_out"
                                                    onchange="$(this).prev('input').val(this.checked ? 1 : 0)">
                                                <label class="form-check-label small fw-bold" for="add_grace_out">Apply
                                                    Grace to Logout</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="hidden" name="is_overnight" value="0">
                                                <input class="form-check-input" type="checkbox" id="add_overnight"
                                                    onchange="$(this).prev('input').val(this.checked ? 1 : 0)">
                                                <label class="form-check-label small fw-bold text-danger"
                                                    for="add_overnight">Overnight Shift (Next Day)</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-top pt-3 text-end">
                                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">Save
                                            Shift</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EDIT MODAL -->
                <div class="modal fade" id="editShiftModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content custom-modal">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">Edit Shift</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editShiftForm" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Shift Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="shift_name" id="edit_shift_name"
                                                class="form-control custom-input" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Status <span
                                                    class="text-danger">*</span></label>
                                            <select name="status" id="edit_status" class="form-select custom-input"
                                                required>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Login Time <span
                                                    class="text-danger">*</span></label>
                                            <!-- Destroy and Re-init requires standard inputs -->
                                            <input type="text" name="login_time" id="edit_login_time"
                                                class="form-control custom-input edit-timepicker" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Logout Time <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="logout_time" id="edit_logout_time"
                                                class="form-control custom-input edit-timepicker" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Grace Time (Minutes)</label>
                                            <input type="number" name="grace_time" id="edit_grace_time"
                                                class="form-control custom-input" min="0" required>
                                        </div>

                                        <div class="col-md-6 d-flex align-items-center gap-4 mt-4">
                                            <div class="form-check">
                                                <input type="hidden" name="grace_time_for_logout"
                                                    id="edit_grace_logout_hidden" value="0">
                                                <input class="form-check-input" type="checkbox" id="edit_grace_out"
                                                    onchange="$(this).prev('input').val(this.checked ? 1 : 0)">
                                                <label class="form-check-label small fw-bold" for="edit_grace_out">Apply
                                                    Grace to Logout</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="hidden" name="is_overnight" id="edit_overnight_hidden"
                                                    value="0">
                                                <input class="form-check-input" type="checkbox" id="edit_overnight"
                                                    onchange="$(this).prev('input').val(this.checked ? 1 : 0)">
                                                <label class="form-check-label small fw-bold text-danger"
                                                    for="edit_overnight">Overnight Shift (Next Day)</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-top pt-3 text-end">
                                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">Update
                                            Shift</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @include('client.includes.footer_links')
    </div>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            let fpEditLogin, fpEditLogout;

            // Initialize Timepickers
            flatpickr(".timepicker", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                altInput: true,
                altFormat: "h:i K",
                time_24hr: false
            });

            fpEditLogin = flatpickr("#edit_login_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                altInput: true,
                altFormat: "h:i K",
                time_24hr: false
            });

            fpEditLogout = flatpickr("#edit_logout_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                altInput: true,
                altFormat: "h:i K",
                time_24hr: false
            });

            // Handle Edit Modal Population
            const editButtons = document.querySelectorAll('.edit-btn');
            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    document.getElementById('editShiftForm').action = '{{ url("client/shifts/update") }}/' + id;

                    document.getElementById('edit_shift_name').value = this.getAttribute('data-name');
                    document.getElementById('edit_status').value = this.getAttribute('data-status');
                    document.getElementById('edit_grace_time').value = this.getAttribute('data-grace');

                    // Set Timepickers
                    fpEditLogin.setDate(this.getAttribute('data-login'));
                    fpEditLogout.setDate(this.getAttribute('data-logout'));

                    // Set Checkboxes
                    const graceLogout = this.getAttribute('data-grace_logout') === '1';
                    document.getElementById('edit_grace_out').checked = graceLogout;
                    document.getElementById('edit_grace_logout_hidden').value = graceLogout ? '1' : '0';

                    const isOvernight = this.getAttribute('data-overnight') === '1';
                    document.getElementById('edit_overnight').checked = isOvernight;
                    document.getElementById('edit_overnight_hidden').value = isOvernight ? '1' : '0';
                });
            });
        });
    </script>
</body>

</html>