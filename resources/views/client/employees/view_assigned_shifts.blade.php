<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Assigned Shifts - Dashboard</title>
    @include('client.includes.header_links')
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .custom-input { font-size: 0.9rem; }
    </style>
</head>
<body>

    <div id="overlay" onclick="closeMobile()"></div>
    @include('client.includes.sidebar')

    <div id="main">
        @include('client.includes.header')

        <div class="content">
            <div class="container-fluid py-2">

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold m-0">Employee Shift Roster</h4>
                    <a href="{{ route('client.employee_shifts') }}" class="btn btn-primary px-4 py-2" style="border-radius:10px;">
                        <i class="bi bi-plus-circle me-2"></i> Assign New Shifts
                    </a>
                </div>

                <!-- Filters -->
                <div class="dashboard-card mb-3">
                    <form method="GET" action="{{ route('client.employee_shifts.view') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="filter-label fw-bold small">Search Employee</label>
                                <input type="text" name="search" class="form-control custom-input" value="{{ request('search') }}" placeholder="Name or ID...">
                            </div>
                            <div class="col-md-2">
                                <label class="filter-label fw-bold small">Department</label>
                                <select name="department_id" class="form-select custom-input" id="filter_department" onchange="this.form.submit()">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="filter-label fw-bold small">Sub Department</label>
                                <select name="sub_department_id" class="form-select custom-input" onchange="this.form.submit()">
                                    <option value="">All Sub Departments</option>
                                    @foreach($subDepartments as $subDept)
                                        <!-- Hide sub depts that don't belong to the selected department -->
                                        @if(!request('department_id') || request('department_id') == $subDept->department_id)
                                            <option value="{{ $subDept->id }}" {{ request('sub_department_id') == $subDept->id ? 'selected' : '' }}>{{ $subDept->sub_department_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="filter-label fw-bold small">Start Date</label>
                                <input type="text" name="start_date" class="form-control custom-input datepicker" value="{{ request('start_date') }}" placeholder="YYYY-MM-DD">
                            </div>
                            <div class="col-md-2">
                                <label class="filter-label fw-bold small">End Date</label>
                                <input type="text" name="end_date" class="form-control custom-input datepicker" value="{{ request('end_date') }}" placeholder="YYYY-MM-DD">
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Search</button>
                                    <a href="{{ route('client.employee_shifts.view') }}" class="btn btn-light border"><i class="bi bi-arrow-clockwise"></i></a>
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
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Employee</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Department Info</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Shift Details</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Timeline</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Total Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assignedShifts as $assignment)
                                    <tr>
                                        <td class="ps-4">
                                            {{ ($assignedShifts->currentPage() - 1) * $assignedShifts->perPage() + $loop->iteration }}
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $assignment->employee->employee_name ?? 'N/A' }}</div>
                                            <div class="small text-muted">{{ $assignment->employee->employee_id ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            <div><span class="badge bg-light text-dark border">{{ $assignment->employee->department->department_name ?? '--' }}</span></div>
                                            @if($assignment->employee->subDepartment)
                                                <div class="mt-1"><span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">{{ $assignment->employee->subDepartment->sub_department_name }}</span></div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($assignment->shift)
                                                <div class="fw-bold text-primary">{{ $assignment->shift->shift_name }}</div>
                                                <div class="small text-muted">
                                                    {{ \Carbon\Carbon::parse($assignment->shift->login_time)->format('h:i A') }} - 
                                                    {{ \Carbon\Carbon::parse($assignment->shift->logout_time)->format('h:i A') }}
                                                    @if($assignment->shift->is_overnight)
                                                        <span class="badge bg-danger text-white ms-1" style="font-size: 0.6rem;">Next Day</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-danger small">Shift Deleted</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small">
                                                <strong>From:</strong> <span class="text-success">{{ \Carbon\Carbon::parse($assignment->start_date)->format('d M, Y') }}</span><br>
                                                <strong>To:</strong> <span class="text-danger">{{ \Carbon\Carbon::parse($assignment->end_date)->format('d M, Y') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark">
                                                {{ \Carbon\Carbon::parse($assignment->start_date)->diffInDays(\Carbon\Carbon::parse($assignment->end_date)) + 1 }} Days
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                                            No shift assignments found for the selected filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 d-flex justify-content-end">
                        {{ $assignedShifts->links('pagination::bootstrap-4') }}
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
            flatpickr(".datepicker", { 
                dateFormat: "Y-m-d",
                allowInput: true
            });
        });
    </script>
</body>
</html>