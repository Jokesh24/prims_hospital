<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Login Access - HRMS</title>
    @include('client.includes.header_links')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .permission-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .permission-header {
            background: #f8fafc;
            padding: 10px 15px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 700;
            color: #1e293b;
            border-radius: 8px 8px 0 0;
        }

        .permission-body {
            padding: 15px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }

        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.375rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold m-0"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Login Access</h4>
                    <a href="{{ route('client.login_access') }}" class="btn btn-light border"><i
                            class="bi bi-arrow-left me-1"></i>Back</a>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{$error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
                @endif

                <form action="{{ route('login_access.update', $access->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="dashboard-card mb-4 p-4">
                        <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">1. Select Employee</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Department <span
                                        class="text-danger">*</span></label>
                                <select id="dept_id" class="form-select bg-light select2" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ $access->employee->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Sub Department</label>
                                <select id="sub_dept_id" class="form-select bg-light select2">
                                    <option value="">All</option>
                                    @foreach($subDepartments as $sd)
                                        <option value="{{ $sd->id }}" {{ $access->employee->sub_department_id == $sd->id ? 'selected' : '' }}>{{ $sd->sub_department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Designation</label>
                                <select id="designation_id" class="form-select bg-light select2">
                                    <option value="">All</option>
                                    @foreach($designations as $des)
                                        <option value="{{ $des->id }}" {{ $access->employee->designation_id == $des->id ? 'selected' : '' }}>{{ $des->designation_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Employee <span
                                        class="text-danger">*</span></label>
                                <select name="employee_id" id="employee_id" class="form-select bg-light select2"
                                    required>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ $access->employee_id == $emp->id ? 'selected' : '' }}>{{ $emp->employee_name }} ({{$emp->employee_id }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    @php $perms = $access->accessible_sections ?? []; @endphp

                    <div class="dashboard-card mb-4 p-4">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h6 class="fw-bold text-dark m-0">2. Configure Module Access</h6>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllBtn">Select
                                All</button>
                        </div>

                        <div class="permission-card">
                            <div class="permission-header">Master Data</div>
                            <div class="permission-body">
                                <div class="form-check"><input class="form-check-input perm-cb" type="checkbox"
                                        name="sections[]" value="holidays" id="p1" {{ in_array('holidays', $perms) ? 'checked' : '' }}><label class="form-check-label" for="p1">Holidays</label>
                                </div>
                                <div class="form-check"><input class="form-check-input perm-cb" type="checkbox"
                                        name="sections[]" value="paidleaves" id="p2" {{ in_array('paidleaves', $perms) ? 'checked' : '' }}><label class="form-check-label" for="p2">Paid Leaves</label>
                                </div>
                                <div class="form-check"><input class="form-check-input perm-cb" type="checkbox"
                                        name="sections[]" value="shifts" id="p3" {{ in_array('shifts', $perms) ? 'checked' : '' }}><label class="form-check-label" for="p3">Shifts</label></div>
                            </div>
                        </div>

                        <div class="permission-card">
                            <div class="permission-header">Employee Management</div>
                            <div class="permission-body">
                                <div class="form-check"><input class="form-check-input perm-cb" type="checkbox"
                                        name="sections[]" value="departments" id="p4" {{ in_array('departments', $perms) ? 'checked' : '' }}><label class="form-check-label" for="p4">Departments</label>
                                </div>
                                <div class="form-check"><input class="form-check-input perm-cb" type="checkbox"
                                        name="sections[]" value="sub_departments" id="p5" {{ in_array('sub_departments', $perms) ? 'checked' : '' }}><label class="form-check-label"
                                        for="p5">Sub-Departments</label></div>
                                <div class="form-check"><input class="form-check-input perm-cb" type="checkbox"
                                        name="sections[]" value="designations" id="p6" {{ in_array('designations', $perms) ? 'checked' : '' }}><label class="form-check-label"
                                        for="p6">Designations</label></div>
                                <div class="form-check"><input class="form-check-input perm-cb" type="checkbox"
                                        name="sections[]" value="roster" id="p7" {{ in_array('roster', $perms) ? 'checked' : '' }}><label class="form-check-label" for="p7">Roster Manage</label>
                                </div>
                                <div class="form-check"><input class="form-check-input perm-cb" type="checkbox"
                                        name="sections[]" value="employees" id="p8" {{ in_array('employees', $perms) ? 'checked' : '' }}><label class="form-check-label" for="p8">Employee
                                        Directory</label></div>
                            </div>
                        </div>

                        <div class="permission-card">
                            <div class="permission-header">Leaves & Payroll</div>
                            <div class="permission-body">
                                <div class="form-check"><input class="form-check-input perm-cb" type="checkbox"
                                        name="sections[]" value="manage_leaves" id="p9" {{ in_array('manage_leaves', $perms) ? 'checked' : '' }}><label class="form-check-label" for="p9">Manage
                                        Leaves</label></div>
                                <div class="form-check"><input class="form-check-input perm-cb" type="checkbox"
                                        name="sections[]" value="daily_attendance" id="p10" {{ in_array('daily_attendance', $perms) ? 'checked' : '' }}><label
                                        class="form-check-label" for="p10">Daily Attendance</label></div>
                                <div class="form-check"><input class="form-check-input perm-cb" type="checkbox"
                                        name="sections[]" value="monthly_attendance" id="p11" {{ in_array('monthly_attendance', $perms) ? 'checked' : '' }}><label
                                        class="form-check-label" for="p11">Monthly Attendance</label></div>
                                <div class="form-check"><input class="form-check-input perm-cb" type="checkbox"
                                        name="sections[]" value="payslips" id="p12" {{ in_array('payslips', $perms) ? 'checked' : '' }}><label class="form-check-label" for="p12">Payslips</label>
                                </div>
                                <div class="form-check"><input class="form-check-input perm-cb" type="checkbox"
                                        name="sections[]" value="advances" id="p13" {{ in_array('advances', $perms) ? 'checked' : '' }}><label class="form-check-label" for="p13">Advances</label>
                                </div>
                            </div>
                        </div>

                        <div class="permission-card">
                            <div class="permission-header">Settings</div>
                            <div class="permission-body">
                                <div class="form-check"><input class="form-check-input perm-cb" type="checkbox"
                                        name="sections[]" value="payroll_settings" id="p14" {{ in_array('payroll_settings', $perms) ? 'checked' : '' }}><label
                                        class="form-check-label" for="p14">Payroll Settings</label></div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Login Status <span
                                        class="text-danger">*</span></label>
                                <select name="status" class="form-select bg-light" required>
                                    <option value="1" {{ $access->status == 1 ? 'selected' : '' }}>Active (Can Login)
                                    </option>
                                    <option value="0" {{ $access->status == 0 ? 'selected' : '' }}>Inactive (Cannot Login)
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="text-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold"
                                style="border-radius:8px;">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('client.includes.footer_links')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({width: '100%'});

            $('#selectAllBtn').click(function () {
                let allChecked = $('.perm-cb').length === $('.perm-cb:checked').length;
                $('.perm-cb').prop('checked', !allChecked); $(this).text(!allChecked ? 'Deselect All' : 'Select All');
            });

            function fetchAjax(type, payload, targetSelect, placeholder) {
                $(targetSelect).html(`<option value="">Loading...</option>`);
                $.ajax({
                    url: "{{ route('login_access.fetch_employees') }}",
                    type: "POST",
                    data: {_token: "{{ csrf_token() }}", type: type, ...payload},
                    success: function (res) {
                        let options = `<option value="">${placeholder}</option>`;
                        if (res.status && res.data.length > 0) {
                            res.data.forEach(item => {options += `<option value="${item.id}">${item.name}</option>`;});
                        }
                        $(targetSelect).html(options);
                    }
                });
            }

            $('#dept_id').change(function () {
                let deptId = $(this).val();
                if (!deptId) return;
                fetchAjax('sub_departments', {department_id: deptId}, '#sub_dept_id', 'All Sub Departments');
                fetchAjax('designations', {department_id: deptId}, '#designation_id', 'All Designations');
                fetchAjax('employees', {department_id: deptId}, '#employee_id', 'Select Employee');
            });

            $('#sub_dept_id').change(function () {
                let payload = {department_id: $('#dept_id').val(), sub_department_id: $(this).val()};
                fetchAjax('designations', payload, '#designation_id', 'All Designations');
                fetchAjax('employees', payload, '#employee_id', 'Select Employee');
            });

            $('#designation_id').change(function () {
                let payload = {department_id: $('#dept_id').val(), sub_department_id: $('#sub_dept_id').val(), designation_id: $(this).val()};
                fetchAjax('employees', payload, '#employee_id', 'Select Employee');
            });
        });
    </script>
</body>

</html>