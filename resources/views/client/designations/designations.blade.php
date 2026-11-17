<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Designations - Dashboard</title>
    @include('client.includes.header_links')

    <style>
        .custom-input {
            font-size: 0.9rem;
        }

        /* Fix modal over modal backdrop issues */
        .modal-stack {
            z-index: 1060 !important;
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

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="fw-bold m-0">Designations</h4>
                    </div>
                    <button class="btn btn-primary px-4 py-2" style="border-radius:10px;" data-bs-toggle="modal"
                        data-bs-target="#addDesignationModal">
                        <i class="bi bi-plus-circle me-2"></i> Add Designation
                    </button>
                </div>

                <!-- Filters -->
                <div class="dashboard-card mb-2">
                    <form method="GET" action="{{ route('client.designations') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="filter-label">Search Designation</label>
                                <input type="text" name="search" class="filter-control w-100"
                                    value="{{ request('search') }}" placeholder="Search by name...">
                            </div>
                            <div class="col-md-3">
                                <label class="filter-label">Department</label>
                                <select name="department_id" class="form-select filter-control"
                                    onchange="this.form.submit()">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="filter-label">Sub Department</label>
                                <select name="sub_department_id" class="form-select filter-control"
                                    onchange="this.form.submit()">
                                    <option value="">All Sub Departments</option>
                                    @if(isset($subDepartments))
                                        @foreach($subDepartments as $subDept)
                                            <option value="{{ $subDept->id }}" {{ request('sub_department_id') == $subDept->id ? 'selected' : '' }}>{{ $subDept->sub_department_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-search-trigger"><i
                                            class="bi bi-search"></i></button>
                                    <a href="{{ route('client.designations') }}" class="btn btn-refresh"><i
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
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Designation</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Department</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Sub Department</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Status</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($designations as $desig)
                                    <tr>
                                        <td class="ps-4">
                                            {{ ($designations->currentPage() - 1) * $designations->perPage() + $loop->iteration }}
                                        </td>
                                        <td><span class="text-dark fw-bold">{{ $desig->designation_name }}</span></td>
                                        <td><span
                                                class="badge bg-light text-dark border">{{ $desig->department->department_name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if($desig->subDepartment)
                                                <span
                                                    class="badge bg-light text-dark border">{{ $desig->subDepartment->sub_department_name }}</span>
                                            @else
                                                <span class="text-muted small">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($desig->status == 1)
                                                <span class="badge-soft"
                                                    style="background:#ecfdf5; color:#10b981;">Active</span>
                                            @else
                                                <span class="badge-soft"
                                                    style="background:#fef2f2; color:#ef4444;">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-light btn-sm text-primary me-1 edit-btn"
                                                data-bs-toggle="modal" data-bs-target="#editDesignationModal"
                                                data-id="{{ $desig->id }}" data-department_id="{{ $desig->department_id }}"
                                                data-sub_department_id="{{ $desig->sub_department_id }}"
                                                data-name="{{ $desig->designation_name }}"
                                                data-status="{{ $desig->status }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <form action="{{ route('designations.destroy', $desig->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to trash this designation?');">
                                                @csrf
                                                <button type="submit" class="btn btn-light btn-sm text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No Designations Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 d-flex justify-content-end">
                        {{ $designations->links('pagination::bootstrap-4') }}
                    </div>
                </div>

                <!-- ADD MODAL -->
                <div class="modal fade" id="addDesignationModal" aria-hidden="true" data-bs-focus="false">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content custom-modal">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">New Designation</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="{{ route('designations.store') }}" method="POST">
                                    @csrf

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Department <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <select name="department_id" id="add_department_id"
                                                    class="form-select custom-input" required>
                                                    <option value="" selected disabled>Select Department</option>
                                                    @foreach($departments as $dept)
                                                        <option value="{{ $dept->id }}">{{ $dept->department_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#quickAddDeptModal">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Sub Department (Optional)</label>
                                            <div class="input-group">
                                                <select name="sub_department_id" id="add_sub_department_id"
                                                    class="form-select custom-input">
                                                    <option value="">Select Sub Department</option>
                                                </select>
                                                <button type="button" class="btn btn-outline-primary"
                                                    id="btnQuickAddSubDept">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Designation Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="designation_name" class="form-control custom-input"
                                                placeholder="e.g. Software Engineer" required>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Status <span
                                                    class="text-danger">*</span></label>
                                            <select name="status" class="form-select custom-input" required>
                                                <option value="1" selected>Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mt-4 border-top pt-3 text-end">
                                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">Create
                                            Designation</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EDIT MODAL -->
                <div class="modal fade" id="editDesignationModal" aria-hidden="true" data-bs-focus="false">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content custom-modal">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">Edit Designation</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editDesignationForm" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Department <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <select name="department_id" id="edit_department_id"
                                                    class="form-select custom-input" required>
                                                    <option value="" disabled>Select Department</option>
                                                    @foreach($departments as $dept)
                                                        <option value="{{ $dept->id }}">{{ $dept->department_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Sub Department (Optional)</label>
                                            <div class="input-group">
                                                <select name="sub_department_id" id="edit_sub_department_id"
                                                    class="form-select custom-input">
                                                    <option value="">Select Sub Department</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Designation Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="designation_name" id="edit_designation_name"
                                                class="form-control custom-input" required>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Status <span
                                                    class="text-danger">*</span></label>
                                            <select name="status" id="edit_status" class="form-select custom-input"
                                                required>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mt-4 border-top pt-3 text-end">
                                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">Update
                                            Designation</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QUICK ADD DEPARTMENT MODAL -->
                <div class="modal fade modal-stack" id="quickAddDeptModal" aria-hidden="true" data-bs-focus="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content custom-modal border-0 shadow-lg">
                            <div class="modal-header bg-light pb-2">
                                <h6 class="modal-title fw-bold">Quick Add Department</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="quickAddDeptForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Department Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="department_name" class="form-control custom-input"
                                            required>
                                    </div>
                                    <input type="hidden" name="priority" value="99">
                                    <input type="hidden" name="status" value="1">
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary py-2 fw-bold w-100"
                                            id="btnSaveQuickDept">Save Department</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QUICK ADD SUB DEPARTMENT MODAL -->
                <div class="modal fade modal-stack" id="quickAddSubDeptModal" aria-hidden="true" data-bs-focus="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content custom-modal border-0 shadow-lg">
                            <div class="modal-header bg-light pb-2">
                                <h6 class="modal-title fw-bold">Quick Add Sub Department</h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="quickAddSubDeptForm">
                                    @csrf
                                    <input type="hidden" name="department_id" id="quick_sub_dept_parent_id">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Sub Department Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="sub_departments[0][name]"
                                            class="form-control custom-input" required>
                                        <input type="hidden" name="sub_departments[0][status]" value="1">
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary py-2 fw-bold w-100"
                                            id="btnSaveQuickSubDept">Save Sub Department</button>
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

            // 1. Initialize Sub Departments Data & Dynamic Logic
            const allSubDepts = @json($subDepartments ?? []);

            function populateSubDepartments(departmentId, targetSelectId, selectedSubDeptId = null) {
                const targetSelect = document.getElementById(targetSelectId);
                targetSelect.innerHTML = '<option value="">Select Sub Department (Optional)</option>';

                if (!departmentId) return;

                const filtered = allSubDepts.filter(sd => sd.department_id == departmentId);
                filtered.forEach(sd => {
                    const isSelected = (selectedSubDeptId == sd.id) ? 'selected' : '';
                    targetSelect.innerHTML += `<option value="${sd.id}" ${isSelected}>${sd.sub_department_name}</option>`;
                });
            }

            document.getElementById('add_department_id').addEventListener('change', function () {
                populateSubDepartments(this.value, 'add_sub_department_id');
            });

            document.getElementById('edit_department_id').addEventListener('change', function () {
                populateSubDepartments(this.value, 'edit_sub_department_id');
            });

            // 2. QUICK ADD AJAX Logic (Departments & Sub Departments)
            document.getElementById('quickAddDeptForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const btn = document.getElementById('btnSaveQuickDept');
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
                btn.disabled = true;

                const formData = new FormData(this);

                fetch('{{ route("departments.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                })
                    .then(response => response.json())
                    .then(data => {
                        btn.innerHTML = 'Save Department';
                        btn.disabled = false;

                        if (data.success) {
                            const newId = data.data.id;
                            const newName = data.data.department_name;

                            const addSelect = document.getElementById('add_department_id');
                            addSelect.add(new Option(newName, newId, false, true));
                            addSelect.dispatchEvent(new Event('change'));

                            document.getElementById('edit_department_id').add(new Option(newName, newId));
                            document.querySelector('select[name="department_id"]').add(new Option(newName, newId));

                            bootstrap.Modal.getInstance(document.getElementById('quickAddDeptModal')).hide();
                            this.reset();
                        } else {
                            alert(data.message || 'Error creating department');
                        }
                    }).catch(error => {
                        btn.innerHTML = 'Save Department';
                        btn.disabled = false;
                        alert('Server error occurred.');
                    });
            });

            document.getElementById('btnQuickAddSubDept').addEventListener('click', function () {
                const deptId = document.getElementById('add_department_id').value;
                if (!deptId) {
                    alert('Please select a Department first!');
                    return;
                }
                document.getElementById('quick_sub_dept_parent_id').value = deptId;
                const quickSubModal = new bootstrap.Modal(document.getElementById('quickAddSubDeptModal'), {focus: false});
                quickSubModal.show();
            });

            document.getElementById('quickAddSubDeptForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const btn = document.getElementById('btnSaveQuickSubDept');
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
                btn.disabled = true;

                const formData = new FormData(this);
                const parentDeptId = document.getElementById('quick_sub_dept_parent_id').value;

                fetch('{{ route("sub_departments.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {'X-Requested-With': 'XMLHttpRequest'}
                })
                    .then(response => response.json())
                    .then(data => {
                        btn.innerHTML = 'Save Sub Department';
                        btn.disabled = false;

                        if (data.success) {
                            const newId = data.data.id;
                            const newName = data.data.sub_department_name;

                            allSubDepts.push({
                                id: newId,
                                department_id: parentDeptId,
                                sub_department_name: newName
                            });

                            populateSubDepartments(parentDeptId, 'add_sub_department_id', newId);

                            bootstrap.Modal.getInstance(document.getElementById('quickAddSubDeptModal')).hide();
                            this.reset();
                        } else {
                            alert(data.message || 'Error creating sub department');
                        }
                    }).catch(error => {
                        btn.innerHTML = 'Save Sub Department';
                        btn.disabled = false;
                        alert('Server error occurred.');
                    });
            });

            // 3. Edit Modal Population Logic
            const editButtons = document.querySelectorAll('.edit-btn');

            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const form = document.getElementById('editDesignationForm');
                    let url = '{{ route("designations.update", ":id") }}';
                    form.action = url.replace(':id', id);

                    const departmentId = this.getAttribute('data-department_id');
                    const subDepartmentId = this.getAttribute('data-sub_department_id');

                    document.getElementById('edit_department_id').value = departmentId;
                    populateSubDepartments(departmentId, 'edit_sub_department_id', subDepartmentId);

                    document.getElementById('edit_designation_name').value = this.getAttribute('data-name');
                    document.getElementById('edit_status').value = this.getAttribute('data-status');
                });
            });

        });
    </script>
</body>

</html>