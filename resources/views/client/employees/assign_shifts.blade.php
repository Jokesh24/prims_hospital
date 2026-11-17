<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Assign Employee Shifts - Dashboard</title>
    @include('client.includes.header_links')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .custom-input {
            font-size: 0.9rem;
        }

        .emp-table-wrapper {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .emp-table th {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            z-index: 1;
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
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><strong>{{ session('success') }}</strong>
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
                    <h4 class="fw-bold m-0">Master Shift Assignment</h4>
                    <a href="{{ route('client.employee_shifts.view') }}" class="btn btn-primary px-4 py-2"
                        style="border-radius:10px;">
                        <i class="bi bi-list-check me-2"></i> View Assigned Shifts
                    </a>
                </div>

                <div class="row">
                    <!-- Target Selection Filters -->
                    <div class="col-lg-4 mb-3">
                        <div class="dashboard-card h-100">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-funnel me-2"></i>Filter Target
                                Selection</h6>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Department <span
                                        class="text-danger">*</span></label>
                                <select id="filter_department" class="form-select custom-input">
                                    <option value="" selected disabled>Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Sub Department (Optional)</label>
                                <select id="filter_sub_department" class="form-select custom-input">
                                    <option value="">-- All Sub Departments --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Designation <span
                                        class="text-danger">*</span></label>
                                <select id="filter_designation" class="form-select custom-input">
                                    <option value="">-- Select Designation --</option>
                                </select>
                            </div>
                            <button type="button" id="btn_fetch_data" class="btn btn-primary w-100 py-2 mt-2">
                                <i class="bi bi-search me-2"></i> Fetch Employees & Shifts
                            </button>
                        </div>
                    </div>

                    <!-- Assignment Panel -->
                    <div class="col-lg-8 mb-3">
                        <div class="dashboard-card h-100">
                            <form action="{{ route('client.employee_shifts.store') }}" method="POST"
                                id="assignmentForm">
                                @csrf
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Select Master Shift <span
                                                class="text-danger">*</span></label>
                                        <select name="designation_shift_id" id="assign_shift_id"
                                            class="form-select custom-input" required>
                                            <option value="">-- Please fetch data first --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Start Date <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="start_date"
                                            class="form-control custom-input datepicker" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">No. of Days <span
                                                class="text-danger">*</span></label>
                                        <input type="number" name="number_of_days" class="form-control custom-input"
                                            min="1" value="7" required>
                                    </div>
                                </div>

                                <h6 class="fw-bold mb-3 border-bottom pb-2">Select Employees</h6>
                                <div class="emp-table-wrapper mb-4">
                                    <table class="table table-hover table-sm emp-table align-middle m-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-3 py-2" style="width:50px;">
                                                    <input class="form-check-input" type="checkbox" id="check_all">
                                                </th>
                                                <th class="py-2">EMP ID</th>
                                                <th class="py-2">Employee Name</th>
                                            </tr>
                                        </thead>
                                        <tbody id="employee_table_body">
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted small">Select filters
                                                    and fetch data to view employees.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-success px-5 py-2 fw-bold" id="btn_submit"
                                        disabled>
                                        <i class="bi bi-calendar-check me-2"></i> Assign Shift
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @include('client.includes.footer_links')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            flatpickr(".datepicker", {dateFormat: "Y-m-d", minDate: "today"});

            const deptSelect = document.getElementById('filter_department');
            const subDeptSelect = document.getElementById('filter_sub_department');
            const desigSelect = document.getElementById('filter_designation');
            const fetchBtn = document.getElementById('btn_fetch_data');
            const shiftSelect = document.getElementById('assign_shift_id');
            const empTableBody = document.getElementById('employee_table_body');
            const checkAll = document.getElementById('check_all');
            const submitBtn = document.getElementById('btn_submit');

            // Load Sub Departments and Designations based on Department
            deptSelect.addEventListener('change', function () {
                const deptId = this.value;
                subDeptSelect.innerHTML = '<option value="">-- All Sub Departments --</option>';
                desigSelect.innerHTML = '<option value="">-- Select Designation --</option>';

                fetch(`{{ route('client.employee_shifts.dependencies') }}?department_id=${deptId}`)
                    .then(res => res.json())
                    .then(data => {
                        data.sub_departments.forEach(sub => {
                            subDeptSelect.add(new Option(sub.sub_department_name, sub.id));
                        });
                        data.designations.forEach(desig => {
                            desigSelect.add(new Option(desig.designation_name, desig.id));
                        });
                    });
            });

            // Fetch Employees & Shifts
            fetchBtn.addEventListener('click', function () {
                const deptId = deptSelect.value;
                const desigId = desigSelect.value;
                const subDeptId = subDeptSelect.value;

                if (!deptId || !desigId) {
                    alert('Please select both Department and Designation.');
                    return;
                }

                fetchBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Fetching...';
                fetchBtn.disabled = true;

                fetch(`{{ route('client.employee_shifts.data') }}?department_id=${deptId}&designation_id=${desigId}&sub_department_id=${subDeptId}`)
                    .then(res => res.json())
                    .then(data => {
                        // Populate Shifts
                        shiftSelect.innerHTML = '<option value="" disabled selected>-- Select a Shift --</option>';
                        if (data.shifts.length === 0) {
                            shiftSelect.add(new Option('No shifts found for this designation', ''));
                        } else {
                            data.shifts.forEach(shift => {
                                shiftSelect.add(new Option(shift.display_name, shift.id));
                            });
                        }

                        // Populate Employees
                        empTableBody.innerHTML = '';
                        if (data.employees.length === 0) {
                            empTableBody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted">No active employees found for this criteria.</td></tr>';
                            submitBtn.disabled = true;
                        } else {
                            data.employees.forEach(emp => {
                                empTableBody.insertAdjacentHTML('beforeend', `
                                    <tr>
                                        <td class="ps-3"><input class="form-check-input emp-checkbox" type="checkbox" name="employee_ids[]" value="${emp.id}"></td>
                                        <td class="fw-bold">${emp.emp_code}</td>
                                        <td>${emp.employee_name}</td>
                                    </tr>
                                `);
                            });
                            submitBtn.disabled = false;
                        }
                    })
                    .finally(() => {
                        fetchBtn.innerHTML = '<i class="bi bi-search me-2"></i> Fetch Employees & Shifts';
                        fetchBtn.disabled = false;
                        checkAll.checked = false;
                    });
            });

            // Handle Check All
            checkAll.addEventListener('change', function () {
                document.querySelectorAll('.emp-checkbox').forEach(cb => cb.checked = this.checked);
            });

            // Form Validation before submit
            document.getElementById('assignmentForm').addEventListener('submit', function (e) {
                if (document.querySelectorAll('.emp-checkbox:checked').length === 0) {
                    e.preventDefault();
                    alert('Please select at least one employee to assign the shift.');
                }
            });
        });
    </script>
</body>

</html>