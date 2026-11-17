<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Employee Login Access - HRMS</title>
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="fw-bold m-0">Employee Login Access</h4>
                        <p class="text-muted small mb-0">Control sidebar visibility for staff logins</p>
                    </div>
                    <a href="{{ route('login_access.create') }}" class="btn btn-primary px-4 py-2"
                        style="border-radius:10px;">
                        <i class="bi bi-shield-lock me-2"></i> Grant Access
                    </a>
                </div>

                <!-- Filters -->
                <div class="dashboard-card mb-3 p-3 shadow-sm border-0" style="border-radius:10px;">
                    <form method="GET" action="{{ route('client.login_access') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="filter-label small fw-bold text-secondary mb-1">Search Employee</label>
                            <input type="text" name="search" class="form-control bg-light"
                                value="{{ request('search') }}" placeholder="Search name or ID...">
                        </div>
                        <div class="col-md-4">
                            <label class="filter-label small fw-bold text-secondary mb-1">Department</label>
                            <select name="department_id" class="form-select bg-light" onchange="this.form.submit()">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark w-100 fw-bold shadow-sm"
                                style="border-radius:8px;">
                                <i class="bi bi-funnel me-2"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('client.login_access') }}" class="btn btn-outline-secondary w-100 fw-bold"
                                style="border-radius:8px;">Reset</a>
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
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Department</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Designation</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Allowed Modules</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Status</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accessLists as $access)
                                    <tr>
                                        <td class="ps-4">
                                            {{ ($accessLists->currentPage() - 1) * $accessLists->perPage() + $loop->iteration }}
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $access->employee->employee_name ?? '--' }}
                                            </div>
                                            <div class="small text-muted">{{ $access->employee->employee_id ?? '--' }}</div>
                                        </td>
                                        <td><span
                                                class="badge bg-light text-dark border">{{ $access->employee->department->department_name ?? '--' }}</span>
                                        </td>
                                        <td><span
                                                class="text-muted small">{{ $access->employee->designationInfo->designation_name ?? '--' }}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-primary rounded-pill">{{ count($access->accessible_sections ?? []) }}
                                                Sections Granted</span>
                                        </td>
                                        <td>
                                            @if($access->status == 1)
                                                <span class="badge-soft"
                                                    style="background:#ecfdf5; color:#10b981;">Active</span>
                                            @else
                                                <span class="badge-soft"
                                                    style="background:#fef2f2; color:#ef4444;">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('login_access.edit', $access->id) }}"
                                                class="btn btn-light btn-sm text-primary me-1">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('login_access.destroy', $access->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Revoke login access for this employee?');">
                                                @csrf
                                                <button type="submit" class="btn btn-light btn-sm text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">No login access configurations
                                            found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 d-flex justify-content-end">
                        {{ $accessLists->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('client.includes.footer_links')
</body>

</html>