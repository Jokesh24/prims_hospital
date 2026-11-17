<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>School HRMS Dashboard</title>
    @include('client.includes.header_links')
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

                @if(session('import_errors') && count(session('import_errors')) > 0)
                    <div class="alert alert-warning alert-dismissible fade show mb-4 mt-2 shadow-sm" role="alert" style="border-radius: 10px; border-left: 5px solid #f59e0b;">
                        <h6 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2" style="color: #f59e0b;"></i>Import Issues Detected:</h6>
                        <ul class="mb-0 mt-2 text-dark">
                            @foreach(session('import_errors') as $importError)
                                <li>{{ $importError }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>
                        <h4 class="fw-bold m-0">Employee Directory</h4>
                    </div>

                    <div class="d-flex align-items-center gap-2">

                        <a href="{{ route('downloadTemplate') }}" class="btn btn-success px-4 py-2"
                            style="border-radius:10px;">
                            <i class="bi bi-download me-2"></i> Template
                        </a>

                        <form action="{{ route('importEmployees') }}" method="POST" enctype="multipart/form-data"
                            class="d-flex align-items-center gap-2 mb-0">
                            @csrf
                            <input type="file" name="file" class="form-control" accept=".csv" required
                                style="max-width:250px;">
                            <button type="submit" class="btn btn-warning px-4 py-2" style="border-radius:10px;">
                                <i class="bi bi-upload me-2"></i> Import
                            </button>
                        </form>

                        <a href="{{ route('employees.create') }}" class="btn btn-primary px-4 py-2"
                            style="border-radius:10px;">
                            <i class="bi bi-person-plus me-2"></i> Add Employee
                        </a>

                    </div>

                </div>

                <!-- FILTER CARD WITH EXCEL EXPORT BUTTON -->
                <div class="dashboard-card mb-2">
                    <form method="GET" action="{{ route('client.employees') }}" class="row g-3 align-items-end"
                        id="employeeFilterForm">
                        <div class="col-md-4">
                            <label class="filter-label">Search Employee</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="filter-control w-100" placeholder="Search by name, email or ID...">
                        </div>
                        <div class="col-md-3">
                            <label class="filter-label">Department</label>
                            <select name="department_id" class="form-select filter-control">
                                <option value="" selected>All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="filter-label">Status</label>
                            <select name="status" class="form-select filter-control">
                                <option value="all" selected>All Status</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <!-- 1. Search Trigger -->
                                <button type="submit" class="btn btn-search-trigger bg-primary text-white"
                                    title="Search">
                                    <i class="bi bi-search"></i>
                                </button>

                                <!-- 2. Reset Filters -->
                                <a href="{{ route('client.employees') }}" class="btn btn-refresh bg-light border"
                                    title="Reset Filters">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>

                                <!-- 3. Export to Excel Button (Respecting active filters) -->
                                <button type="submit" formaction="{{ route('employees.export.excel') }}"
                                    class="btn btn-success flex-fill px-3 py-2 fw-semibold"
                                    title="Export Filtered Employees to Excel">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="dashboard-card">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3" style="width:60px">Sl.No</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">EMP ID</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Employee Name</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Email</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Join Date</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Designation</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Status</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td class="ps-4">
                                            {{ ($employees->currentPage() - 1) * $employees->perPage() + $loop->iteration }}
                                        </td>
                                        <td class="text-muted small">#{{ $employee->employee_id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($employee->photo)
                                                    <img src="{{ asset('public/' . $employee->photo) }}" class="me-2"
                                                        style="width:32px; height:32px; border-radius:50%; object-fit:cover;">
                                                @else
                                                    <div class="avatar-circle me-2 bg-primary text-white"
                                                        style="width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size: 11px; font-weight: bold;">
                                                        {{ strtoupper(substr($employee->employee_name, 0, 2)) }}
                                                    </div>
                                                @endif
                                                <span class="text-dark fw-medium">{{ $employee->employee_name }}</span>
                                            </div>
                                        </td>

                                        <td class="small">{{ $employee->email ?? '--' }}</td>
                                        <td class="small">{{ optional($employee->joining_date)->format('d M Y') ?? '--' }}
                                        </td>
                                        <td><span class="small">{{ $employee->designationInfo->designation_name ?? '--' }}</span></td>
                                        <td>
                                            @if($employee->status == 1)
                                                <span class="badge-soft"
                                                    style="background:#ecfdf5; color:#10b981;">Active</span>
                                            @else
                                                <span class="badge-soft"
                                                    style="background:#fef2f2; color:#ef4444;">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('employees.show', $employee->id) }}"
                                                class="btn btn-light btn-sm text-primary me-1" title="View Profile"><i
                                                    class="bi bi-eye"></i></a>
                                            <a href="{{ route('employees.edit', $employee->id) }}"
                                                class="btn btn-light btn-sm text-secondary me-1" title="Edit Employee"><i
                                                    class="bi bi-pencil"></i></a>

                                            <form action="{{ route('employees.destroy', $employee->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to trash this employee?');">
                                                @csrf
                                                <button type="submit" class="btn btn-light btn-sm text-danger"
                                                    title="Trash Employee">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No Employees Found matching your
                                            criteria.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        {{ $employees->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>
        </div>

        @include('client.includes.footer_links')

</body>

</html>