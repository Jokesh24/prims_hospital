<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Employee Roster - HRMS Dashboard</title>

    @include('client.includes.header_links')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* Custom Scrollbar for better UX */
        .roster-wrapper::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .roster-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .roster-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .roster-wrapper::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .roster-wrapper {
            overflow-x: auto;
            max-height: 70vh;
            border-radius: 10px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }

        .roster-table {
            border-collapse: separate;
            border-spacing: 0;
            min-width: 100%;
        }

        .roster-table th,
        .roster-table td {
            white-space: nowrap;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            padding: 10px 8px;
            text-align: center;
            vertical-align: middle;
            transition: background-color 0.2s ease;
        }

        .roster-table th:nth-child(1),
        .roster-table td:nth-child(1) {
            position: sticky;
            left: 0;
            z-index: 2;
            width: 50px;
            background: #f8fafc;
        }

        .roster-table th:nth-child(2),
        .roster-table td:nth-child(2) {
            position: sticky;
            left: 40px;
            z-index: 2;
            width: 240px;
            background: #ffffff;
            border-right: 2px solid #cbd5e1;
            box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.05);
        }

        .roster-table tbody tr:not(.dept-header-row):hover td:not(.bg-weekend) {
            background-color: #f1f5f9;
        }

        .roster-table tbody tr:not(.dept-header-row):hover td:nth-child(2) {
            background-color: #f8fafc;
        }

        .roster-table thead th {
            position: sticky;
            top: 0;
            background: #f1f5f9;
            z-index: 3;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            border-bottom: 2px solid #cbd5e1;
        }

        .roster-table thead th:nth-child(1),
        .roster-table thead th:nth-child(2) {
            z-index: 4;
            background: #e2e8f0;
        }

        .dept-header-row td {
            background: #1e293b !important;
            padding: 0 !important;
            z-index: 1;
        }

        .sticky-dept-name {
            position: sticky;
            left: 0;
            display: inline-block;
            padding: 10px 15px;
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            z-index: 5;
        }

        .roster-select {
            min-width: 150px;
            width: auto;
            font-size: 11px;
            padding: 5px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s ease;
            appearance: none;
            -webkit-appearance: none;
            text-align-last: center;
            font-weight: 600;
            color: #64748b;
        }

        .roster-select:hover {
            border-color: #94a3b8;
            background: #f1f5f9;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .roster-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
            background: #ffffff;
        }

        .roster-select.has-shift {
            background: #dcfce7;
            color: #166534;
            border-color: #86efac;
        }

        .roster-select.has-weekoff {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fca5a5;
        }

        .emp-name {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 3px;
            display: block;
        }

        .emp-desig {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }

        .emp-id-badge {
            font-size: 10px;
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 6px;
            color: #475569;
            font-weight: 600;
        }

        .bg-weekend {
            background-color: #fff1f2 !important;
        }

        .text-weekend {
            color: #e11d48 !important;
        }

        .custom-modal-header {
            border-bottom: 1px solid #e2e8f0;
        }

        .copy-summary {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px;
        }

        .copy-summary-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px dashed #cbd5e1;
        }

        .copy-summary-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .copy-summary-item:first-child {
            padding-top: 0;
        }

        .copy-summary-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
        }

        .copy-summary-value {
            font-size: 13px;
            color: #0f172a;
            font-weight: 700;
        }

        .copy-preview {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 16px;
        }

        .copy-preview-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 12px;
            border-bottom: 1px solid #dbeafe;
            padding-bottom: 8px;
        }

        .copy-preview-date {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            padding: 4px 0;
        }

        .copy-preview-date span:first-child {
            color: #64748b;
        }

        .copy-preview-date span:last-child {
            font-weight: 700;
            color: #0f172a;
        }

        .copy-days-limit {
            font-size: 11px;
            color: #64748b;
            margin-top: 6px;
        }

        .copy-days-limit.warning {
            color: #b45309;
            font-weight: 700;
        }

        .copy-error {
            display: none;
            color: #dc2626;
            font-size: 12px;
            font-weight: 600;
            margin-top: 6px;
        }

        .copy-error.show {
            display: block;
        }

        .roster-empty {
            padding: 60px 20px;
        }

        @media (max-width: 768px) {

            .roster-table th:nth-child(2),
            .roster-table td:nth-child(2) {
                width: 180px;
            }

            .roster-select {
                min-width: 120px;
                font-size: 10px;
            }
        }

        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.375rem !important;
            background-color: #f8f9fa !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            font-size: 14px;
            color: #212529 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        .select2-container--open .select2-dropdown--below,
        .select2-container--open .select2-dropdown--above {
            border-color: #86b7fe;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 0.375rem;
        }

        .select2-search__field {
            border-radius: 4px !important;
        }

        .select2-search__field:focus {
            outline: none !important;
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
        }

        .roster-cell-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .btn-copy-forward {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            border-radius: 6px;
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            cursor: pointer;
            padding: 0;
            transition: all 0.2s ease;
        }

        .btn-copy-forward:hover:not(:disabled) {
            background: #dbeafe;
            border-color: #93c5fd;
            color: #1d4ed8;
        }

        .btn-copy-forward:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <div id="overlay" onclick="closeMobile()"></div>

    @include('client.includes.sidebar')

    <div id="main">
        @include('client.includes.header')

        <div class="content">
            <div class="container-fluid py-3">

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                    <div>
                        <h4 class="fw-bold m-0 text-dark">Shift Roster Management</h4>
                        <p class="text-muted small mb-0">Assign and manage hospital shifts dynamically</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">

                        <a href="{{ route('roster.export', request()->all()) }}"
                            class="btn btn-success px-3 py-2 fw-bold shadow-sm" style="border-radius:8px;">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                        </a>
                        <button type="button" class="btn btn-light border px-3 py-2 fw-bold shadow-sm text-secondary"
                            style="border-radius:8px;" data-bs-toggle="modal" data-bs-target="#reassignModal">
                            <i class="bi bi-arrow-left-right me-1"></i> Reassign
                        </button>

                        <button type="button" class="btn btn-light border px-3 py-2 fw-bold shadow-sm text-primary"
                            style="border-radius:8px;" data-bs-toggle="modal" data-bs-target="#bulkAssignModal">
                            <i class="bi bi-layers-half me-1"></i> Bulk Fill
                        </button>

                        <button type="button" onclick="saveRoster()" id="btnSaveRoster"
                            class="btn btn-primary px-4 py-2 fw-bold shadow-sm" style="border-radius:8px;">
                            <i class="bi bi-save2 me-1"></i> Save Roster
                        </button>
                    </div>
                </div>

                <div class="alert alert-info py-2 px-3 small fw-bold shadow-sm border-info border-opacity-25 d-flex align-items-center"
                    style="border-radius:8px; background-color: #f0f9ff;">
                    <i class="bi bi-lightbulb-fill text-warning fs-5 me-3"></i>
                    <span>Pro Tip: Select a shift, then click the <span
                            class="text-decoration-underline text-dark px-1">copy icon</span> next to the dropdown to
                        instantly copy that shift forward for the next N days!</span>
                </div>

                <div class="dashboard-card mb-4 p-3 shadow-sm border-0 bg-white" style="border-radius:10px;">
                    <form method="GET" action="{{ route('roster.manage') }}" class="row g-3 align-items-end"
                        id="rosterFilterForm">

                        <div class="col-md-3 col-sm-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Department</label>
                            <select name="department_id" class="form-select bg-light">
                                <option value="">All Departments</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Month</label>
                            <select name="month" class="form-select bg-light">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ sprintf('%02d', $m) }}" {{ (int) $month === $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Year</label>
                            <select name="year" class="form-select bg-light">
                                @for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ (int) $year === $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <button type="submit" class="btn btn-dark w-100 fw-bold shadow-sm"
                                style="border-radius:8px;">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>

                <div class="roster-wrapper">
                    <table class="roster-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th class="text-start">Employee Details</th>
                                @for ($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $cDate = \Carbon\Carbon::create($year, $month, $day);
                                        $isWeekend = $cDate->isWeekend();
                                    @endphp
                                    <th class="{{ $isWeekend ? 'bg-weekend text-weekend' : '' }}">
                                        <span style="font-size:14px;">{{ sprintf('%02d', $day) }}</span><br>
                                        <span
                                            style="font-size:10px; font-weight:600; opacity:0.8;">{{ strtoupper($cDate->format('D')) }}</span>
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @if ($groupedEmployees->isEmpty())
                                <tr>
                                    <td colspan="{{ $daysInMonth + 2 }}" class="text-center text-muted roster-empty">
                                        <i class="bi bi-inboxes display-4 d-block mb-3 opacity-25"></i>
                                        <div class="fw-bold fs-5">No employees found</div>
                                        <small>No employees match your selected filter criteria.</small>
                                    </td>
                                </tr>
                            @else
                                @php $sno = 1; @endphp

                                @foreach ($groupedEmployees as $deptName => $deptEmployees)
                                    <tr class="dept-header-row">
                                        <td colspan="{{ $daysInMonth + 2 }}">
                                            <div class="sticky-dept-name">
                                                <i class="bi bi-building me-2 text-info"></i> {{ $deptName }}
                                            </div>
                                        </td>
                                    </tr>

                                    @foreach ($deptEmployees as $employee)
                                        <tr>
                                            <td class="text-muted fw-bold">{{ $sno++ }}</td>
                                            <td class="text-start bg-white">
                                                <span class="emp-name">
                                                    {{ $employee->employee_name }}
                                                    <span class="emp-id-badge">#{{ $employee->employee_id }}</span>
                                                </span>
                                                <span class="emp-desig">
                                                    {{ $employee->designationInfo->designation_name ?? '--' }}
                                                </span>
                                            </td>

                                            @for ($day = 1; $day <= $daysInMonth; $day++)
                                                @php
                                                    $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                                    $dayStr = sprintf('%02d', $day);
                                                    $rosterKey = $employee->id . '_' . $dayStr;

                                                    $savedShift = isset($existingRosters[$rosterKey]) ? $existingRosters[$rosterKey]->shift_id : '';
                                                    $isWeekOff = isset($existingRosters[$rosterKey]) && $existingRosters[$rosterKey]->is_weekoff;

                                                    $currentVal = $isWeekOff ? 'weekoff' : $savedShift;
                                                    $cDate = \Carbon\Carbon::create($year, $month, $day);
                                                    $isWeekend = $cDate->isWeekend();
                                                    $selectClass = '';
                                                    if ($isWeekOff) {
                                                        $selectClass = 'has-weekoff';
                                                    } elseif (!empty($savedShift)) {
                                                        $selectClass = 'has-shift';
                                                    }
                                                @endphp
                                                <td class="{{ $isWeekend && empty($currentVal) ? 'bg-weekend' : '' }}">
                                                    <div class="roster-cell-wrap">
                                                        <select class="roster-select {{ $selectClass }}" data-emp="{{ $employee->id }}"
                                                            data-date="{{ $dateStr }}" data-day="{{ $dayStr }}"
                                                            onchange="updateSelectColor(this)" title="Select shift">
                                                            <option value="">-</option>
                                                            @foreach ($shifts as $shift)
                                                                <option value="{{ $shift->id }}" {{ $currentVal == $shift->id ? 'selected' : '' }}>
                                                                    {{ $shift->shift_name }}
                                                                    ({{ date('h:i A', strtotime($shift->login_time)) }} -
                                                                    {{ date('h:i A', strtotime($shift->logout_time)) }})
                                                                </option>
                                                            @endforeach
                                                            <option value="weekoff" {{ $currentVal == 'weekoff' ? 'selected' : '' }}>W/O
                                                            </option>
                                                        </select>
                                                        <button type="button" class="btn-copy-forward"
                                                            onclick="triggerBulkCopy(this.previousElementSibling)"
                                                            title="Copy this shift forward" {{ empty($currentVal) ? 'disabled' : '' }}>
                                                            <i class="bi bi-copy"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            @endfor
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
    REASSIGN SHIFT MODAL
    ============================================================ --}}
    <div class="modal fade" id="reassignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:12px;">
                <div class="modal-header bg-light pb-2 border-bottom">
                    <h6 class="modal-title fw-bold text-dark"><i
                            class="bi bi-arrow-left-right text-primary me-2"></i>Reassign Shift (Cover Absentee)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-danger">Absent Employee (Remove Shift From)</label>
                        <select id="reassign_from_emp" class="form-select bg-light">
                            <option value="">Select Absent Employee -</option>
                            @foreach ($flatEmployees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->employee_name }} ({{$emp->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-success">Covering Employee (Assign Extra Shift
                            To)</label>
                        <select id="reassign_to_emp" class="form-select bg-light">
                            <option value="">Select Covering Employee -</option>
                            @foreach ($flatEmployees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->employee_name }} ({{$emp->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Date of Absence (1-{{ $daysInMonth }})</label>
                            <input type="number" id="reassign_day" class="form-control bg-light" min="1"
                                max="{{ $daysInMonth }}" value="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Shift to Cover</label>
                            <select id="reassign_shift" class="form-select bg-light">
                                <option value="">Select Shift -</option>
                                @foreach ($shifts as $shift)
                                    <option value="{{ $shift->id }}">
                                        {{ $shift->shift_name }} ({{ date('h:i A', strtotime($shift->login_time)) }} -
                                        {{ date('h:i A', strtotime($shift->logout_time)) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="button" onclick="executeReassign()" class="btn btn-primary w-100 fw-bold shadow-sm"
                        style="border-radius:8px;">
                        <i class="bi bi-check2-circle me-1"></i> Apply Reassignment & Log OT
                    </button>
                    <p class="text-muted small mt-3 text-center mb-0">
                        <i class="bi bi-info-circle me-1"></i> Note: OT will be automatically logged and calculated.
                    </p>
                </div>
            </div>
        </div>
    </div>


    {{-- ============================================================
    BULK ASSIGN MODAL
    ============================================================ --}}
    <div class="modal fade" id="bulkAssignModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:12px;">
                <div class="modal-header bg-light pb-2 border-bottom">
                    <h6 class="modal-title fw-bold text-dark"><i class="bi bi-layers text-primary me-2"></i>Bulk Assign
                        Shift</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Employee</label>
                        <select id="bulk_employee_id" class="form-select bg-light">
                            <option value="all">-- All Employees in Grid --</option>
                            @foreach ($flatEmployees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->employee_name }} ({{$emp->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Shift</label>
                        <select id="bulk_shift_val" class="form-select bg-light">
                            <option value="">Clear Shift -</option>
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}">
                                    {{ $shift->shift_name }} ({{ date('h:i A', strtotime($shift->login_time)) }} -
                                    {{ date('h:i A', strtotime($shift->logout_time)) }})
                                </option>
                            @endforeach
                            <option value="weekoff">Week Off (W/O)</option>
                        </select>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label small fw-bold">From Date (1-{{ $daysInMonth }})</label>
                            <input type="number" id="bulk_start_day" class="form-control bg-light" min="1"
                                max="{{ $daysInMonth }}" value="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">To Date (1-{{ $daysInMonth }})</label>
                            <input type="number" id="bulk_end_day" class="form-control bg-light" min="1"
                                max="{{ $daysInMonth }}" value="{{ $daysInMonth }}">
                        </div>
                    </div>

                    <button type="button" onclick="executeBulkAssign()" class="btn btn-primary w-100 fw-bold shadow-sm"
                        style="border-radius:8px;">
                        <i class="bi bi-check2-circle me-1"></i> Apply to Grid
                    </button>
                    <p class="text-muted small mt-3 text-center mb-0">
                        <i class="bi bi-info-circle me-1"></i> Note: You still need to click <strong>Save
                            Roster</strong> after applying.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
    SHIFT COPY MODAL
    ============================================================ --}}
    <div class="modal fade" id="shiftCopyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:14px;">
                <div class="modal-header custom-modal-header bg-light">
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-1"><i class="bi bi-copy text-primary me-2"></i>Copy
                            Shift Forward</h5>
                        <small class="text-muted">Apply the selected shift to upcoming days.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="copy-summary mb-4">
                        <div class="copy-summary-item">
                            <span class="copy-summary-label">Employee</span>
                            <span class="copy-summary-value text-end" id="copyEmployeeName">-</span>
                        </div>
                        <div class="copy-summary-item">
                            <span class="copy-summary-label">Selected Shift</span>
                            <span class="copy-summary-value text-end" id="copyShiftName">-</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="copyDaysInput" class="form-label fw-bold small text-dark">Apply this shift for how
                            many upcoming days?</label>
                        <div class="input-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                            <input type="number" id="copyDaysInput" class="form-control" min="1" value="1"
                                inputmode="numeric">
                            <span class="input-group-text bg-light text-secondary fw-bold">days</span>
                        </div>
                        <div id="copyDaysLimit" class="copy-days-limit">Maximum available days</div>
                        <div id="copyDaysError" class="copy-error"></div>
                    </div>

                    <div class="copy-preview">
                        <div class="copy-preview-title"><i class="bi bi-calendar-range me-2"></i>Schedule Preview</div>
                        <div class="copy-preview-date">
                            <span>From Date</span>
                            <span id="copyFromDate">-</span>
                        </div>
                        <div class="copy-preview-date">
                            <span>To Date</span>
                            <span id="copyToDate">-</span>
                        </div>
                        <div class="copy-preview-date pt-2 mt-2 border-top border-primary border-opacity-25">
                            <span>Total Days Affected</span>
                            <span id="copyTotalDays" class="text-primary fs-6">0</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top-0 pt-3 pb-3 px-4"
                    style="border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                    <button type="button" class="btn btn-outline-secondary fw-bold px-4" data-bs-dismiss="modal"
                        style="border-radius:8px;">Cancel</button>
                    <button type="button" id="confirmShiftCopyBtn" onclick="confirmShiftCopy()"
                        class="btn btn-primary fw-bold px-4 shadow-sm" style="border-radius:8px;">
                        <i class="bi bi-check2-circle me-1"></i> Apply Shift
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('client.includes.footer_links')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#reassign_from_emp').select2({dropdownParent: $('#reassignModal'), width: '100%', placeholder: "- Select Absent Employee -"});
            $('#reassign_to_emp').select2({dropdownParent: $('#reassignModal'), width: '100%', placeholder: "- Select Covering Employee -"});
            $('#bulk_employee_id').select2({dropdownParent: $('#bulkAssignModal'), width: '100%'});

            $('#reassignModal').on('hidden.bs.modal', function () {
                $('#reassign_from_emp').val('').trigger('change');
                $('#reassign_to_emp').val('').trigger('change');
            });
            $('#bulkAssignModal').on('hidden.bs.modal', function () {
                $('#bulk_employee_id').val('all').trigger('change');
            });
        });

        let hasUnsavedChanges = false;
        window.addEventListener('beforeunload', function (e) {
            if (hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });

        function updateSelectColor(selectObj) {
            if (!selectObj) return;
            hasUnsavedChanges = true;
            selectObj.classList.remove('has-shift', 'has-weekoff');
            if (selectObj.value === 'weekoff') {
                selectObj.classList.add('has-weekoff');
            } else if (selectObj.value !== '') {
                selectObj.classList.add('has-shift');
            }

            // Keep the "copy forward" button enabled only when a shift/weekoff is selected
            const copyBtn = selectObj.nextElementSibling;
            if (copyBtn && copyBtn.classList.contains('btn-copy-forward')) {
                copyBtn.disabled = (selectObj.value === '');
            }
        }

        /* ============================================================
            REASSIGN SHIFT & OT LOGGING (AJAX)
        ============================================================ */
        function executeReassign() {
            const fromEmpId = document.getElementById('reassign_from_emp').value;
            const toEmpId = document.getElementById('reassign_to_emp').value;
            const day = parseInt(document.getElementById('reassign_day').value, 10);
            const shiftVal = document.getElementById('reassign_shift').value;
            const maxDays = {{ $daysInMonth }};

            if (!fromEmpId || !toEmpId || !shiftVal || isNaN(day)) {
                alert("Please fill in all fields.");
                return;
            }

            if (fromEmpId === toEmpId) {
                alert("Cannot reassign to the same employee. Please select a different covering employee.");
                return;
            }

            if (day < 1 || day > maxDays) {
                alert("Please enter a valid date for this month.");
                return;
            }

            const paddedDay = String(day).padStart(2, '0');
            const dateStr = `{{ $year }}-{{ sprintf('%02d', $month) }}-${paddedDay}`;

            const btn = document.querySelector('#reassignModal .btn-primary');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Applying...';
            btn.disabled = true;

            // Make background AJAX Call to process OT and delete absentee shift immediately
            fetch("{{ route('roster.reassignAjax') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    from_emp_id: fromEmpId,
                    to_emp_id: toEmpId,
                    date: dateStr,
                    shift_id: shiftVal
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Visually update the UI Grid
                        const fromSelect = document.querySelector(`.roster-select[data-emp="${fromEmpId}"][data-day="${paddedDay}"]`);
                        if (fromSelect) {
                            fromSelect.value = '';
                            updateSelectColor(fromSelect);
                        }

                        alert("Success! Shift reassigned and Double-Shift (OT) has been logged for the covering employee.");

                        const modalElement = document.getElementById('reassignModal');
                        if (modalElement) {
                            const modal = bootstrap.Modal.getInstance(modalElement);
                            if (modal) modal.hide();
                        }
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(err => {
                    alert("An error occurred connecting to the server.");
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }

        /* Shift Copy Logic remains identical */
        let pendingShiftCopy = {selectObj: null, empId: null, shiftVal: null, startDay: null, maxDays: 0};

        function triggerBulkCopy(selectObj) {
            const shiftVal = selectObj.value;
            if (!shiftVal) return;

            const empId = selectObj.getAttribute('data-emp');
            const startDay = parseInt(selectObj.getAttribute('data-day'), 10);
            const maxDays = {{ $daysInMonth }} - startDay;

            if (maxDays <= 0) return;

            pendingShiftCopy = {selectObj: selectObj, empId: empId, shiftVal: shiftVal, startDay: startDay, maxDays: maxDays};

            let employeeName = 'Selected Employee';
            const row = selectObj.closest('tr');
            if (row) {
                const el = row.querySelector('.emp-name');
                if (el) employeeName = el.textContent.replace(/#\S+/g, '').trim();
            }

            const option = selectObj.options[selectObj.selectedIndex];
            const shiftName = option ? option.textContent.trim() : 'Selected Shift';

            document.getElementById('copyEmployeeName').textContent = employeeName;
            document.getElementById('copyShiftName').textContent = shiftName;

            const input = document.getElementById('copyDaysInput');
            if (input) {
                input.min = 1;
                input.max = maxDays;
                input.value = Math.min(5, maxDays);
            }

            document.getElementById('copyDaysLimit').textContent = `Maximum available: ${maxDays} day${maxDays === 1 ? '' : 's'}`;
            updateShiftCopyPreview();

            const modalElement = document.getElementById('shiftCopyModal');
            if (modalElement) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modal.show();
            }
        }

        function updateShiftCopyPreview() {
            const input = document.getElementById('copyDaysInput');
            const error = document.getElementById('copyDaysError');
            const button = document.getElementById('confirmShiftCopyBtn');
            const limit = document.getElementById('copyDaysLimit');

            if (!input || !pendingShiftCopy.maxDays) return;

            const raw = input.value.trim();
            const days = Number(raw);

            if (error) {error.classList.remove('show'); error.textContent = '';}
            input.classList.remove('is-invalid');

            if (raw === '' || !Number.isInteger(days) || days < 1) {
                document.getElementById('copyFromDate').textContent = '-';
                document.getElementById('copyToDate').textContent = '-';
                document.getElementById('copyTotalDays').textContent = '0';

                if (button) button.disabled = true;
                input.classList.add('is-invalid');
                if (error) {
                    error.textContent = raw === '' ? 'Please enter the number of days.' : 'Please enter a valid number of days.';
                    error.classList.add('show');
                }
                return;
            }

            if (days > pendingShiftCopy.maxDays) {
                if (button) button.disabled = true;
                input.classList.add('is-invalid');
                if (error) {
                    error.textContent = `Only ${pendingShiftCopy.maxDays} upcoming day${pendingShiftCopy.maxDays === 1 ? '' : 's'} available.`;
                    error.classList.add('show');
                }
                return;
            }

            if (button) button.disabled = false;

            const from = new Date({{ $year }}, {{ (int) $month - 1 }}, pendingShiftCopy.startDay + 1);
            const to = new Date({{ $year }}, {{ (int) $month - 1 }}, pendingShiftCopy.startDay + days);

            const fmt = d => d.toLocaleDateString('en-IN', {day: '2-digit', month: 'short', year: 'numeric'});

            document.getElementById('copyFromDate').textContent = fmt(from);
            document.getElementById('copyToDate').textContent = fmt(to);
            document.getElementById('copyTotalDays').textContent = days;

            if (limit) {
                if (days === pendingShiftCopy.maxDays) {
                    limit.textContent = `Maximum ${pendingShiftCopy.maxDays} day${pendingShiftCopy.maxDays === 1 ? '' : 's'} selected`;
                    limit.classList.add('warning');
                } else {
                    limit.textContent = `Maximum available: ${pendingShiftCopy.maxDays} day${pendingShiftCopy.maxDays === 1 ? '' : 's'}`;
                    limit.classList.remove('warning');
                }
            }
        }

        function confirmShiftCopy() {
            const input = document.getElementById('copyDaysInput');
            if (!input) return;

            const days = Number(input.value);
            if (!Number.isInteger(days) || days < 1 || days > pendingShiftCopy.maxDays) {
                updateShiftCopyPreview();
                return;
            }

            const start = pendingShiftCopy.startDay;
            const end = start + days;

            document.querySelectorAll(`.roster-select[data-emp="${pendingShiftCopy.empId}"]`).forEach(select => {
                const day = parseInt(select.getAttribute('data-day'), 10);
                if (day > start && day <= end) {
                    select.value = pendingShiftCopy.shiftVal;
                    updateSelectColor(select);
                }
            });

            const modalElement = document.getElementById('shiftCopyModal');
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            }

            pendingShiftCopy = {selectObj: null, empId: null, shiftVal: null, startDay: null, maxDays: 0};
        }

        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('copyDaysInput');
            if (!input) return;
            input.addEventListener('input', updateShiftCopyPreview);
            input.addEventListener('keydown', function (e) {
                if (['-', '+', 'e', 'E', '.'].includes(e.key)) e.preventDefault();
            });
        });

        function executeBulkAssign() {
            const employeeElement = document.getElementById('bulk_employee_id');
            const shiftElement = document.getElementById('bulk_shift_val');
            const startElement = document.getElementById('bulk_start_day');
            const endElement = document.getElementById('bulk_end_day');

            if (!employeeElement || !shiftElement || !startElement || !endElement) return;

            const empId = employeeElement.value;
            const shiftVal = shiftElement.value;
            const startDay = parseInt(startElement.value, 10);
            const endDay = parseInt(endElement.value, 10);
            const maxDays = {{ $daysInMonth }};

            if (isNaN(startDay) || isNaN(endDay) || startDay < 1 || endDay > maxDays || startDay > endDay) {
                alert("Please enter a valid date range.");
                return;
            }

            let selector = '.roster-select';
            if (empId !== 'all') selector += `[data-emp="${empId}"]`;

            const allSelects = document.querySelectorAll(selector);
            if (allSelects.length === 0) {
                alert("No employees found in the current grid.");
                return;
            }

            allSelects.forEach(select => {
                const day = parseInt(select.getAttribute('data-day'), 10);
                if (day >= startDay && day <= endDay) {
                    select.value = shiftVal;
                    updateSelectColor(select);
                }
            });

            const modalElement = document.getElementById('bulkAssignModal');
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            }
        }

        function saveRoster() {
            const btn = document.getElementById('btnSaveRoster');
            if (!btn) return;

            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Saving...';
            btn.disabled = true;

            let rosterData = {};
            const selects = document.querySelectorAll('.roster-select');

            selects.forEach(select => {
                let empId = select.getAttribute('data-emp');
                let dateStr = select.getAttribute('data-date');
                let val = select.value;

                if (!rosterData[empId]) rosterData[empId] = {};
                rosterData[empId][dateStr] = val;
            });

            if (Object.keys(rosterData).length === 0) {
                alert("No shift data found.");
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }

            fetch("{{ route('roster.save') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({rosterData: rosterData})
            })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || `HTTP error: ${response.status}`);
                    return data;
                })
                .then(data => {
                    if (data.status === 'success') {
                        hasUnsavedChanges = false;
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success position-fixed top-0 end-0 m-3 shadow';
                        alertDiv.style.zIndex = '9999';
                        alertDiv.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i> ${data.message}`;
                        document.body.appendChild(alertDiv);

                        setTimeout(() => alertDiv.remove(), 3000);
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    alert(error.message || "A critical error occurred while saving the roster.");
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }
    </script>
</body>

</html>