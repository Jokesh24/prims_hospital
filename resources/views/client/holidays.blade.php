<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>School HRMS Dashboard</title>
    @include('client.includes.header_links')
    <style>
        .dept-checkbox-container {
            max-height: 180px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px 14px;
            background-color: #fafbfc;
        }

        .dept-checkbox-container::-webkit-scrollbar {
            width: 5px;
        }

        .dept-checkbox-container::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 4px;
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
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="fw-bold m-0">Holiday Management</h4>
                    </div>
                    <button class="btn btn-primary px-4 py-2" style="border-radius:10px;" data-bs-toggle="modal"
                        data-bs-target="#addHolidayModal">
                        <i class="bi bi-plus-circle me-2"></i> Add New Holiday
                    </button>
                </div>

                <div class="dashboard-card mb-3">
                    <!-- ADDED ID: filterForm -->
                    <form method="GET" action="{{ route('client.holidays') }}" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="filter-label">Search Holiday</label>
                                <div class="position-relative">
                                    <input type="text" name="search" value="{{ request('search') }}"
                                        class="filter-control w-100" placeholder="Enter holiday name...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="filter-label">Holiday Type</label>
                                <select name="type" class="form-select filter-control">
                                    <option value="All Types" {{ request('type') == 'All Types' ? 'selected' : '' }}>All
                                        Types</option>
                                    <option value="1" {{ request('type') == '1' ? 'selected' : '' }}>General Holiday
                                    </option>
                                    <option value="2" {{ request('type') == '2' ? 'selected' : '' }}>Public Holiday
                                    </option>
                                    <option value="3" {{ request('type') == '3' ? 'selected' : '' }}>Company Holiday
                                    </option>
                                    <option value="4" {{ request('type') == '4' ? 'selected' : '' }}>Optional Holiday
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="filter-label">Select Month</label>
                                <select name="month" class="form-select filter-control">
                                    <option value="All Months" {{ request('month') == 'All Months' ? 'selected' : '' }}>
                                        All Months</option>
                                    @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                        <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                                            {{ $month }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-search-trigger bg-primary text-white">
                                        <i class="bi bi-search"></i>
                                    </button>
                                    <a href="{{ route('client.holidays') }}" class="btn btn-refresh border"
                                        id="resetFilterBtn" title="Reset Filters">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="dashboard-card">
                    <div class="p-0">
                        <div class="table-responsive mb-3">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3" style="width:60px">Sl.No</th>
                                        <th class="py-3 text-muted text-uppercase small fw-bold">Holiday Name</th>
                                        <th class="py-3 text-muted text-uppercase small fw-bold">Date</th>
                                        <th class="py-3 text-muted text-uppercase small fw-bold">Day</th>
                                        <th class="py-3 text-muted text-uppercase small fw-bold">Departments</th>
                                        <th class="py-3 text-muted text-uppercase small fw-bold">Type</th>
                                        <th class="py-3 text-muted text-uppercase small fw-bold">Status</th>
                                        <th class="py-3 text-muted text-uppercase small fw-bold text-end pe-4">Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($holidays as $holiday)
                                        <tr>
                                            <td class="ps-4">
                                                {{ ($holidays->currentPage() - 1) * $holidays->perPage() + $loop->iteration }}
                                            </td>
                                            <td>
                                                <span class="text-dark fw-bold">{{ $holiday->title }}</span>
                                                @if($holiday->description)
                                                    <br><small class="text-muted"
                                                        style="font-size: 11px;">{{ Str::limit($holiday->description, 30) }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $holiday->date->format('d M Y') }}</td>
                                            <td>{{ $holiday->day }}</td>
                                            <td>
                                                @if($holiday->departments->isNotEmpty())
                                                    <div class="d-flex flex-wrap gap-1" style="max-width: 250px;">
                                                        @foreach($holiday->departments as $dept)
                                                            <span class="badge bg-secondary-subtle text-secondary border"
                                                                style="font-size: 11px;">
                                                                {{ $dept->department_name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted small">All Departments</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($holiday->type == 1)
                                                    <span class="badge-soft"
                                                        style="background:#f3f4f6; color:#4b5563;">General</span>
                                                @elseif($holiday->type == 2)
                                                    <span class="badge-soft"
                                                        style="background:#eef2ff; color:#6366f1;">Public</span>
                                                @elseif($holiday->type == 3)
                                                    <span class="badge-soft"
                                                        style="background:#f0fdf4; color:#10b981;">Company</span>
                                                @else
                                                    <span class="badge-soft"
                                                        style="background:#fff7ed; color:#ea580c;">Optional</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span
                                                    class="badge {{ $holiday->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $holiday->status == 1 ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <button class="btn btn-light btn-sm text-primary me-1 edit-btn"
                                                    data-bs-toggle="modal" data-bs-target="#editHolidayModal"
                                                    data-id="{{ $holiday->id }}" data-title="{{ $holiday->title }}"
                                                    data-date="{{ $holiday->date->format('Y-m-d') }}"
                                                    data-day="{{ $holiday->day }}" data-type="{{ $holiday->type }}"
                                                    data-description="{{ $holiday->description }}"
                                                    data-status="{{ $holiday->status }}"
                                                    data-departments='@json($holiday->departments->pluck("id"))'>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <!-- ADDED CLASS: delete-form -->
                                                <form action="{{ route('holidays.destroy', $holiday->id) }}" method="POST"
                                                    class="d-inline delete-form"
                                                    onsubmit="return confirm('Are you sure you want to delete this holiday?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-light btn-sm text-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">No Holidays Found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-4 px-3">
                            {{ $holidays->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Add Holiday Modal -->
    <div class="modal fade" id="addHolidayModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">New Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- ADDED CLASS: loader-form -->
                    <form action="{{ route('holidays.store') }}" method="POST" class="loader-form">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Holiday Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control custom-input"
                                placeholder="e.g. Christmas Day" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Description</label>
                            <textarea name="description" class="form-control custom-input" rows="2"
                                placeholder="Optional details..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="add_date" class="form-control custom-input" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Day <span class="text-danger">*</span></label>
                                <input type="text" name="day" id="add_day" class="form-control custom-input bg-light"
                                    placeholder="Auto-calculated from date" readonly required>
                            </div>
                        </div>

                        <!-- Departments Multi-select -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold m-0">Departments</label>
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="checkbox" id="add_select_all_depts">
                                    <label class="form-check-label small text-muted" for="add_select_all_depts">Select
                                        All</label>
                                </div>
                            </div>
                            <div class="dept-checkbox-container">
                                @forelse($departments as $dept)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input add-dept-checkbox" type="checkbox"
                                            name="department_ids[]" value="{{ $dept->id }}" id="add_dept_{{ $dept->id }}">
                                        <label class="form-check-label small" for="add_dept_{{ $dept->id }}">
                                            {{ $dept->department_name }}
                                        </label>
                                    </div>
                                @empty
                                    <span class="text-muted small">No departments configured under this client.</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select custom-input" required>
                                    <option selected disabled value="">Holiday Type</option>
                                    <option value="1">General Holiday</option>
                                    <option value="2">Public Holiday</option>
                                    <option value="3">Company Holiday</option>
                                    <option value="4">Optional Holiday</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Status <span
                                        class="text-danger">*</span></label>
                                <select name="status" class="form-select custom-input" required>
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary py-2 fw-bold w-100">Add Holiday</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Holiday Modal -->
    <div class="modal fade" id="editHolidayModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Edit Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- ADDED CLASS: loader-form -->
                    <form id="editHolidayForm" method="POST" class="loader-form">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Holiday Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="title" id="edit_title" class="form-control custom-input" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Description</label>
                            <textarea name="description" id="edit_description" class="form-control custom-input"
                                rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" id="edit_date" class="form-control custom-input"
                                    required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Day <span class="text-danger">*</span></label>
                                <input type="text" name="day" id="edit_day" class="form-control custom-input bg-light"
                                    placeholder="Auto-calculated from date" readonly required>
                            </div>
                        </div>

                        <!-- Departments Multi-select -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold m-0">Departments</label>
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="checkbox" id="edit_select_all_depts">
                                    <label class="form-check-label small text-muted" for="edit_select_all_depts">Select
                                        All</label>
                                </div>
                            </div>
                            <div class="dept-checkbox-container">
                                @forelse($departments as $dept)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input edit-dept-checkbox" type="checkbox"
                                            name="department_ids[]" value="{{ $dept->id }}" id="edit_dept_{{ $dept->id }}">
                                        <label class="form-check-label small" for="edit_dept_{{ $dept->id }}">
                                            {{ $dept->department_name }}
                                        </label>
                                    </div>
                                @empty
                                    <span class="text-muted small">No departments configured under this client.</span>
                                @endforelse
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Type <span class="text-danger">*</span></label>
                                <select name="type" id="edit_type" class="form-select custom-input" required>
                                    <option value="1">General Holiday</option>
                                    <option value="2">Public Holiday</option>
                                    <option value="3">Company Holiday</option>
                                    <option value="4">Optional Holiday</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Status <span
                                        class="text-danger">*</span></label>
                                <select name="status" id="edit_status" class="form-select custom-input" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary py-2 fw-bold w-100">Update Holiday</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('client.includes.footer_links')

    <!-- --- ADDED: Loader Triggers and UI Script --- -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /* --- Loader Logic Setup --- */
            const showLoader = () => {document.getElementById('globalLoader').style.display = 'flex';};

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

            // 3. Add and Edit Modal Form Submits
            document.querySelectorAll('.loader-form').forEach(form => {
                form.addEventListener('submit', showLoader);
            });

            // 4. Delete Form Trigger
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function () {
                    // Only triggers if the native confirm dialog returns true
                    showLoader();
                });
            });

            /* --- Functional UI Logic --- */
            function bindDayAutoSelect(dateInputId, daySelectId) {
                const dateElement = document.getElementById(dateInputId);
                const dayElement = document.getElementById(daySelectId);

                if (dateElement && dayElement) {
                    dateElement.addEventListener('change', function () {
                        if (this.value) {
                            const dateParts = this.value.split('-');
                            // Create date using local time to avoid timezone offset shifts
                            const dateObj = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
                            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            dayElement.value = days[dateObj.getDay()];
                        }
                    });
                }
            }

            bindDayAutoSelect('add_date', 'add_day');
            bindDayAutoSelect('edit_date', 'edit_day');

            /* Select All Helper */
            function bindSelectAll(masterId, targetClass) {
                const master = document.getElementById(masterId);
                if (master) {
                    master.addEventListener('change', function () {
                        const checkboxes = document.querySelectorAll('.' + targetClass);
                        checkboxes.forEach(cb => cb.checked = this.checked);
                    });
                }
            }

            bindSelectAll('add_select_all_depts', 'add-dept-checkbox');
            bindSelectAll('edit_select_all_depts', 'edit-dept-checkbox');

            /* Populate Edit Modal Data */
            const editButtons = document.querySelectorAll('.edit-btn');

            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');

                    let url = '{{ route("holidays.update", ":id") }}';
                    document.getElementById('editHolidayForm').action = url.replace(':id', id);

                    document.getElementById('edit_title').value = this.getAttribute('data-title');
                    document.getElementById('edit_date').value = this.getAttribute('data-date');
                    document.getElementById('edit_day').value = this.getAttribute('data-day');
                    document.getElementById('edit_type').value = this.getAttribute('data-type');
                    document.getElementById('edit_description').value = this.getAttribute('data-description');
                    document.getElementById('edit_status').value = this.getAttribute('data-status');

                    // Reset all edit checkboxes first
                    const editCheckboxes = document.querySelectorAll('.edit-dept-checkbox');
                    editCheckboxes.forEach(cb => cb.checked = false);

                    // Pre-check assigned departments
                    let selectedDepts = [];
                    try {
                        selectedDepts = JSON.parse(this.getAttribute('data-departments')) || [];
                    } catch (e) {
                        selectedDepts = [];
                    }

                    selectedDepts.forEach(deptId => {
                        const targetCheckbox = document.getElementById('edit_dept_' + deptId);
                        if (targetCheckbox) {
                            targetCheckbox.checked = true;
                        }
                    });

                    // Synchronize the "Select All" toggle state in Edit Modal
                    const selectAllMaster = document.getElementById('edit_select_all_depts');
                    if (selectAllMaster && editCheckboxes.length > 0) {
                        selectAllMaster.checked = (selectedDepts.length === editCheckboxes.length);
                    }
                });
            });
        });
    </script>
</body>

</html>