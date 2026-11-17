<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>View Employee - Dashboard</title>
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
                        <h4 class="fw-bold m-0">Employee Profile</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('client.employees') }}" class="btn btn-outline-secondary px-4 py-2"
                            style="border-radius:10px;">
                            <i class="bi bi-arrow-left me-2"></i> Back
                        </a>
                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary px-4 py-2"
                            style="border-radius:10px;">
                            <i class="bi bi-pencil me-2"></i> Edit Profile
                        </a>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <div class="dashboard-card shadow-sm border-0 d-flex align-items-center p-4">
                            @if($employee->photo)
                                <img src="{{ asset('public/' . $employee->photo) }}"
                                    style="width:100px; height:100px; border-radius:50%; object-fit:cover;"
                                    class="border border-3 border-primary p-1 me-4">
                            @else
                                <div class="bg-primary text-white d-flex align-items-center justify-content-center me-4"
                                    style="width:100px; height:100px; border-radius:50%; font-size: 32px; font-weight: bold;">
                                    {{ strtoupper(substr($employee->employee_name, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <h3 class="fw-bold mb-1">{{ $employee->employee_name }}</h3>
                                <p class="text-muted mb-2">{{ $employee->designationInfo->designation_name ?? '--' }} |
                                    {{ $employee->department->department_name ?? 'No Department' }}</p>
                                <span class="badge {{ $employee->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $employee->status == 1 ? 'Active Employee' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="dashboard-card shadow-sm border-0 h-100">
                            <div class="card-body p-4">
                                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i
                                        class="bi bi-person-badge me-2"></i>Basic Information</h6>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="text-muted w-50">Employee ID</td>
                                        <td class="fw-medium">{{ $employee->employee_id }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Email</td>
                                        <td class="fw-medium">{{ $employee->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Mobile Number</td>
                                        <td class="fw-medium">{{ $employee->mobile_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Gross Salary</td>
                                        <td class="fw-medium"><i
                                                class="bi bi-currency-rupee">{{ number_format($employee->gross_salary, 2) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Joining Date</td>
                                        <td class="fw-medium">{{ optional($employee->joining_date)->format('d M Y') }}
                                        </td>
                                    </tr>
                                </table>

                                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3 mt-4"><i
                                        class="bi bi-house-heart me-2"></i>Personal Details</h6>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="text-muted w-50">Date of Birth</td>
                                        <td class="fw-medium">{{ optional($employee->dob)->format('d M Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Blood Group</td>
                                        <td class="fw-medium">{{ $employee->blood_group ?? '--' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Father/Spouse Name</td>
                                        <td class="fw-medium">{{ $employee->father_spouse_name ?? '--' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Alt. Mobile</td>
                                        <td class="fw-medium">{{ $employee->alternative_mobile_number ?? '--' }}
                                            <br><small
                                                class="text-muted">({{ $employee->relation_with_alt_mobile ?? 'N/A' }})</small>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="dashboard-card shadow-sm border-0 h-100">
                            <div class="card-body p-4">
                                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i
                                        class="bi bi-geo-alt me-2"></i>Address Details</h6>
                                <p class="mb-1 text-muted small">Present Address</p>
                                <p class="fw-medium mb-3">{{ $employee->present_address }}</p>
                                <p class="mb-1 text-muted small">Permanent Address</p>
                                <p class="fw-medium mb-0">{{ $employee->permanent_address }}</p>

                                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3 mt-4"><i
                                        class="bi bi-bank me-2"></i>Bank Details</h6>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="text-muted w-50">Bank Name</td>
                                        <td class="fw-medium">{{ $employee->bank_name ?? '--' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Account Number</td>
                                        <td class="fw-medium">{{ $employee->bank_account_number ?? '--' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">IFSC Code</td>
                                        <td class="fw-medium">{{ $employee->ifsc_code ?? '--' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Account Type</td>
                                        <td class="fw-medium">{{ $employee->account_type ?? '--' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="dashboard-card shadow-sm border-0">
                            <div class="card-body p-4">
                                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i
                                        class="bi bi-file-earmark-text me-2"></i>Statutory & Documents</h6>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <p class="mb-1 text-muted small">Aadhaar Number</p>
                                        <p class="fw-medium mb-1">{{ $employee->aadhaar_number }}</p>
                                        @if($employee->aadhaar_proof)
                                            <a href="{{ asset('public/' . $employee->aadhaar_proof) }}" target="_blank"
                                                class="badge bg-light text-primary border"><i class="bi bi-eye"></i> View
                                                File</a>
                                        @endif
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <p class="mb-1 text-muted small">PAN Number</p>
                                        <p class="fw-medium mb-1">{{ $employee->pan_number }}</p>
                                        @if($employee->pan_proof)
                                            <a href="{{ asset('public/' . $employee->pan_proof) }}" target="_blank"
                                                class="badge bg-light text-primary border"><i class="bi bi-eye"></i> View
                                                File</a>
                                        @endif
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <p class="mb-1 text-muted small">Highest Qualification</p>
                                        <p class="fw-medium mb-1">{{ $employee->highest_qualification ?? '--' }}</p>
                                        @if($employee->qualification_proof)
                                            <a href="{{ asset('public/' . $employee->qualification_proof) }}" target="_blank"
                                                class="badge bg-light text-primary border"><i class="bi bi-eye"></i> View
                                                File</a>
                                        @endif
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <p class="mb-1 text-muted small">UAN & ESI</p>
                                        <p class="fw-medium mb-0">UAN: {{ $employee->uan_number ?? '--' }}</p>
                                        <p class="fw-medium mb-0">ESI: {{ $employee->esi_number ?? '--' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @include('client.includes.footer_links')
    </div>
</body>

</html>