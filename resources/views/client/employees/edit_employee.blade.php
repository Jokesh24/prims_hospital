<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Employee - Dashboard</title>
    @include('client.includes.header_links')
</head>

<body>
    <div id="overlay" onclick="closeMobile()"></div>
    @include('client.includes.sidebar')

    <div id="main">
        @include('client.includes.header')

        <div class="content">
            <div class="container-fluid py-2">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold m-0">Edit Employee Profile</h4>
                        <p class="text-muted small mb-0">Update information for {{ $employee->employee_name }}</p>
                    </div>
                    <a href="{{ route('client.employees') }}" class="btn btn-outline-secondary px-4 py-2"
                        style="border-radius:10px;">
                        <i class="bi bi-arrow-left me-2"></i> Back to Directory
                    </a>
                </div>

                <!-- Global Form Errors Alert (Shows all validation issues upfront) -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Please fix the following errors to
                            proceed:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <strong><i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="dashboard-card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form action="{{ route('employees.update', $employee->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2"><i
                                    class="bi bi-person-badge me-2"></i>Basic Information</h6>

                            <!-- ROW 1 -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Employee ID <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="employee_id"
                                        class="form-control custom-input @error('employee_id') is-invalid @enderror"
                                        value="{{ old('employee_id', $employee->employee_id) }}">
                                    @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Full Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="employee_name"
                                        class="form-control custom-input @error('employee_name') is-invalid @enderror"
                                        value="{{ old('employee_name', $employee->employee_name) }}">
                                    @error('employee_name')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Department <span
                                            class="text-danger">*</span></label>
                                    <select name="department_id" id="department_id"
                                        class="form-select custom-input @error('department_id') is-invalid @enderror">
                                        <option value="" disabled>Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>
                                                {{ $dept->department_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Sub Department</label>
                                    <select name="sub_department_id" id="sub_department_id"
                                        class="form-select custom-input @error('sub_department_id') is-invalid @enderror">
                                        <option value="" selected disabled>Select Sub Department</option>
                                    </select>
                                    @error('sub_department_id')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- ROW 2 -->
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Designation <span
                                            class="text-danger">*</span></label>
                                    <select name="designation_id" id="designation_id"
                                        class="form-select custom-input @error('designation_id') is-invalid @enderror">
                                        <option value="" disabled>Select Designation</option>
                                        @foreach($designations as $desig)
                                            <option value="{{ $desig->id }}" {{ old('designation_id', $employee->designation_id) == $desig->id ? 'selected' : '' }}>
                                                {{ $desig->designation_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('designation_id')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Email Address </label>
                                    <input type="email" name="email"
                                        class="form-control custom-input @error('email') is-invalid @enderror"
                                        value="{{ old('email', $employee->email) }}">
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Mobile Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="mobile_number"
                                        class="form-control custom-input @error('mobile_number') is-invalid @enderror"
                                        value="{{ old('mobile_number', $employee->mobile_number) }}" maxlength="10">
                                    @error('mobile_number')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Joining Date </label>
                                    <input type="date" name="joining_date"
                                        class="form-control custom-input @error('joining_date') is-invalid @enderror"
                                        value="{{ old('joining_date', optional($employee->joining_date)->format('Y-m-d')) }}">
                                    @error('joining_date')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- ROW 3 -->
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Employee Photo</label>
                                    <input type="file" name="photo"
                                        class="form-control custom-input @error('photo') is-invalid @enderror"
                                        accept="image/*">
                                    @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($employee->photo) <small><a href="{{ asset('public/' . $employee->photo) }}"
                                    target="_blank">View Current Photo</a></small> @endif
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Gender <span
                                            class="text-danger">*</span></label>
                                    <select name="gender"
                                        class="form-select custom-input @error('gender') is-invalid @enderror">
                                        <option value="" disabled>Select Gender</option>
                                        <option value="1" {{ old('gender', $employee->gender) == 1 ? 'selected' : '' }}>
                                            Male</option>
                                        <option value="2" {{ old('gender', $employee->gender) == 2 ? 'selected' : '' }}>
                                            Female</option>
                                    </select>
                                    @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Status <span
                                            class="text-danger">*</span></label>
                                    <select name="status"
                                        class="form-select custom-input @error('status') is-invalid @enderror">
                                        <option value="1" {{ old('status', $employee->status) == 1 ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="0" {{ old('status', $employee->status) == 0 ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                    @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12 {{ old('status', $employee->status) == 0 ? '' : 'd-none' }}"
                                    id="inactive_comments_div">
                                    <label class="form-label small fw-bold">Reason for Inactivity <span
                                            class="text-danger">*</span></label>
                                    <textarea name="inactive_comments"
                                        class="form-control custom-input @error('inactive_comments') is-invalid @enderror"
                                        rows="2">{{ old('inactive_comments', $employee->inactive_comments) }}</textarea>
                                    @error('inactive_comments')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 mt-5"><i
                                    class="bi bi-house-heart me-2"></i>Personal & Family Details</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Date of Birth </label>
                                    <input type="date" name="dob"
                                        class="form-control custom-input @error('dob') is-invalid @enderror"
                                        value="{{ old('dob', optional($employee->dob)->format('Y-m-d')) }}">
                                    @error('dob')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Father / Spouse Name</label>
                                    <input type="text" name="father_spouse_name"
                                        class="form-control custom-input @error('father_spouse_name') is-invalid @enderror"
                                        value="{{ old('father_spouse_name', $employee->father_spouse_name) }}">
                                    @error('father_spouse_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Blood Group</label>
                                    <select name="blood_group"
                                        class="form-select custom-input @error('blood_group') is-invalid @enderror">
                                        <option value="" selected disabled>Select Group</option>
                                        @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                            <option value="{{ $bg }}" {{ old('blood_group', $employee->blood_group) == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                        @endforeach
                                    </select>
                                    @error('blood_group')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Alt. Mobile Number</label>
                                    <input type="text" name="alternative_mobile_number" id="alt_mobile"
                                        class="form-control custom-input @error('alternative_mobile_number') is-invalid @enderror"
                                        value="{{ old('alternative_mobile_number', $employee->alternative_mobile_number) }}"
                                        oninput="toggleRelationField()">
                                    @error('alternative_mobile_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 d-none" id="relation_div">
                                    <label class="form-label small fw-bold">Relation with Alt. No </label>
                                    <input type="text" name="relation_with_alt_mobile"
                                        class="form-control custom-input @error('relation_with_alt_mobile') is-invalid @enderror"
                                        value="{{ old('relation_with_alt_mobile', $employee->relation_with_alt_mobile) }}"
                                        placeholder="e.g. Father, Spouse">
                                    @error('relation_with_alt_mobile')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Marital Status</label>
                                    <select name="marital_status" id="marital_status"
                                        class="form-select custom-input @error('marital_status') is-invalid @enderror"
                                        onchange="toggleMarriageDate()">
                                        <option value="" disabled>Select Status</option>
                                        <option value="2" {{ old('marital_status', $employee->marital_status) == 2 ? 'selected' : '' }}>Unmarried</option>
                                        <option value="1" {{ old('marital_status', $employee->marital_status) == 1 ? 'selected' : '' }}>Married</option>
                                    </select>
                                    @error('marital_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 {{ old('marital_status', $employee->marital_status) == 1 ? '' : 'd-none' }}"
                                    id="marriage_date_div">
                                    <label class="form-label small fw-bold">Marriage Date</label>
                                    <input type="date" name="marriage_date"
                                        class="form-control custom-input @error('marriage_date') is-invalid @enderror"
                                        value="{{ old('marriage_date', optional($employee->marriage_date)->format('Y-m-d')) }}">
                                    @error('marriage_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Present Address</label>
                                    <textarea name="present_address"
                                        class="form-control custom-input @error('present_address') is-invalid @enderror"
                                        rows="2">{{ old('present_address', $employee->present_address) }}</textarea>
                                    @error('present_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Permanent Address</label>
                                    <textarea name="permanent_address"
                                        class="form-control custom-input @error('permanent_address') is-invalid @enderror"
                                        rows="2">{{ old('permanent_address', $employee->permanent_address) }}</textarea>
                                    @error('permanent_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 mt-5">
                                <h6 class="text-primary fw-bold mb-0">
                                    <i class="bi bi-cash-stack me-2"></i> Salary & Payment Information
                                </h6>

                                @php
                                    $isIndividual = isset($employee) && (
                                        ($earnings->isNotEmpty() && $earnings->first()->employee_id !== null) ||
                                        ($deductions->isNotEmpty() && $deductions->first()->employee_id !== null)
                                    );
                                @endphp

                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input border-secondary" type="checkbox"
                                        name="is_individual_salary" id="is_individual_salary" value="1" {{ $isIndividual || old('is_individual_salary') ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold text-dark"
                                        for="is_individual_salary">Individual Structure</label>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Gross Salary <span
                                            class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="gross_salary"
                                        class="form-control custom-input @error('gross_salary') is-invalid @enderror"
                                        value="{{ old('gross_salary', $employee->gross_salary ?? '') }}"
                                        placeholder="0.00">
                                    @error('gross_salary')
                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Payment Mode</label>
                                    <select name="payment_mode"
                                        class="form-select custom-input @error('payment_mode') is-invalid @enderror">
                                        <option value="1" {{ old('payment_mode', $employee->payment_mode ?? 1) == 1 ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="2" {{ old('payment_mode', $employee->payment_mode ?? '') == 2 ? 'selected' : '' }}>Cash</option>
                                        <option value="3" {{ old('payment_mode', $employee->payment_mode ?? '') == 3 ? 'selected' : '' }}>Cheque</option>
                                        <option value="4" {{ old('payment_mode', $employee->payment_mode ?? '') == 4 ? 'selected' : '' }}>UPI</option>
                                    </select>
                                    @error('payment_mode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">PF Applicable</label>
                                    <select name="pf_applicable" id="pf_applicable"
                                        class="form-select custom-input @error('pf_applicable') is-invalid @enderror"
                                        onchange="updatePFDeductionAvailability();">
                                        <option value="0" {{ old('pf_applicable', $employee->pf_applicable ?? 0) == 0 ? 'selected' : '' }}>No</option>
                                        <option value="1" {{ old('pf_applicable', $employee->pf_applicable ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                                    </select>
                                    @error('pf_applicable')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row" id="salary_components_wrapper">
                                <div class="col-12">
                                    <div id="global_warning_msg"
                                        class="alert alert-warning py-2 small fw-bold text-center mb-3">
                                        <i class="bi bi-info-circle me-1"></i> Global structure is applied. Turn on
                                        'Individual Structure' above to edit earning and deduction configurations.
                                    </div>
                                </div>

                                <!-- Earnings -->
                                <div class="col-lg-6 mb-4">
                                    <div class="card border-0 shadow-sm">
                                        <div
                                            class="card-header bg-white d-flex justify-content-between align-items-center">
                                            <h6 class="fw-bold text-primary mb-0">Earnings (Monthly)</h6>
                                            <button type="button" class="btn btn-sm btn-primary" id="addEarning">
                                                <i class="bi bi-plus-circle me-1"></i> Add Earning
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle mb-0" id="earningTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="40%">Component</th>
                                                        <th width="20%">Type</th>
                                                        <th width="20%">Value</th>
                                                        <th width="20%" class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($earnings as $earning)
                                                        <tr>
                                                            <td>
                                                                <input type="hidden" name="earning_id[]"
                                                                    value="{{ $earning->employee_id ? $earning->id : '' }}">
                                                                <input type="text" name="earning_title[]"
                                                                    class="form-control custom-input"
                                                                    value="{{ $earning->earnings_title }}" required>
                                                            </td>
                                                            <td>
                                                                <select name="earning_type[]"
                                                                    class="form-select custom-input">
                                                                    <option value="1" {{ $earning->value_type == 1 ? 'selected' : '' }}>%</option>
                                                                    <option value="2" {{ $earning->value_type == 2 ? 'selected' : '' }}>₹</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" name="earning_value[]"
                                                                    class="form-control custom-input"
                                                                    value="{{ $earning->value_type == 1 ? $earning->earnings_percent : $earning->earnings_money }}"
                                                                    required>
                                                            </td>
                                                            <td class="text-center">
                                                                <button type="button"
                                                                    class="btn btn-outline-danger btn-sm removeRow"><i
                                                                        class="bi bi-trash"></i></button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted py-3">No earnings
                                                                configured.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Deductions -->
                                <div class="col-lg-6 mb-4">
                                    <div class="card border-0 shadow-sm">
                                        <div
                                            class="card-header bg-white d-flex justify-content-between align-items-center">
                                            <h6 class="fw-bold text-danger mb-0">Deductions (Monthly)</h6>
                                            <button type="button" class="btn btn-sm btn-danger" id="addDeduction">
                                                <i class="bi bi-plus-circle me-1"></i> Add Deduction
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle mb-0" id="deductionTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="40%">Component</th>
                                                        <th width="20%">Type</th>
                                                        <th width="20%">Value</th>
                                                        <th width="20%" class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($deductions as $deduction)
                                                        @php
                                                            $isPT = in_array(strtoupper(trim($deduction->deduction_title)), ['PROFESSIONAL TAX', 'PTAX', 'PT', 'PROFESSIONAL TAX (PT)', 'PROF TAX']);
                                                        @endphp
                                                        <tr>
                                                            <td>
                                                                <input type="hidden" name="deduction_id[]"
                                                                    value="{{ $deduction->employee_id ? $deduction->id : '' }}">
                                                                <input type="text" name="deduction_title[]"
                                                                    class="form-control custom-input"
                                                                    value="{{ $deduction->deduction_title }}" {{ $isPT ? 'readonly' : 'required' }}>
                                                            </td>
                                                            <td>
                                                                <select name="deduction_type[]"
                                                                    class="form-select custom-input" {{ $isPT ? 'disabled' : '' }}>
                                                                    <option value="1" {{ $deduction->value_type == 1 ? 'selected' : '' }}>%</option>
                                                                    <option value="2" {{ $deduction->value_type == 2 ? 'selected' : '' }}>₹</option>
                                                                </select>
                                                                <!-- Hidden input to pass value since disabled selects aren't submitted -->
                                                                @if($isPT) <input type="hidden" name="deduction_type[]"
                                                                value="{{ $deduction->value_type }}"> @endif
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" name="deduction_value[]"
                                                                    class="form-control custom-input"
                                                                    value="{{ $deduction->value_type == 1 ? $deduction->deduction_percent : $deduction->deduction_money }}"
                                                                    {{ $isPT ? 'readonly' : 'required' }}>
                                                                @if($isPT)
                                                                    <small class="text-success fw-bold"
                                                                        style="font-size:10px;">Auto-calculated based on
                                                                        gross</small>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @if(!$isPT)
                                                                    <button type="button"
                                                                        class="btn btn-outline-danger btn-sm removeRow"><i
                                                                            class="bi bi-trash"></i></button>
                                                                @else
                                                                    <button type="button" class="btn btn-secondary btn-sm"
                                                                        disabled title="System managed"><i
                                                                            class="bi bi-lock-fill"></i></button>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted py-3">No
                                                                deductions configured.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 mt-5"><i
                                    class="bi bi-file-earmark-text me-2"></i>Documents & Statutory</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Aadhaar Number </label>
                                    <input type="text" name="aadhaar_number"
                                        class="form-control custom-input @error('aadhaar_number') is-invalid @enderror"
                                        value="{{ old('aadhaar_number', $employee->aadhaar_number) }}">
                                    @error('aadhaar_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Upload Aadhaar</label>
                                    <input type="file" name="aadhaar_proof"
                                        class="form-control custom-input @error('aadhaar_proof') is-invalid @enderror">
                                    @error('aadhaar_proof')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($employee->aadhaar_proof) <small><a
                                        href="{{ asset('public/' . $employee->aadhaar_proof) }}"
                                    target="_blank">View Current Document</a></small> @endif
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">PAN Number</label>
                                    <input type="text" name="pan_number"
                                        class="form-control custom-input @error('pan_number') is-invalid @enderror"
                                        value="{{ old('pan_number', $employee->pan_number) }}">
                                    @error('pan_number')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Upload PAN</label>
                                    <input type="file" name="pan_proof"
                                        class="form-control custom-input @error('pan_proof') is-invalid @enderror">
                                    @error('pan_proof')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($employee->pan_proof) <small><a
                                        href="{{ asset('public/' . $employee->pan_proof) }}" target="_blank">View
                                    Current Document</a></small> @endif
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">UAN Number</label>
                                    <input type="text" name="uan_number"
                                        class="form-control custom-input @error('uan_number') is-invalid @enderror"
                                        value="{{ old('uan_number', $employee->uan_number) }}">
                                    @error('uan_number')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">ESI Number</label>
                                    <input type="text" name="esi_number"
                                        class="form-control custom-input @error('esi_number') is-invalid @enderror"
                                        value="{{ old('esi_number', $employee->esi_number) }}">
                                    @error('esi_number')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 mt-5"><i
                                    class="bi bi-mortarboard me-2"></i>Qualification Details</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Highest Qualification</label>
                                    <input type="text" name="highest_qualification"
                                        class="form-control custom-input @error('highest_qualification') is-invalid @enderror"
                                        value="{{ old('highest_qualification', $employee->highest_qualification) }}"
                                        placeholder="e.g. B.Tech, MBA">
                                    @error('highest_qualification')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Upload Certificate</label>
                                    <input type="file" name="qualification_proof"
                                        class="form-control custom-input @error('qualification_proof') is-invalid @enderror">
                                    @error('qualification_proof')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if($employee->qualification_proof) <small><a
                                        href="{{ asset('public/' . $employee->qualification_proof) }}"
                                    target="_blank">View Current Certificate</a></small> @endif
                                </div>
                            </div>

                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 mt-5"><i
                                    class="bi bi-bank me-2"></i>Bank Account Details</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Bank Name</label>
                                    <input type="text" name="bank_name"
                                        class="form-control custom-input @error('bank_name') is-invalid @enderror"
                                        value="{{ old('bank_name', $employee->bank_name) }}">
                                    @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Branch Name</label>
                                    <input type="text" name="bank_branch"
                                        class="form-control custom-input @error('bank_branch') is-invalid @enderror"
                                        value="{{ old('bank_branch', $employee->bank_branch ?? '') }}">
                                    @error('bank_branch')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Account Number</label>
                                    <input type="text" name="bank_account_number"
                                        class="form-control custom-input @error('bank_account_number') is-invalid @enderror"
                                        value="{{ old('bank_account_number', $employee->bank_account_number) }}">
                                    @error('bank_account_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">IFSC Code</label>
                                    <input type="text" name="ifsc_code"
                                        class="form-control custom-input @error('ifsc_code') is-invalid @enderror"
                                        value="{{ old('ifsc_code', $employee->ifsc_code) }}">
                                    @error('ifsc_code')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Account Type</label>
                                    <select name="account_type"
                                        class="form-select custom-input @error('account_type') is-invalid @enderror">
                                        <option value="" selected disabled>Select Type</option>
                                        <option value="Savings" {{ old('account_type', $employee->account_type) == 'Savings' ? 'selected' : '' }}>Savings</option>
                                        <option value="Current" {{ old('account_type', $employee->account_type) == 'Current' ? 'selected' : '' }}>Current</option>
                                    </select>
                                    @error('account_type')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mt-5 d-flex justify-content-end gap-3 border-top pt-4">
                                <a href="{{ route('client.employees') }}"
                                    class="btn btn-light px-5 fw-bold border">Cancel</a>
                                <button type="submit" class="btn btn-primary py-2 px-5 fw-bold">Update Employee</button>

                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
        @include('client.includes.footer_links')
    </div>

    <script>
        function toggleRelationField() {
            var altMobile = document.getElementById('alt_mobile').value;
            var relationDiv = document.getElementById('relation_div');
            var relationInput = relationDiv.querySelector('input');

            var hasError = relationInput.classList.contains('is-invalid');

            if (altMobile.trim() !== "" || hasError) {
                relationDiv.classList.remove('d-none');
                relationInput.setAttribute('required', 'required');
            } else {
                relationDiv.classList.add('d-none');
                relationInput.removeAttribute('required');
            }
        }

        // Toggle Marriage Date
        function toggleMarriageDate() {
            var status = document.getElementById('marital_status').value;
            var div = document.getElementById('marriage_date_div');
            if (status == 1) {
                div.classList.remove('d-none');
            } else {
                div.classList.add('d-none');
            }
        }

        function togglePFCeiling() {
            var pfApplicable = document.getElementById('pf_applicable') ? document.getElementById('pf_applicable').value : null;
            var pfCeilingDiv = document.getElementById('pf_ceiling_div');
            var pfCeiling = document.getElementById('pf_ceiling');

            if (pfApplicable !== null) {
                if (pfApplicable == '1') {
                    if (pfCeilingDiv) pfCeilingDiv.classList.remove('d-none');
                } else {
                    if (pfCeilingDiv) pfCeilingDiv.classList.add('d-none');
                    if (pfCeiling) pfCeiling.value = '1';
                }
            }
        }

        // Toggle Individual/Global Structure Fields
        function toggleSalaryScope() {
            var isIndie = $('#is_individual_salary').is(':checked');
            if (isIndie) {
                $('#global_warning_msg').addClass('d-none');
                $('#earningTable input, #earningTable select, #earningTable button').prop('disabled', false);
                $('#deductionTable input, #deductionTable select, #deductionTable button').prop('disabled', false);
                $('#addEarning, #addDeduction').prop('disabled', false);
            } else {
                $('#global_warning_msg').removeClass('d-none');
                $('#earningTable input, #earningTable select, #earningTable button').prop('disabled', true);
                $('#deductionTable input, #deductionTable select, #deductionTable button').prop('disabled', true);
                $('#addEarning, #addDeduction').prop('disabled', true);
            }
        }

        function allowManualPF() {
            return $('#pf_applicable').val() == '1';
        }

        function updatePFDeductionAvailability() {
            var manualPFAllowed = allowManualPF();

            $('#deductionTable tbody tr').each(function () {
                var titleInput = $(this).find('input[name="deduction_title[]"]');
                if (!titleInput.length) return;

                var title = titleInput.val().trim().toUpperCase();
                var isPF = title === 'PF' || title === 'P.F.' || title.includes('PROVIDENT FUND') || title.includes('PF ');

                if (isPF && !manualPFAllowed) {
                    $(this).remove();
                }
            });
        }

        // NEW FUNCTION: Auto-fill PF when Individual Structure is checked and PF is applicable
        function handleAutoPF() {
            var isIndie = $('#is_individual_salary').is(':checked');
            var pfApplicable = $('#pf_applicable').val() == '1';

            if (isIndie && pfApplicable) {
                var pfFound = false;
                $('#deductionTable tbody tr').each(function () {
                    var titleInput = $(this).find('input[name="deduction_title[]"]');
                    if (titleInput.length) {
                        var title = titleInput.val().trim().toUpperCase();
                        if (title === 'PF' || title === 'P.F.' || title.includes('PROVIDENT FUND') || title.includes('PF ')) {
                            pfFound = true;
                        }
                    }
                });

                if (!pfFound) {
                    // Remove the "No deductions configured" placeholder if present
                    $('#deductionTable tbody').find('td[colspan="4"]').closest('tr').remove();

                    var pfRow = `
                    <tr>
                        <td>
                            <input type="hidden" name="deduction_id[]" value="">
                            <input type="text" name="deduction_title[]" class="form-control custom-input" value="PF" required>
                        </td>
                        <td>
                            <select name="deduction_type[]" class="form-select custom-input">
                                <option value="1" selected>%</option>
                                <option value="2">₹</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" name="deduction_value[]" class="form-control custom-input" value="12" required>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm removeRow"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;

                    $('#deductionTable tbody').append(pfRow);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            toggleRelationField();
            if (typeof toggleMarriageDate === "function") toggleMarriageDate();
            if (typeof togglePFCeiling === "function") togglePFCeiling();

            updatePFDeductionAvailability();
            toggleSalaryScope();
            handleAutoPF(); // Trigger on load
        });

        // Event Listeners
        $('#is_individual_salary').on('change', function () {
            toggleSalaryScope();
            handleAutoPF(); // Trigger when structure toggles
        });

        $('#pf_applicable').on('change', function () {
            updatePFDeductionAvailability();
            handleAutoPF(); // Trigger when PF Dropdown changes
        });

        // Toggle Inactive Comments (for edit_employee)
        var statusSelect = document.querySelector('select[name="status"]');
        if (statusSelect) {
            statusSelect.addEventListener('change', function () {
                var commentDiv = document.getElementById('inactive_comments_div');
                var commentInput = commentDiv.querySelector('textarea');

                if (this.value == '0') {
                    commentDiv.classList.remove('d-none');
                    commentInput.setAttribute('required', 'required');
                } else {
                    commentDiv.classList.add('d-none');
                    commentInput.removeAttribute('required');
                }
            });
        }

        $(document).on('click', '.removeRow', function () {
            $(this).closest('tr').remove();
            if ($('#deductionTable tbody tr').length === 0) {
                $('#deductionTable tbody').html('<tr><td colspan="4" class="text-center text-muted py-3">No deductions configured.</td></tr>');
            }
        });

        function earningRow() {
            return `
            <tr>
                <td>
                    <input type="hidden" name="earning_id[]" value="">
                    <input type="text" name="earning_title[]" class="form-control custom-input" placeholder="Ex: Basic Salary" required>
                </td>
                <td>
                    <select name="earning_type[]" class="form-select custom-input">
                        <option value="1">%</option>
                        <option value="2">₹</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" name="earning_value[]" class="form-control custom-input" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm removeRow"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
            `;
        }

        function deductionRow() {
            return `
            <tr>
                <td>
                    <input type="hidden" name="deduction_id[]" value="">
                    <input type="text" name="deduction_title[]" class="form-control custom-input" placeholder="Ex: PF" required>
                </td>
                <td>
                    <select name="deduction_type[]" class="form-select custom-input">
                        <option value="1">%</option>
                        <option value="2">₹</option>
                    </select>
                </td>
                <td>
                    <input type="number" step="0.01" name="deduction_value[]" class="form-control custom-input" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm removeRow"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
            `;
        }

        $('#addEarning').click(function () {
            // Remove the "No earnings configured" placeholder if present
            $('#earningTable tbody').find('td[colspan="4"]').closest('tr').remove();
            $('#earningTable tbody').append(earningRow());
        });

        $('#addDeduction').click(function () {
            // Remove the "No deductions configured" placeholder if present
            $('#deductionTable tbody').find('td[colspan="4"]').closest('tr').remove();
            $('#deductionTable tbody').append(deductionRow());

            // PF and PT Validation on typing for newly added rows
            $('#deductionTable tbody tr').last().find('input[name="deduction_title[]"]').on('input', function () {
                var title = $(this).val().trim().toUpperCase();
                var isPF = title === 'PF' || title === 'P.F.' || title.includes('PROVIDENT FUND') || title.includes('PF ');

                // Define PT aliases
                var ptAliases = ['PROFESSIONAL TAX', 'PTAX', 'PT', 'PROFESSIONAL TAX (PT)', 'PROF TAX'];
                var isPT = ptAliases.includes(title);

                if (isPF && !allowManualPF()) {
                    alert('PF cannot be added manually because PF Applicable is set to No.');
                    $(this).val('');
                }

                if (isPT) {
                    alert('Professional Tax (PT) is automatically calculated and applied by the system based on Gross Salary. You do not need to add it manually.');
                    $(this).val('');
                }
            });
        });

        // -------------------------------------------------------------
        // Real-time Gross Salary & Professional Tax (PT) Automation
        // -------------------------------------------------------------
        $('input[name="gross_salary"]').on('input', function () {
            var salary = parseFloat($(this).val()) || 0;
            var ptax = 0;

            // 1. Calculate PT Bracket
            if (salary >= 15001 && salary <= 20000) {
                ptax = 150;
            } else if (salary > 20000) {
                ptax = 200;
            }

            // 2. Automatically turn ON 'Individual Structure' if it's off
            var indieToggle = $('#is_individual_salary');
            if (!indieToggle.is(':checked')) {
                indieToggle.prop('checked', true).trigger('change');
            }

            // 3. Update, Add, or Remove the PT row dynamically
            var ptFound = false;
            var ptAliases = ['PROFESSIONAL TAX', 'PTAX', 'PT', 'PROFESSIONAL TAX (PT)', 'PROF TAX'];

            $('#deductionTable tbody tr').each(function () {
                var titleInput = $(this).find('input[name="deduction_title[]"]');
                if (titleInput.length) {
                    var title = titleInput.val().trim().toUpperCase();
                    if (ptAliases.includes(title)) {
                        ptFound = true;
                        if (ptax > 0) {
                            // Update existing PT row value
                            $(this).find('input[name="deduction_value[]"]').val(ptax);
                        } else {
                            // Remove PT row if salary drops below 15,001
                            $(this).remove();
                            // Restore empty state if no deductions remain
                            if ($('#deductionTable tbody tr').length === 0) {
                                $('#deductionTable tbody').html('<tr><td colspan="4" class="text-center text-muted py-3">No deductions configured.</td></tr>');
                            }
                        }
                    }
                }
            });

            // 4. Append locked PT row if it doesn't exist and salary qualifies
            if (!ptFound && ptax > 0) {
                // Remove the "No deductions configured" placeholder if present
                $('#deductionTable tbody').find('td[colspan="4"]').closest('tr').remove();

                var ptRow = `
        <tr>
            <td>
                <input type="hidden" name="deduction_id[]" value="">
                <input type="text" name="deduction_title[]" class="form-control custom-input" value="Professional Tax" readonly>
            </td>
            <td>
                <select class="form-select custom-input" disabled>
                    <option value="2" selected>₹</option>
                </select>
                <input type="hidden" name="deduction_type[]" value="2">
            </td>
            <td>
                <input type="number" step="0.01" name="deduction_value[]" class="form-control custom-input" value="${ptax}" readonly>
                <small class="text-success fw-bold" style="font-size:10px;">Auto-calculated based on gross</small>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-secondary btn-sm" disabled title="System managed"><i class="bi bi-lock-fill"></i></button>
            </td>
        </tr>`;

                $('#deductionTable tbody').append(ptRow);
            }
        });

        // -------------------------------------------------------------
        // Dependent Dropdown for Department -> Sub Departments & Designations
        // -------------------------------------------------------------
        const departmentSelect = document.getElementById('department_id');
        const subDeptSelect = document.getElementById('sub_department_id');
        const designationSelect = document.getElementById('designation_id');

        // Check if variables exist (to prevent JS errors if they aren't loaded in a specific view)
        const allSubDepts = typeof @json($subDepartments ?? []) !== 'undefined' ? @json($subDepartments ?? []) : [];
        const currentSubDept = "{{ old('sub_department_id', $employee->sub_department_id ?? '') }}";

        function populateSubDepartments(deptId, selectedId = null) {
            if (!subDeptSelect) return;
            subDeptSelect.innerHTML = '<option value="" selected disabled>Select Sub Department</option>';
            if (!deptId) return;

            const filtered = allSubDepts.filter(sd => sd.department_id == deptId);
            if (filtered.length > 0) {
                filtered.forEach(sd => {
                    let option = document.createElement('option');
                    option.value = sd.id;
                    option.textContent = sd.sub_department_name;
                    if (selectedId && selectedId == sd.id) {
                        option.selected = true;
                    }
                    subDeptSelect.appendChild(option);
                });
            } else {
                subDeptSelect.innerHTML = '<option value="" selected disabled>No Sub Departments Available</option>';
            }
        }

        // Initialize sub-departments on page load
        if (departmentSelect && departmentSelect.value) {
            populateSubDepartments(departmentSelect.value, currentSubDept);
        }

        if (departmentSelect) {
            departmentSelect.addEventListener('change', function () {
                let departmentId = this.value;

                // 1. Populate Sub Departments (Local Filtering)
                populateSubDepartments(departmentId);

                // 2. Fetch Designations via AJAX
                if (designationSelect) {
                    designationSelect.innerHTML = '<option value="" selected disabled>Loading Designations...</option>';
                    let url = '{{ route("client.get.designations", ":id") }}';
                    url = url.replace(':id', departmentId);

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            designationSelect.innerHTML = '<option value="" selected disabled>Select Designation</option>';

                            if (data.length === 0) {
                                designationSelect.innerHTML = '<option value="" disabled>No Designations Found</option>';
                                return;
                            }

                            data.forEach(designation => {
                                let option = document.createElement('option');
                                option.value = designation.id;
                                option.textContent = designation.designation_name;
                                designationSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching designations:', error);
                            designationSelect.innerHTML = '<option value="" selected disabled>Error loading designations</option>';
                        });
                }
            });
        }
    </script>
</body>

</html>