<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>School HRMS Dashboard</title>
    @include('employee.includes.header_links')
    <!-- jQuery UI CSS for Datepicker -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <style>
        .skill-pill-custom {
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid #e2e8f0;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .skill-pill-custom:hover {
            background-color: #e2e8f0;
            transform: translateY(-1px);
        }
    </style>
</head>

<body>

    <div id="overlay" onclick="closeMobile()"></div>

    @include('employee.includes.sidebar')

    <div id="main">

        @if(session('success'))
            <div class="alert alert-dismissible fade show floating-alert d-flex align-items-center" role="alert"
                style="position: fixed; top: 24px; right: 24px; z-index: 9999; min-width: 320px;
                                                                       background-color: #ecfdf5; 
                                                                       border: 1px solid #a7f3d0; 
                                                                       border-left: 5px solid #059669; 
                                                                       box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1); 
                                                                       color: #064e3b; font-weight: 500; border-radius: 10px; padding: 16px 20px;">
                <i class="bi bi-check-circle-fill" style="color: #059669; font-size: 1.25rem; margin-right: 12px;"></i>
                <div style="flex-grow: 1;">{{ session('success') }}</div>
                <button type="button" class="btn-close position-static p-0 ms-3" data-bs-dismiss="alert" aria-label="Close"
                    style="font-size: 0.8rem; opacity: 0.7;"></button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="alert alert-dismissible fade show floating-alert d-flex align-items-center" role="alert"
                style="position: fixed; top: 24px; right: 24px; z-index: 9999; min-width: 320px;
                                                                       background-color: #fef2f2; 
                                                                       border: 1px solid #fecaca; 
                                                                       border-left: 5px solid #dc2626; 
                                                                       box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1); 
                                                                       color: #7f1d1d; font-weight: 500; border-radius: 10px; padding: 16px 20px;">
                <i class="bi bi-exclamation-triangle-fill"
                    style="color: #dc2626; font-size: 1.25rem; margin-right: 12px;"></i>
                <div style="flex-grow: 1;">{{ session('error') ?? $errors->first() }}</div>
                <button type="button" class="btn-close position-static p-0 ms-3" data-bs-dismiss="alert" aria-label="Close"
                    style="font-size: 0.8rem; opacity: 0.7;"></button>
            </div>
        @endif

        @include('employee.includes.header')

        <div class="content">

            <div class="container-fluid px-3 px-md-4 py-3">

                <!-- Profile Header Start -->
                <div class="bg-white rounded-3 border mb-4"
                    style="border-color:var(--border)!important;overflow:hidden;">
                    <div class="profile-cover"></div>

                    <div class="px-4 pb-3" style="background:#fff">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-3">

                            <div class="d-flex align-items-end gap-3 flex-grow-1">
                                <div class="position-relative flex-shrink-0"
                                    style="margin-top: -45px; width: 100px; height: 100px; border-radius: 50%; background: #fff; border: 4px solid #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.05); z-index: 2;">
                                    <img src="{{ $employee->photo ? asset('public/' . $employee->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($employee->employee_name) . '&background=1a4d8f&color=ffffff&size=128' }}"
                                        alt="{{ $employee->employee_name }}"
                                        style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" />
                                    <span
                                        style="position: absolute; bottom: 5px; right: 5px; width: 16px; height: 16px; background-color: #22c55e; border: 2px solid #fff; border-radius: 50%;"></span>
                                </div>

                                <div class="pb-1">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <h4 class="mb-0 fw-bold text-dark" style="letter-spacing: -0.5px;">
                                            {{ $employee->employee_name }}
                                        </h4>
                                        <span class="tag tag-purple" style="font-size:.65rem; padding: 3px 8px;">
                                            {{ now()->isWeekend() ? 'WEEKLY OFF' : 'ACTIVE' }}
                                        </span>
                                    </div>
                                    <div style="font-size:.85rem; color: #64748b; margin-top: 4px; font-weight: 500;">
                                        <i class="bi bi-display me-1"></i>{{ $employee->designation }}
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 pb-1 flex-shrink-0 mt-2 mt-sm-0">
                                <button class="btn-o" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </button>
                                <!-- <button class="btn-p"><i class="bi bi-printer me-1"></i>Print</button> -->
                                <button class="btn-o px-2"><i class="bi bi-three-dots-vertical"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="contact-strip flex-wrap" style="padding: 10px 24px; border-top: 1px solid #f8fafc;">
                        <span class="ci text-truncate"><i class="bi bi-envelope-fill"></i> <a
                                href="mailto:{{ $employee->email }}">{{ $employee->email }}</a></span>
                        <span class="ci text-truncate"><i class="bi bi-telephone-fill"></i>
                            {{ $employee->mobile_number }}</span>
                        <span class="ci text-truncate"><i class="bi bi-geo-alt-fill"></i>
                            {{ $employee->present_address ?? 'Not Provided' }}</span>
                        <span class="ci"><i class="bi bi-hash"></i> RL000{{ $employee->employee_id }}</span>
                    </div>

                    <div class="meta-row">
                        <div>
                            <div class="ml">Department</div>
                            <div class="mv">{{ $employee->department->department_name ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="profile-tabs">
                        <div class="ptab active" onclick="switchTab(this,'profile')">PROFILE</div>
                        <div class="ptab" onclick="switchTab(this,'documents')">DOCUMENTS</div>
                    </div>
                </div>
                <!-- Profile Header End -->

                <!-- PROFILE TAB -->
                <div id="tab-profile" class="tab-pane-custom show">
                    <!-- <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="num" style="font-size: 1.25rem;">
                                    @php
                                        if ($employee->joining_date) {
                                            $diff = \Carbon\Carbon::parse($employee->joining_date)->diff(now());
                                            if ($diff->y >= 1) {
                                                echo $diff->y . ' Yr' . ($diff->y > 1 ? 's' : '');
                                            } else {
                                                $parts = [];
                                                if ($diff->m > 0)
                                                    $parts[] = $diff->m . ' Mo' . ($diff->m > 1 ? 's' : '');
                                                if ($diff->d > 0 || empty($parts))
                                                    $parts[] = $diff->d . ' Day' . ($diff->d != 1 ? 's' : '');
                                                echo implode(' ', $parts);
                                            }
                                        } else {
                                            echo '0 Days';
                                        }
                                    @endphp
                                </div>
                                <div class="lbl">Length of Service</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="num">{{ $attendanceRate }}%</div>
                                <div class="lbl">Attendance Rate (YTD)</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="num">4.2</div>
                                <div class="lbl">Appraisal Score</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-card">
                                <div class="num">{{ $leavesUsed }}</div>
                                <div class="lbl">Leaves Used</div>
                            </div>
                        </div>
                    </div> -->

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="section-card h-100 d-flex flex-column">
                                <div class="section-title border-bottom pb-3 mb-2">
                                    <i class="bi bi-briefcase-fill text-primary me-2"></i>Employment Information
                                </div>

                                <!-- flex-grow-1 and justify-content-between stretch the rows evenly to fill the card height -->
                                <div class="flex-grow-1 d-flex flex-column justify-content-between">

                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom"
                                        style="border-style: dashed !important; border-color: #e2e8f0 !important;">
                                        <span class="text-muted small fw-bold d-flex align-items-center">
                                            <div
                                                style="width: 28px; height: 28px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                                <i class="bi bi-hash text-secondary"></i>
                                            </div>
                                            Employee ID
                                        </span>
                                        <span class="text-dark fw-semibold"
                                            style="font-size: 0.85rem;">RL000{{ $employee->employee_id }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom"
                                        style="border-style: dashed !important; border-color: #e2e8f0 !important;">
                                        <span class="text-muted small fw-bold d-flex align-items-center">
                                            <div
                                                style="width: 28px; height: 28px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                                <i class="bi bi-person-workspace text-secondary"></i>
                                            </div>
                                            Designation
                                        </span>
                                        <span class="text-dark fw-semibold"
                                            style="font-size: 0.85rem;">{{ $employee->designation }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom"
                                        style="border-style: dashed !important; border-color: #e2e8f0 !important;">
                                        <span class="text-muted small fw-bold d-flex align-items-center">
                                            <div
                                                style="width: 28px; height: 28px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                                <i class="bi bi-diagram-3 text-secondary"></i>
                                            </div>
                                            Department
                                        </span>
                                        <span class="text-dark fw-semibold"
                                            style="font-size: 0.85rem;">{{ $employee->department->department_name ?? 'N/A' }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom"
                                        style="border-style: dashed !important; border-color: #e2e8f0 !important;">
                                        <span class="text-muted small fw-bold d-flex align-items-center">
                                            <div
                                                style="width: 28px; height: 28px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                                <i class="bi bi-calendar-check text-secondary"></i>
                                            </div>
                                            Date of Joining
                                        </span>
                                        <span class="text-dark fw-semibold"
                                            style="font-size: 0.85rem;">{{ $employee->joining_date ? $employee->joining_date->format('d M Y') : 'N/A' }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom"
                                        style="border-style: dashed !important; border-color: #e2e8f0 !important;">
                                        <span class="text-muted small fw-bold d-flex align-items-center">
                                            <div
                                                style="width: 28px; height: 28px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                                <i class="bi bi-clock-history text-secondary"></i>
                                            </div>
                                            Length of Service
                                        </span>
                                        <span class="text-dark fw-semibold" style="font-size: 0.85rem;">
                                            @php
                                                if ($employee->joining_date) {
                                                    $diff = \Carbon\Carbon::parse($employee->joining_date)->diff(now());
                                                    if ($diff->y >= 1) {
                                                        echo $diff->y . ' Yr' . ($diff->y > 1 ? 's' : '');
                                                    } else {
                                                        $parts = [];
                                                        if ($diff->m > 0)
                                                            $parts[] = $diff->m . ' Mo' . ($diff->m > 1 ? 's' : '');
                                                        if ($diff->d > 0 || empty($parts))
                                                            $parts[] = $diff->d . ' Day' . ($diff->d != 1 ? 's' : '');
                                                        echo implode(' ', $parts);
                                                    }
                                                } else {
                                                    echo '0 Days';
                                                }
                                            @endphp
                                        </span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom"
                                        style="border-style: dashed !important; border-color: #e2e8f0 !important;">
                                        <span class="text-muted small fw-bold d-flex align-items-center">
                                            <div
                                                style="width: 28px; height: 28px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                                <i class="bi bi-geo-alt text-secondary"></i>
                                            </div>
                                            Work Location
                                        </span>
                                        <span class="text-dark fw-semibold" style="font-size: 0.85rem;">HQ</span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom"
                                        style="border-style: dashed !important; border-color: #e2e8f0 !important;">
                                        <span class="text-muted small fw-bold d-flex align-items-center">
                                            <div
                                                style="width: 28px; height: 28px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                                <i class="bi bi-shield-lock text-secondary"></i>
                                            </div>
                                            UAN Account
                                        </span>
                                        <span class="text-dark fw-semibold"
                                            style="font-size: 0.85rem;">{{ $employee->uan_number ?? 'Not Available' }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom"
                                        style="border-style: dashed !important; border-color: #e2e8f0 !important;">
                                        <span class="text-muted small fw-bold d-flex align-items-center">
                                            <div
                                                style="width: 28px; height: 28px; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                                <i class="bi bi-bank text-secondary"></i>
                                            </div>
                                            Bank A/C
                                        </span>
                                        <span class="text-dark fw-semibold text-end"
                                            style="font-size: 0.85rem;">{{ $employee->bank_name ?? 'N/A' }} <br>
                                            {{ $employee->bank_account_number ?? '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">

                            <div class="section-card mb-3">
                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                    <div class="section-title"><i class="bi bi-star-fill me-2"></i>
                                        Skills &amp; Expertise</div>
                                    <button class="btn btn-sm btn-light border" data-bs-toggle="modal"
                                        data-bs-target="#addSkillModal" title="Add New Skill">
                                        <i class="bi bi-plus-lg text-primary"></i> Add Skill
                                    </button>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse($employee->skills as $skill)
                                        <div class="skill-pill-custom" id="skill_pill_{{ $skill->id }}"
                                            title="Click to edit"
                                            onclick="openEditSkillModal({{ $skill->id }}, '{{ addslashes($skill->name) }}')">
                                            {{ $skill->name }} <i class="bi bi-pencil-fill ms-1"
                                                style="font-size: 0.65rem; opacity: 0.5;"></i>
                                        </div>
                                    @empty
                                        <span class="text-muted small w-100">No skills added yet. Click 'Add Skill' to
                                            update your profile.</span>
                                    @endforelse

                                    @if($employee->highest_qualification)
                                        <div class="skill-pill-custom bg-white border text-secondary"
                                            style="border-style: dashed !important; cursor: default;">
                                            <i class="bi bi-mortarboard"></i> {{ $employee->highest_qualification }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="section-card">
                                <div class="section-title"><i class="bi bi-calendar2-check-fill"></i>Leave Balance
                                    {{ date('Y') }}
                                </div>
                                @php $leavePercentage = $totalYearlyPaidLeaves > 0 ? round(($leavesUsed / $totalYearlyPaidLeaves) * 100) : 0; @endphp
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="leave-type">Overall Paid Leaves</span>
                                        <span class="leave-val">{{ $leavesUsed }} / {{ $totalYearlyPaidLeaves }}</span>
                                    </div>
                                    <div class="progress" style="height:6px;border-radius:10px">
                                        <div class="progress-bar"
                                            style="width:{{ $leavePercentage }}%;background:var(--primary)"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="leave-type">Medical Leave</span>
                                        <span class="leave-val">0 / 0</span>
                                    </div>
                                    <div class="progress" style="height:6px;border-radius:10px">
                                        <div class="progress-bar" style="width:0%;background:#0ea5e9"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="leave-type">Earned Leave</span>
                                        <span class="leave-val">0 / 0</span>
                                    </div>
                                    <div class="progress" style="height:6px;border-radius:10px">
                                        <div class="progress-bar" style="width:0%;background:#f59e0b"></div>
                                    </div>
                                </div>
                                <hr class="my-2" />
                                <div class="d-flex justify-content-between align-items-center">
                                    <span style="font-size:.82rem;font-weight:600">Total Absent (YTD)</span>
                                    <span class="tag tag-amber">{{ $totalAbsentYTD }} Days</span>
                                </div>
                                <!-- <div class="mt-3">
                                    <a href="{{ route('employee.apply_leave') }}"
                                        class="btn-o w-100 d-block text-center" style="text-decoration: none;">
                                        <i class="bi bi-calendar2-plus me-1"></i>Apply Leave
                                    </a>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DOCUMENTS TAB -->
                <div id="tab-documents" class="tab-pane-custom">

                    <div class="mb-4">
                        <h5 class="fw-bold mb-0">Document Center</h5>
                        <div style="font-size:.85rem;color:var(--muted)">Manage your personal, educational, and
                            professional verifications.</div>
                    </div>

                    <div class="section-card mb-4">
                        <div class="section-title text-primary border-bottom pb-2 mb-3"><i
                                class="bi bi-person-vcard me-2"></i>Personal Documents</div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <div
                                    class="border rounded p-3 bg-light-subtle h-100 d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            style="background:#d1fae5; width:45px; height:45px; display:flex; align-items:center; justify-content:center; border-radius:10px;">
                                            <i class="bi bi-card-image" style="color:#065f46; font-size:1.2rem;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark" style="font-size:0.9rem;">Aadhaar Card <span
                                                    class="text-muted fw-normal"
                                                    style="font-size:0.75rem;"></span></div>
                                            <div class="mt-1" style="font-size:0.8rem;">
                                                @if($employee->aadhaar_proof) <span class="text-success fw-bold"><i
                                                    class="bi bi-check-circle-fill me-1"></i>Uploaded</span>
                                                @else <span class="text-warning fw-bold"><i
                                                class="bi bi-exclamation-circle me-1"></i>Pending</span> @endif
                                            </div>
                                        </div>
                                        <div>
                                            @if($employee->aadhaar_proof)
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        title="View Document"
                                                        onclick="viewDocument('{{ asset('public/' . $employee->aadhaar_proof) }}', 'Aadhaar Card')">
                                                        <i class="bi bi-eye text-primary"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        title="Update Document"
                                                        onclick="openUploadModal('aadhaar_proof', 'Aadhaar Card')">
                                                        <i class="bi bi-pencil-square text-warning"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <button type="button" class="btn btn-sm btn-primary"
                                                    onclick="openUploadModal('aadhaar_proof', 'Aadhaar Card')"><i
                                                        class="bi bi-upload"></i></button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <div
                                    class="border rounded p-3 bg-light-subtle h-100 d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            style="background:#e0f2fe; width:45px; height:45px; display:flex; align-items:center; justify-content:center; border-radius:10px;">
                                            <i class="bi bi-credit-card" style="color:#0284c7; font-size:1.2rem;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark" style="font-size:0.9rem;">PAN Card <span
                                                    class="text-muted fw-normal"
                                                    style="font-size:0.75rem;"></span></div>
                                            <div class="mt-1" style="font-size:0.8rem;">
                                                @if($employee->pan_proof) <span class="text-success fw-bold"><i
                                                    class="bi bi-check-circle-fill me-1"></i>Uploaded</span>
                                                @else <span class="text-warning fw-bold"><i
                                                class="bi bi-exclamation-circle me-1"></i>Pending</span> @endif
                                            </div>
                                        </div>
                                        <div>
                                            @if($employee->pan_proof)
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        title="View Document"
                                                        onclick="viewDocument('{{ asset('public/' . $employee->pan_proof) }}', 'PAN Card')">
                                                        <i class="bi bi-eye text-primary"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        title="Update Document"
                                                        onclick="openUploadModal('pan_proof', 'PAN Card')">
                                                        <i class="bi bi-pencil-square text-warning"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <button type="button" class="btn btn-sm btn-primary"
                                                    onclick="openUploadModal('pan_proof', 'PAN Card')"><i
                                                        class="bi bi-upload"></i></button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @php $digiSig = $newDocuments->get('digital_signature'); @endphp
                            <div class="col-md-6 col-lg-4">
                                <div
                                    class="border rounded p-3 bg-light-subtle h-100 d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            style="background:#f3e8ff; width:45px; height:45px; display:flex; align-items:center; justify-content:center; border-radius:10px;">
                                            <i class="bi bi-pen" style="color:#7e22ce; font-size:1.2rem;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark" style="font-size:0.9rem;">Digital Signature
                                                <span class="text-muted fw-normal"
                                                    style="font-size:0.75rem;"></span>
                                            </div>
                                            <div class="mt-1" style="font-size:0.8rem;">
                                                @if($digiSig) <span class="text-success fw-bold"><i
                                                    class="bi bi-check-circle-fill me-1"></i>Uploaded</span>
                                                @else <span class="text-warning fw-bold"><i
                                                class="bi bi-exclamation-circle me-1"></i>Pending</span> @endif
                                            </div>
                                        </div>
                                        <div>
                                            @if($digiSig)
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        title="View Document"
                                                        onclick="viewDocument('{{ asset('public/' . $digiSig->file) }}', 'Digital Signature')">
                                                        <i class="bi bi-eye text-primary"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        title="Update Document"
                                                        onclick="openUploadModal('digital_signature', 'Digital Signature')">
                                                        <i class="bi bi-pencil-square text-warning"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <button type="button" class="btn btn-sm btn-primary"
                                                    onclick="openUploadModal('digital_signature', 'Digital Signature')"><i
                                                        class="bi bi-upload"></i></button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-card mb-4">
                        <div class="section-title text-primary border-bottom pb-2 mb-3"><i
                                class="bi bi-mortarboard me-2"></i>Educational Documents</div>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-4">
                                <div
                                    class="border rounded p-3 bg-light-subtle h-100 d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            style="background:#fef3c7; width:45px; height:45px; display:flex; align-items:center; justify-content:center; border-radius:10px;">
                                            <i class="bi bi-journal-check" style="color:#d97706; font-size:1.2rem;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark" style="font-size:0.9rem;">Highest Qual. <span
                                                    class="text-muted fw-normal"
                                                    style="font-size:0.75rem;"></span></div>
                                            <div class="mt-1" style="font-size:0.8rem;">
                                                @if($employee->qualification_proof) <span
                                                    class="text-success fw-bold"><i
                                                        class="bi bi-check-circle-fill me-1"></i>Uploaded</span>
                                                @else <span class="text-warning fw-bold"><i
                                                class="bi bi-exclamation-circle me-1"></i>Pending</span> @endif
                                            </div>
                                        </div>
                                        <div>
                                            @if($employee->qualification_proof)
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        title="View Document"
                                                        onclick="viewDocument('{{ asset('public/' . $employee->qualification_proof) }}', 'Highest Qualification')">
                                                        <i class="bi bi-eye text-primary"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        title="Update Document"
                                                        onclick="openUploadModal('qualification_proof', 'Highest Qualification')">
                                                        <i class="bi bi-pencil-square text-warning"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <button type="button" class="btn btn-sm btn-primary"
                                                    onclick="openUploadModal('qualification_proof', 'Highest Qualification')"><i
                                                        class="bi bi-upload"></i></button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @php $sscMemo = $newDocuments->get('ssc_memo'); @endphp
                            <div class="col-md-6 col-lg-4">
                                <div
                                    class="border rounded p-3 bg-light-subtle h-100 d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            style="background:#fef3c7; width:45px; height:45px; display:flex; align-items:center; justify-content:center; border-radius:10px;">
                                            <i class="bi bi-file-earmark-text"
                                                style="color:#d97706; font-size:1.2rem;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark" style="font-size:0.9rem;">SSC Memo <span
                                                    class="text-muted fw-normal"
                                                    style="font-size:0.75rem;"></span></div>
                                            <div class="mt-1" style="font-size:0.8rem;">
                                                @if($sscMemo) <span class="text-success fw-bold"><i
                                                    class="bi bi-check-circle-fill me-1"></i>Uploaded</span>
                                                @else <span class="text-warning fw-bold"><i
                                                class="bi bi-exclamation-circle me-1"></i>Pending</span> @endif
                                            </div>
                                        </div>
                                        <div>
                                            @if($sscMemo)
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        title="View Document"
                                                        onclick="viewDocument('{{ asset('public/' . $sscMemo->file) }}', 'SSC Memo')">
                                                        <i class="bi bi-eye text-primary"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        title="Update Document"
                                                        onclick="openUploadModal('ssc_memo', 'SSC Memo')">
                                                        <i class="bi bi-pencil-square text-warning"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <button type="button" class="btn btn-sm btn-primary"
                                                    onclick="openUploadModal('ssc_memo', 'SSC Memo')"><i
                                                        class="bi bi-upload"></i></button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @php $interMemo = $newDocuments->get('intermediate'); @endphp
                            <div class="col-md-6 col-lg-4">
                                <div
                                    class="border rounded p-3 bg-light-subtle h-100 d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            style="background:#fef3c7; width:45px; height:45px; display:flex; align-items:center; justify-content:center; border-radius:10px;">
                                            <i class="bi bi-file-earmark-ruled"
                                                style="color:#d97706; font-size:1.2rem;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark" style="font-size:0.9rem;">Intermediate/Dip.
                                                <span class="text-muted fw-normal"
                                                    style="font-size:0.75rem;"></span>
                                            </div>
                                            <div class="mt-1" style="font-size:0.8rem;">
                                                @if($interMemo) <span class="text-success fw-bold"><i
                                                    class="bi bi-check-circle-fill me-1"></i>Uploaded</span>
                                                @else <span class="text-warning fw-bold"><i
                                                class="bi bi-exclamation-circle me-1"></i>Pending</span> @endif
                                            </div>
                                        </div>
                                        <div>
                                            @if($interMemo)
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        title="View Document"
                                                        onclick="viewDocument('{{ asset('public/' . $interMemo->file) }}', 'Intermediate or Diploma')">
                                                        <i class="bi bi-eye text-primary"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        title="Update Document"
                                                        onclick="openUploadModal('intermediate', 'Intermediate or Diploma')">
                                                        <i class="bi bi-pencil-square text-warning"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <button type="button" class="btn btn-sm btn-primary"
                                                    onclick="openUploadModal('intermediate', 'Intermediate or Diploma')"><i
                                                        class="bi bi-upload"></i></button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <div class="section-title text-primary border-bottom pb-2 mb-3"><i
                                class="bi bi-bank me-2"></i>Professional & Financial</div>
                        <div class="row g-3">
                            @php $bankProof = $newDocuments->get('bank_passbook'); @endphp
                            <div class="col-md-6 col-lg-4">
                                <div
                                    class="border rounded p-3 bg-light-subtle h-100 d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div
                                            style="background:#e0e7ff; width:45px; height:45px; display:flex; align-items:center; justify-content:center; border-radius:10px;">
                                            <i class="bi bi-cash-stack" style="color:#4338ca; font-size:1.2rem;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark" style="font-size:0.9rem;">Bank Passbook <span
                                                    class="text-muted fw-normal"
                                                    style="font-size:0.75rem;"></span></div>
                                            <div class="mt-1" style="font-size:0.8rem;">
                                                @if($bankProof) <span class="text-success fw-bold"><i
                                                    class="bi bi-check-circle-fill me-1"></i>Uploaded</span>
                                                @else <span class="text-warning fw-bold"><i
                                                class="bi bi-exclamation-circle me-1"></i>Pending</span> @endif
                                            </div>
                                        </div>
                                        <div>
                                            @if($bankProof)
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        title="View Document"
                                                        onclick="viewDocument('{{ asset('public/' . $bankProof->file) }}', 'Bank Passbook / Cancelled Cheque')">
                                                        <i class="bi bi-eye text-primary"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-light border"
                                                        title="Update Document"
                                                        onclick="openUploadModal('bank_passbook', 'Bank Passbook / Cancelled Cheque')">
                                                        <i class="bi bi-pencil-square text-warning"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <button type="button" class="btn btn-sm btn-primary"
                                                    onclick="openUploadModal('bank_passbook', 'Bank Passbook / Cancelled Cheque')"><i
                                                        class="bi bi-upload"></i></button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <script>
                function switchTab(el, id) {
                    document.querySelectorAll('.ptab').forEach(t => t.classList.remove('active'));
                    el.classList.add('active');
                    document.querySelectorAll('.tab-pane-custom').forEach(p => p.classList.remove('show'));
                    document.getElementById('tab-' + id).classList.add('show');
                }
            </script>
        </div>
    </div>

    <!-- Targeted Document Upload Modal -->
    <div class="modal fade" id="uploadDocModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                    <h6 class="modal-title fw-bold" id="uploadDocModalLabel">Upload Document</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('employee.upload_document') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="document_type" id="doc_type_hidden">

                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label"
                                style="font-size: 0.85rem; font-weight: 600; color: #475569;">Select File <span
                                    class="text-danger">*</span></label>
                            <input type="file" name="document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png"
                                required style="border-color: var(--border); border-radius: 8px;">
                            <div class="form-text mt-2" style="font-size: 0.75rem; color: var(--muted);">
                                Accepted formats: PDF, JPG, PNG. Maximum size: 2MB.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"
                        style="border-top: 1px solid var(--border); background: #f8fafc; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                        <button type="button" class="btn-o" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-p"><i class="bi bi-cloud-arrow-up me-1"></i>Upload
                            Securely</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Document Modal -->
    <div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow" style="border-radius: 12px; height: 90vh;">
                <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                    <h6 class="modal-title fw-bold" id="viewDocumentModalLabel">View Document</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: calc(100% - 56px);">
                    <iframe id="documentViewerFrame" src=""
                        style="width: 100%; height: 100%; border: none; border-radius: 0 0 12px 12px;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- ADD SKILLS MODAL -->
    <div class="modal fade" id="addSkillModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                    <h6 class="modal-title fw-bold"><i class="bi bi-award"></i>
                        Add Skills & Expertise</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('employee.manage_skills') ?? '#' }}" method="POST">
                    @csrf
                    <div class="modal-body p-4" style="max-height: 60vh; overflow-y: auto;">
                        <div class="alert alert-info py-2" style="font-size: 0.85rem; border-radius: 8px;">
                            <i class="bi bi-info-circle-fill me-2"></i>Highlight your capabilities. Add multiple skills
                            by clicking the <strong>+</strong> button.
                        </div>

                        <div id="dynamicSkillsContainer">
                            <div class="row g-3 mb-2 skill-row align-items-center">
                                <div class="col-10">
                                    <label class="form-label small fw-bold mb-1">Skill Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="skill_name[]" class="form-control" required
                                        placeholder="e.g., PHP, Laravel, Project Management">
                                </div>
                                <div class="col-2 mt-4 pt-1 text-end">
                                    <button type="button" class="btn btn-sm btn-primary" id="addSkillRowBtn"
                                        title="Add Another Skill">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"
                        style="border-top: 1px solid var(--border); background: #f8fafc; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                        <button type="button" class="btn-o" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-p"><i class="bi bi-save me-1"></i>Save Skills</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT/DELETE SKILL MODAL -->
    <div class="modal fade" id="editSkillModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                    <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Skill</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('employee.update_skill') }}" method="POST">
                    @csrf
                    <input type="hidden" name="skill_id" id="edit_skill_id">

                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Skill Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="edit_skill_name" id="edit_skill_name" class="form-control"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between"
                        style="border-top: 1px solid var(--border); background: #f8fafc; border-radius: 0 0 12px 12px;">
                        <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Are you sure you want to remove this skill?')">
                            <i class="bi bi-trash me-1"></i>Remove
                        </button>
                        <div>
                            <button type="button" class="btn-o" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="action" value="update" class="btn-p"><i
                                    class="bi bi-save me-1"></i>Update Skill</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow" style="border-radius: 12px;">
                <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                    <h6 class="modal-title fw-bold" id="editProfileModalLabel">Edit Profile Information</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('employee.update_profile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">

                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2"><i
                                class="bi bi-person-badge me-2"></i>Employment Information (View Only)</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Employee ID</label>
                                <input type="text" class="form-control" value="RL000{{ $employee->employee_id }}"
                                    disabled style="background-color: #f1f5f9;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Department</label>
                                <input type="text" class="form-control"
                                    value="{{ $employee->department->department_name ?? 'N/A' }}" disabled
                                    style="background-color: #f1f5f9;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Designation</label>
                                <input type="text" class="form-control" value="{{ $employee->designation }}" disabled
                                    style="background-color: #f1f5f9;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-muted">Joining Date</label>
                                <input type="text" class="form-control"
                                    value="{{ $employee->joining_date ? $employee->joining_date->format('d M Y') : 'N/A' }}"
                                    disabled style="background-color: #f1f5f9;">
                            </div>
                        </div>

                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2"><i class="bi bi-person me-2"></i>Basic
                            Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Full Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="employee_name"
                                    class="form-control @error('employee_name') is-invalid @enderror"
                                    value="{{ old('employee_name', $employee->employee_name) }}" required>
                                @error('employee_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Email Address</label>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $employee->email) }}">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Mobile Number <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="mobile_number"
                                    class="form-control @error('mobile_number') is-invalid @enderror"
                                    value="{{ old('mobile_number', $employee->mobile_number) }}" maxlength="10"
                                    required>
                                @error('mobile_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Gender <span
                                        class="text-danger">*</span></label>
                                <select name="gender" class="form-select @error('gender') is-invalid @enderror"
                                    required>
                                    <option value="1" {{ old('gender', $employee->gender) == 1 ? 'selected' : '' }}>Male
                                    </option>
                                    <option value="2" {{ old('gender', $employee->gender) == 2 ? 'selected' : '' }}>Female
                                    </option>
                                </select>
                                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Profile Photo</label>
                                <input type="file" name="photo"
                                    class="form-control @error('photo') is-invalid @enderror" accept=".jpg,.jpeg,.png">
                                <div class="form-text small text-muted">Leave empty to keep the current photo. Max size:
                                    2MB.</div>
                                @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 mt-4"><i
                                class="bi bi-house-heart me-2"></i>Personal & Family Details</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Date of Birth <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="dob"
                                    class="form-control datepicker @error('dob') is-invalid @enderror"
                                    value="{{ old('dob', $employee->dob ? $employee->dob->format('Y-m-d') : '') }}"
                                    required autocomplete="off" placeholder="YYYY-MM-DD">
                                @error('dob') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Father / Spouse Name</label>
                                <input type="text" name="father_spouse_name"
                                    class="form-control @error('father_spouse_name') is-invalid @enderror"
                                    value="{{ old('father_spouse_name', $employee->father_spouse_name) }}">
                                @error('father_spouse_name') <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Mother's Name</label>
                                <input type="text" name="mother_name"
                                    class="form-control @error('mother_name') is-invalid @enderror"
                                    value="{{ old('mother_name', $employee->mother_name) }}">
                                @error('mother_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Blood Group</label>
                                <select name="blood_group"
                                    class="form-select @error('blood_group') is-invalid @enderror">
                                    <option value="" disabled {{ empty($employee->blood_group) ? 'selected' : '' }}>
                                        Select Group</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                        <option value="{{ $bg }}" {{ old('blood_group', $employee->blood_group) == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                    @endforeach
                                </select>
                                @error('blood_group') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Alt. Mobile Number</label>
                                <input type="text" name="alternative_mobile_number" id="alt_mobile_emp"
                                    class="form-control @error('alternative_mobile_number') is-invalid @enderror"
                                    value="{{ old('alternative_mobile_number', $employee->alternative_mobile_number) }}"
                                    maxlength="10" oninput="toggleRelationFieldEmp()">
                                @error('alternative_mobile_number') <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 d-none" id="relation_div_emp">
                                <label class="form-label small fw-bold">Relation with Alt. No</label>
                                <input type="text" name="relation_with_alt_mobile"
                                    class="form-control @error('relation_with_alt_mobile') is-invalid @enderror"
                                    value="{{ old('relation_with_alt_mobile', $employee->relation_with_alt_mobile) }}">
                                @error('relation_with_alt_mobile') <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Marital Status <span
                                        class="text-danger">*</span></label>
                                <select name="marital_status" id="marital_status_emp"
                                    class="form-select @error('marital_status') is-invalid @enderror" required
                                    onchange="toggleMarriageDateEmp()">
                                    <option value="2" {{ old('marital_status', $employee->marital_status) == 2 ? 'selected' : '' }}>Single / Unmarried</option>
                                    <option value="1" {{ old('marital_status', $employee->marital_status) == 1 ? 'selected' : '' }}>Married</option>
                                </select>
                                @error('marital_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3 d-none" id="marriage_date_div_emp">
                                <label class="form-label small fw-bold">Marriage Date</label>
                                <input type="text" name="marriage_date"
                                    class="form-control datepicker @error('marriage_date') is-invalid @enderror"
                                    value="{{ old('marriage_date', $employee->marriage_date ? $employee->marriage_date->format('Y-m-d') : '') }}"
                                    autocomplete="off" placeholder="YYYY-MM-DD">
                                @error('marriage_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Present Address</label>
                                <textarea name="present_address" id="present_address"
                                    class="form-control @error('present_address') is-invalid @enderror"
                                    rows="3">{{ old('present_address', $employee->present_address) }}</textarea>
                                @error('present_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold mb-0">Permanent Address</label>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" id="same_as_present">
                                        <label class="form-check-label small" for="same_as_present"
                                            style="font-size: 0.75rem;">Same as Present</label>
                                    </div>
                                </div>
                                <textarea name="permanent_address" id="permanent_address"
                                    class="form-control @error('permanent_address') is-invalid @enderror"
                                    rows="3">{{ old('permanent_address', $employee->permanent_address) }}</textarea>
                                @error('permanent_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 mt-4"><i
                                class="bi bi-file-earmark-text me-2"></i>Statutory & Educational</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Aadhaar Number</label>
                                <input type="text" name="aadhaar_number"
                                    class="form-control @error('aadhaar_number') is-invalid @enderror"
                                    value="{{ old('aadhaar_number', $employee->aadhaar_number) }}" maxlength="12">
                                @error('aadhaar_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">PAN Number</label>
                                <input type="text" name="pan_number"
                                    class="form-control @error('pan_number') is-invalid @enderror"
                                    value="{{ old('pan_number', $employee->pan_number) }}" maxlength="10">
                                @error('pan_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">UAN Number</label>
                                <input type="text" name="uan_number"
                                    class="form-control @error('uan_number') is-invalid @enderror"
                                    value="{{ old('uan_number', $employee->uan_number) }}" maxlength="12">
                                @error('uan_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">ESI Number</label>
                                <input type="text" name="esi_number"
                                    class="form-control @error('esi_number') is-invalid @enderror"
                                    value="{{ old('esi_number', $employee->esi_number) }}" maxlength="17">
                                @error('esi_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Highest Qualification</label>
                                <input type="text" name="highest_qualification"
                                    class="form-control @error('highest_qualification') is-invalid @enderror"
                                    value="{{ old('highest_qualification', $employee->highest_qualification) }}">
                                @error('highest_qualification') <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 mt-4"><i
                                class="bi bi-bank me-2"></i>Bank Account Details</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Bank Name</label>
                                <input type="text" name="bank_name"
                                    class="form-control @error('bank_name') is-invalid @enderror"
                                    value="{{ old('bank_name', $employee->bank_name) }}">
                                @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Account Number</label>
                                <input type="text" name="bank_account_number"
                                    class="form-control @error('bank_account_number') is-invalid @enderror"
                                    value="{{ old('bank_account_number', $employee->bank_account_number) }}">
                                @error('bank_account_number') <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">IFSC Code</label>
                                <input type="text" name="ifsc_code"
                                    class="form-control @error('ifsc_code') is-invalid @enderror"
                                    value="{{ old('ifsc_code', $employee->ifsc_code) }}">
                                @error('ifsc_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Account Type</label>
                                <select name="account_type"
                                    class="form-select @error('account_type') is-invalid @enderror">
                                    <option value="" selected disabled>Select Type</option>
                                    <option value="Savings" {{ old('account_type', $employee->account_type) == 'Savings' ? 'selected' : '' }}>Savings</option>
                                    <option value="Current" {{ old('account_type', $employee->account_type) == 'Current' ? 'selected' : '' }}>Current</option>
                                </select>
                                @error('account_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"
                        style="border-top: 1px solid var(--border); background: #f8fafc; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                        <button type="button" class="btn-o" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-p"><i class="bi bi-save me-1"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('employee.includes.footer_links')

    <!-- IMPORTANT: Include jQuery Core BEFORE jQuery UI to fix "$ is not defined" error -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- jQuery UI JS for Datepicker -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script>
        $(function () {
            // Fixes accessibility warning on modal close
            $('.modal').on('hidden.bs.modal', function () {
                $(this).find('.btn-close').blur();
            });

            $(".datepicker").datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                yearRange: "-100:+10"
            });

            // Address Copy Logic
            $('#same_as_present').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#permanent_address').val($('#present_address').val());
                    $('#permanent_address').prop('readonly', true);
                } else {
                    $('#permanent_address').prop('readonly', false);
                }
            });
            $('#present_address').on('input', function () {
                if ($('#same_as_present').is(':checked')) {
                    $('#permanent_address').val($(this).val());
                }
            });
        });

        function openUploadModal(docType, docTitle) {
            document.getElementById('doc_type_hidden').value = docType;
            document.getElementById('uploadDocModalLabel').innerText = 'Upload / Update ' + docTitle;
            if (typeof bootstrap !== 'undefined') {
                var uploadModal = new bootstrap.Modal(document.getElementById('uploadDocModal'));
                uploadModal.show();
            }
        }

        // --- IN-PAGE DOCUMENT VIEWER ---
        function viewDocument(url, title) {
            document.getElementById('viewDocumentModalLabel').innerText = title;
            document.getElementById('documentViewerFrame').src = url;
            if (typeof bootstrap !== 'undefined') {
                var viewModal = new bootstrap.Modal(document.getElementById('viewDocumentModal'));
                viewModal.show();
            }
        }

        // Clean up iframe on modal close
        document.getElementById('viewDocumentModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('documentViewerFrame').src = '';
        });

        function openEditSkillModal(id, name) {
            document.getElementById('edit_skill_id').value = id;
            document.getElementById('edit_skill_name').value = name;

            if (typeof bootstrap !== 'undefined') {
                var editSkillModal = new bootstrap.Modal(document.getElementById('editSkillModal'));
                editSkillModal.show();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const addSkillBtn = document.getElementById('addSkillRowBtn');
            if (addSkillBtn) {
                addSkillBtn.addEventListener('click', function () {
                    const container = document.getElementById('dynamicSkillsContainer');
                    const row = document.createElement('div');
                    row.className = 'row g-3 mb-2 skill-row align-items-center';
                    row.innerHTML = `
                        <div class="col-10">
                            <input type="text" name="skill_name[]" class="form-control" required placeholder="e.g., PHP, Laravel, Project Management">
                        </div>
                        <div class="col-2 text-end">
                            <button type="button" class="btn btn-sm btn-danger remove-skill-row" title="Remove Skill Row">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                    container.appendChild(row);
                });
            }

            document.body.addEventListener('click', function (e) {
                if (e.target.closest('.remove-skill-row')) {
                    e.target.closest('.skill-row').remove();
                }
            });
        });

        function toggleRelationFieldEmp() {
            var altMobile = document.getElementById('alt_mobile_emp').value;
            var relationDiv = document.getElementById('relation_div_emp');
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

        function toggleMarriageDateEmp() {
            var status = document.getElementById('marital_status_emp').value;
            var div = document.getElementById('marriage_date_div_emp');
            var input = div.querySelector('input');

            if (status == 1) {
                div.classList.remove('d-none');
                input.setAttribute('required', 'required');
            } else {
                div.classList.add('d-none');
                input.removeAttribute('required');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            @if(session('active_tab') === 'documents')
                const docTabBtn = document.querySelector('.ptab[onclick*="documents"]');
                if (docTabBtn) switchTab(docTabBtn, 'documents');
            @endif

            if (document.getElementById('alt_mobile_emp')) toggleRelationFieldEmp();
            if (document.getElementById('marital_status_emp')) toggleMarriageDateEmp();

            const invalidInputs = document.querySelectorAll('.is-invalid');
            invalidInputs.forEach(input => {
                ['input', 'change'].forEach(evt => {
                    input.addEventListener(evt, function () {
                        this.classList.remove('is-invalid');
                        let feedback = this.parentNode.querySelector('.invalid-feedback');
                        if (feedback) feedback.style.display = 'none';
                    });
                });
            });

            let alerts = document.querySelectorAll('.floating-alert');
            if (alerts.length > 0) {
                setTimeout(function () {
                    alerts.forEach(function (alertNode) {
                        if (typeof bootstrap !== 'undefined') {
                            let bsAlert = new bootstrap.Alert(alertNode);
                            bsAlert.close();
                        } else {
                            alertNode.style.display = 'none';
                        }
                    });
                }, 5000);
            }

            @if($errors->any() && session('active_tab') !== 'documents')
                if (typeof bootstrap !== 'undefined') {
                    var editModalEl = document.getElementById('editProfileModal');
                    if (editModalEl) {
                        var editModal = new bootstrap.Modal(editModalEl);
                        editModal.show();
                    }
                }
            @endif
        });
    </script>
</body>

</html>