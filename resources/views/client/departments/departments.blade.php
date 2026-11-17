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

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="fw-bold m-0">Department Management</h4>
                    </div>
                    <button class="btn btn-primary px-4 py-2" style="border-radius:10px;" data-bs-toggle="modal"
                        data-bs-target="#addDepartmentModal">
                        <i class="bi bi-plus-circle me-2"></i> Add Department
                    </button>
                </div>

                <div class="dashboard-card mb-2">
                    <form method="GET" action="{{ route('client.departments') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="filter-label">Search Department</label>
                                <input type="text" class="filter-control w-100" placeholder="Search by name or code...">
                            </div>
                            <div class="col-md-2">
                                <label class="filter-label">Sort By Staff</label>
                                <select name="sort_staff" class="form-select filter-control"
                                    onchange="this.form.submit()">
                                    <option value="">Select Sort</option>
                                    <option value="desc" {{ request('sort_staff') == 'desc' ? 'selected' : '' }}>High to
                                        Low
                                    </option>
                                    <option value="asc" {{ request('sort_staff') == 'asc' ? 'selected' : '' }}>Low to High
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-search-trigger"><i
                                            class="bi bi-search"></i></button>
                                    <a href="{{ route('client.departments') }}" class="btn btn-refresh"><i
                                            class="bi bi-arrow-clockwise"></i></a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="dashboard-card">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3" style="width:60px">Pr.</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Department Name</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Dept. Code</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Total Staff</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Description</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Status</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departments as $department)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $department->priority }}</td>
                                        <td><span class="text-dark fw-medium">{{ $department->department_name }}</span></td>
                                        <td><span
                                                class="badge bg-light text-dark border">{{ $department->department_code ?? 'N/A' }}</span>
                                        </td>
                                        <td>{{ $department->employees_count }} Employees</td>
                                        <td>{{ Str::limit($department->description, 30) ?? '--' }}</td>
                                        <td>
                                            @if($department->status == 1)
                                                <span class="badge-soft"
                                                    style="background:#ecfdf5; color:#10b981;">Active</span>
                                            @else
                                                <span class="badge-soft"
                                                    style="background:#fef2f2; color:#ef4444;">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-light btn-sm text-primary me-1 edit-btn"
                                                data-bs-toggle="modal" data-bs-target="#editDepartmentModal"
                                                data-id="{{ $department->id }}"
                                                data-name="{{ $department->department_name }}"
                                                data-priority="{{ $department->priority }}"
                                                data-code="{{ $department->department_code }}"
                                                data-status="{{ $department->status }}"
                                                data-description="{{ $department->description }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <form action="{{ route('departments.destroy', $department->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to trash this department?');">
                                                @csrf
                                                <button type="submit" class="btn btn-light btn-sm text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No Departments Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        {{ $departments->links('pagination::bootstrap-4') }}
                    </div>
                </div>

                <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content custom-modal">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">New Department</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('departments.store') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-8 mb-3">
                                            <label class="form-label small fw-bold">Department Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="department_name" class="form-control custom-input"
                                                placeholder="e.g. Human Resources" required>
                                        </div>
                                        <div class="col-4 mb-3">
                                            <label class="form-label small fw-bold">Priority <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="priority" class="form-control custom-input"
                                                value="{{ $nextPriority }}" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label class="form-label small fw-bold">Status <span
                                                    class="text-danger">*</span></label>
                                            <select name="status" class="form-select custom-input" required>
                                                <option value="1" selected>Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label small fw-bold">Dept. Code</label>
                                            <input type="text" name="department_code" class="form-control custom-input"
                                                placeholder="e.g. HR-01">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Description</label>
                                        <textarea name="description" class="form-control custom-input" rows="2"
                                            placeholder="Brief department overview..."></textarea>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary py-2 fw-bold w-100">Create
                                            Department</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content custom-modal">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">Edit Department</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editDepartmentForm" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-8 mb-3">
                                            <label class="form-label small fw-bold">Department Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="department_name" id="edit_department_name"
                                                class="form-control custom-input" required>
                                        </div>
                                        <div class="col-4 mb-3">
                                            <label class="form-label small fw-bold">Priority <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="priority" id="edit_priority"
                                                class="form-control custom-input" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label class="form-label small fw-bold">Status <span
                                                    class="text-danger">*</span></label>
                                            <select name="status" id="edit_status" class="form-select custom-input"
                                                required>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label class="form-label small fw-bold">Dept. Code</label>
                                            <input type="text" name="department_code" id="edit_department_code"
                                                class="form-control custom-input">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Description</label>
                                        <textarea name="description" id="edit_description"
                                            class="form-control custom-input" rows="2"></textarea>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary py-2 fw-bold w-100">Update
                                            Department</button>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editButtons = document.querySelectorAll('.edit-btn');

            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    // Extract data attributes
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const priority = this.getAttribute('data-priority');
                    const code = this.getAttribute('data-code');
                    const status = this.getAttribute('data-status');
                    const description = this.getAttribute('data-description');

                    // Set form action dynamically based on ID
                    const form = document.getElementById('editDepartmentForm');
                    let url = '{{ route("departments.update", ":id") }}';
                    form.action = url.replace(':id', id);

                    // Populate fields
                    document.getElementById('edit_department_name').value = name;
                    document.getElementById('edit_priority').value = priority;
                    document.getElementById('edit_department_code').value = code || '';
                    document.getElementById('edit_status').value = status;
                    document.getElementById('edit_description').value = description || '';
                });
            });
        });
    </script>
</body>

</html>