<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>School HRMS Dashboard - Sub Departments</title>
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
                        <h4 class="fw-bold m-0">Sub Department Management</h4>
                    </div>
                    <button class="btn btn-primary px-4 py-2" style="border-radius:10px;" data-bs-toggle="modal"
                        data-bs-target="#addSubDepartmentModal">
                        <i class="bi bi-plus-circle me-2"></i> Add Sub Department
                    </button>
                </div>

                <div class="dashboard-card mb-2">
                    <form method="GET" action="{{ route('client.sub_departments') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="filter-label">Search Sub Department</label>
                                <input type="text" name="search" class="filter-control w-100"
                                    placeholder="Search by name..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="filter-label">Filter by Department</label>
                                <select name="department_id" class="form-select filter-control"
                                    onchange="this.form.submit()">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-search-trigger"><i
                                            class="bi bi-search"></i></button>
                                    <a href="{{ route('client.sub_departments') }}" class="btn btn-refresh"><i
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
                                    <th class="ps-4 py-3" style="width:60px">#</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Parent Department</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Sub Department Name</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Status</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subDepartments as $index => $subDept)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">{{ $subDepartments->firstItem() + $index }}
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $subDept->department->department_name ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td><span class="text-dark fw-medium">{{ $subDept->sub_department_name }}</span>
                                        </td>
                                        <td>
                                            @if($subDept->status == 1)
                                                <span class="badge-soft"
                                                    style="background:#ecfdf5; color:#10b981;">Active</span>
                                            @else
                                                <span class="badge-soft"
                                                    style="background:#fef2f2; color:#ef4444;">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-light btn-sm text-primary me-1 edit-btn"
                                                data-bs-toggle="modal" data-bs-target="#editSubDepartmentModal"
                                                data-id="{{ $subDept->id }}" data-dept-id="{{ $subDept->department_id }}"
                                                data-name="{{ $subDept->sub_department_name }}"
                                                data-status="{{ $subDept->status }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <form action="{{ route('sub_departments.destroy', $subDept->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to trash this sub-department?');">
                                                @csrf
                                                <button type="submit" class="btn btn-light btn-sm text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No Sub Departments Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        {{ $subDepartments->links('pagination::bootstrap-4') }}
                    </div>
                </div>

                <!-- ADD MODAL -->
                <div class="modal fade" id="addSubDepartmentModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content custom-modal">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">Add Sub Department(s)</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('sub_departments.store') }}" method="POST">
                                    @csrf

                                    <div class="mb-4 pb-3 border-bottom">
                                        <label class="form-label small fw-bold">Select Parent Department <span
                                                class="text-danger">*</span></label>
                                        <select name="department_id" class="form-select custom-input" required>
                                            <option value="">-- Choose Department --</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="dynamic-sub-departments">
                                        <div class="row align-items-end mb-3 sub-dept-row">
                                            <div class="col-md-7 mb-2 mb-md-0">
                                                <label class="form-label small fw-bold">Sub Department Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="sub_departments[0][name]"
                                                    class="form-control custom-input" placeholder="e.g. IT Support"
                                                    required>
                                            </div>
                                            <div class="col-md-4 mb-2 mb-md-0">
                                                <label class="form-label small fw-bold">Status <span
                                                        class="text-danger">*</span></label>
                                                <select name="sub_departments[0][status]"
                                                    class="form-select custom-input" required>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">Inactive</option>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button"
                                                    class="btn btn-success btn-sm w-100 py-2 add-row-btn"
                                                    title="Add More">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-2">
                                        <button type="submit" class="btn btn-primary py-2 fw-bold w-100">Create
                                            Sub Departments</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EDIT MODAL -->
                <div class="modal fade" id="editSubDepartmentModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content custom-modal">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">Edit Sub Department</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editSubDepartmentForm" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Parent Department <span
                                                class="text-danger">*</span></label>
                                        <select name="department_id" id="edit_department_id"
                                            class="form-select custom-input" required>
                                            <option value="">-- Choose Department --</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Sub Department Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="sub_department_name" id="edit_sub_department_name"
                                            class="form-control custom-input" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Status <span
                                                class="text-danger">*</span></label>
                                        <select name="status" id="edit_status" class="form-select custom-input"
                                            required>
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary py-2 fw-bold w-100">Update
                                            Sub Department</button>
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

            // Edit Button Logic
            const editButtons = document.querySelectorAll('.edit-btn');
            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const deptId = this.getAttribute('data-dept-id');
                    const name = this.getAttribute('data-name');
                    const status = this.getAttribute('data-status');

                    const form = document.getElementById('editSubDepartmentForm');
                    let url = '{{ route("sub_departments.update", ":id") }}';
                    form.action = url.replace(':id', id);

                    document.getElementById('edit_department_id').value = deptId;
                    document.getElementById('edit_sub_department_name').value = name;
                    document.getElementById('edit_status').value = status;
                });
            });

            // Dynamic Rows Logic for Multiple Additions
            let rowIndex = 1;
            document.addEventListener('click', function (e) {
                // Handle Add Row
                if (e.target.closest('.add-row-btn')) {
                    const container = document.getElementById('dynamic-sub-departments');
                    const newRow = document.createElement('div');
                    newRow.className = 'row align-items-end mb-3 sub-dept-row';
                    newRow.innerHTML = `
                        <div class="col-md-7 mb-2 mb-md-0">
                            <input type="text" name="sub_departments[${rowIndex}][name]" class="form-control custom-input"
                                placeholder="e.g. IT Support" required>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <select name="sub_departments[${rowIndex}][status]" class="form-select custom-input" required>
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm w-100 py-2 remove-row-btn" title="Remove">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                    container.appendChild(newRow);
                    rowIndex++;
                }

                // Handle Remove Row
                if (e.target.closest('.remove-row-btn')) {
                    e.target.closest('.sub-dept-row').remove();
                }
            });
        });
    </script>
</body>

</html>