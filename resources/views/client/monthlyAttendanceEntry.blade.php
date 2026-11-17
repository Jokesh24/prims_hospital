<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Attendance Entry</title>

    @include('client.includes.header_links')

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .mae-table th,
        .mae-table td {
            vertical-align: middle;
            font-size: 13px;
        }

        .mae-table th {
            background: #f1f5f9;
            font-weight: 700;
            color: #475569;
        }

        .mae-weekend td {
            background-color: #fff1f2 !important;
        }

        .mae-shift-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            background: #dbeafe;
            color: #1e40af;
            display: inline-block;
        }

        .mae-shift-time {
            font-size: 11px;
            color: #64748b;
            display: block;
            margin-top: 2px;
        }

        .mae-weekoff-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            background: #fee2e2;
            color: #991b1b;
        }

        .mae-no-shift {
            font-size: 11px;
            color: #94a3b8;
            font-style: italic;
        }

        .mae-time-input {
            width: 130px;
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
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        /* ADDED: Bulk Fill modal styling, matching the roster page's modal look */
        .mae-bulk-note {
            font-size: 12px;
            color: #64748b;
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

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="fw-bold m-0">Monthly Attendance Entry</h4>
                        <p class="text-muted small mb-0">Fill in attendance for one employee across a month — only the
                            days you have data for.</p>
                    </div>
                    <a href="{{ route('manageAttendance') }}" class="btn btn-light border">
                        <i class="bi bi-list-ul me-1"></i> Manage Attendance
                    </a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="dashboard-card mb-4 p-3 shadow-sm border-0 bg-white" style="border-radius:10px;">
                    <form method="GET" action="{{ route('monthlyAttendanceEntry') }}" class="row g-3 align-items-end">

                        <div class="col-md-5 col-sm-6">
                            <label class="form-label small fw-bold text-secondary mb-1">Employee</label>
                            <select name="employee_id" id="employee_id" class="form-select bg-light" required>
                                <option value="">- Select Employee -</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ (string) $employeeId === (string) $emp->id ? 'selected' : '' }}>
                                        {{ $emp->employee_name }} ({{ $emp->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 col-sm-6">
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
                            <button type="submit" class="btn btn-dark w-100 fw-bold shadow-sm">
                                <i class="bi bi-search me-1"></i> Load
                            </button>
                        </div>

                    </form>
                </div>

                @if($employee)

                    <div class="dashboard-card mb-3 p-3 shadow-sm border-0 bg-white" style="border-radius:10px;">
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <div>
                                <span class="fw-bold fs-6">{{ $employee->employee_name }}</span>
                                <span class="text-muted">#{{ $employee->employee_id }}</span>
                                <br>
                                <span class="text-muted small">
                                    {{ $employee->designationInfo->designation_name ?? '--' }}
                                    @if($employee->department)
                                        &middot; {{ $employee->department->department_name }}
                                    @endif
                                </span>
                            </div>
                            <div class="text-muted small align-self-center">
                                {{ date('F', mktime(0, 0, 0, (int) $month, 1)) }} {{ $year }}
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div class="alert alert-info py-2 px-3 small fw-bold shadow-sm border-info border-opacity-25 d-flex align-items-center mb-0 flex-grow-1"
                            style="border-radius:8px; background-color:#f0f9ff;">
                            <i class="bi bi-lightbulb-fill text-warning fs-5 me-3"></i>
                            <span>Only fill in Punch In / Punch Out for the days you have data for — leave the rest blank
                                and
                                they'll be skipped automatically.</span>
                        </div>

                        {{-- ADDED: Bulk Fill trigger, mirrors the roster page's "Bulk Fill" button --}}
                        <button type="button"
                            class="btn btn-light border px-3 py-2 fw-bold shadow-sm text-primary flex-shrink-0"
                            style="border-radius:8px;" data-bs-toggle="modal" data-bs-target="#bulkFillModal">
                            <i class="bi bi-layers-half me-1"></i> Apply to Multiple Days
                        </button>
                    </div>

                    <form method="POST" action="{{ route('storeMonthlyAttendanceSingle') }}" id="monthlyAttendanceForm">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">

                        <div class="dashboard-card p-0 shadow-sm border-0 bg-white"
                            style="border-radius:10px; overflow:hidden;">
                            <div class="table-responsive" style="max-height:65vh; overflow-y:auto;">
                                <table class="table table-hover mb-0 mae-table">
                                    <thead style="position:sticky; top:0; z-index:2;">
                                        <tr>
                                            <th style="width:90px;">Date</th>
                                            <th style="width:200px;">Rostered Shift</th>
                                            <th>Punch In</th>
                                            <th>Punch Out</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- CHANGED: row now carries data-day so the Bulk Fill modal can target a day range
                                        --}}
                                        @foreach($days as $d)
                                            <tr class="mae-row {{ $d['is_weekend'] ? 'mae-weekend' : '' }}"
                                                data-day="{{ $d['day'] }}">
                                                <td>
                                                    <strong>{{ sprintf('%02d', $d['day']) }}</strong>
                                                    <span class="text-muted small ms-1">{{ $d['day_name'] }}</span>
                                                </td>
                                                <td>
                                                    @if($d['is_weekoff'])
                                                        <span class="mae-weekoff-badge">Week Off</span>
                                                    @elseif($d['shift_name'])
                                                        <span class="mae-shift-badge">{{ $d['shift_name'] }}</span>
                                                        @if($d['shift_time'])
                                                            <span class="mae-shift-time">{{ $d['shift_time'] }}</span>
                                                        @endif
                                                    @else
                                                        <span class="mae-no-shift">No shift assigned</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{-- CHANGED: class mae-punch-in added (used by Bulk Fill JS);
                                                    value/placeholder now AM/PM --}}
                                                    <input type="text"
                                                        class="form-control mae-time-input mae-time-picker mae-punch-in"
                                                        name="attendance[{{ $d['date'] }}][punch_in]"
                                                        value="{{ old('attendance.' . $d['date'] . '.punch_in', $d['punch_in']) }}"
                                                        placeholder="--:-- --" autocomplete="off">
                                                </td>
                                                <td>
                                                    {{-- CHANGED: class mae-punch-out added (used by Bulk Fill JS);
                                                    value/placeholder now AM/PM --}}
                                                    <input type="text"
                                                        class="form-control mae-time-input mae-time-picker mae-punch-out"
                                                        name="attendance[{{ $d['date'] }}][punch_out]"
                                                        value="{{ old('attendance.' . $d['date'] . '.punch_out', $d['punch_out']) }}"
                                                        placeholder="--:-- --" autocomplete="off">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3 text-end">
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="document.querySelectorAll('.mae-time-picker').forEach(function(i){ i.value=''; i._flatpickr && i._flatpickr.clear(); })">
                                Clear All
                            </button>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="bi bi-save2 me-1"></i> Save Attendance
                            </button>
                        </div>
                    </form>

                    {{-- ============================================================
                    ADDED: BULK FILL MODAL — apply one Punch In / Punch Out pair
                    across a chosen day range, mirroring the roster page's
                    Bulk Assign modal.
                    ============================================================ --}}
                    <div class="modal fade" id="bulkFillModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius:12px;">
                                <div class="modal-header bg-light pb-2 border-bottom">
                                    <h6 class="modal-title fw-bold text-dark">
                                        <i class="bi bi-layers text-primary me-2"></i>Apply to Multiple Days
                                    </h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">

                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Punch In Time <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" id="bulk_punch_in"
                                                class="form-control bg-light mae-bulk-time-picker" placeholder="--:-- --"
                                                autocomplete="off">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Punch Out Time</label>
                                            <input type="text" id="bulk_punch_out"
                                                class="form-control bg-light mae-bulk-time-picker" placeholder="--:-- --"
                                                autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">From Date (1-{{ count($days) }})</label>
                                            <input type="number" id="bulk_start_day" class="form-control bg-light" min="1"
                                                max="{{ count($days) }}" value="1">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">To Date (1-{{ count($days) }})</label>
                                            <input type="number" id="bulk_end_day" class="form-control bg-light" min="1"
                                                max="{{ count($days) }}" value="{{ count($days) }}">
                                        </div>
                                    </div>

                                    <p class="mae-bulk-note mb-4">
                                        <i class="bi bi-info-circle me-1"></i> This fills the Punch In / Punch Out fields in
                                        the
                                        table below for the selected date range — Week Off and shift status shown per day
                                        are
                                        not affected. You still need to click <strong>Save Attendance</strong> afterward.
                                    </p>

                                    <button type="button" onclick="executeBulkFillAttendance()"
                                        class="btn btn-primary w-100 fw-bold shadow-sm" style="border-radius:8px;">
                                        <i class="bi bi-check2-circle me-1"></i> Apply to Grid
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                @elseif($employeeId)
                    <div class="alert alert-warning">Selected employee was not found.</div>
                @else
                    <div class="dashboard-card p-5 text-center text-muted shadow-sm border-0 bg-white"
                        style="border-radius:10px;">
                        <i class="bi bi-calendar3 display-4 d-block mb-3 opacity-25"></i>
                        Select an employee, month and year above, then click <strong>Load</strong> to begin.
                    </div>
                @endif

            </div>
        </div>

    </div>

    @include('client.includes.footer_links')

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        // CHANGED: shared 12-hour AM/PM time config, reused by both the per-row
        // pickers and the Bulk Fill modal's pickers so their output format matches
        // exactly (required for the Bulk Fill JS to set values correctly).
        var maeTimeConfig = {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K",   // e.g. "02:30 PM" — matches the controller's 'h:i A' display format
            time_24hr: false,
            allowInput: true
        };

        $(document).ready(function () {
            $('#employee_id').select2({width: '100%', placeholder: '- Select Employee -'});

            flatpickr(".mae-time-picker", maeTimeConfig);

            // ADDED: Bulk Fill modal's own time pickers (12-hour AM/PM, same format)
            flatpickr(".mae-bulk-time-picker", maeTimeConfig);

            $('#monthlyAttendanceForm').on('submit', function () {
                var btn = $(this).find('button[type="submit"]');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Saving...');
            });
        });

        // ADDED: Applies the Bulk Fill modal's Punch In / Punch Out values to every
        // row in the chosen day range, mirroring executeBulkAssign() on the roster page.
        function executeBulkFillAttendance() {
            var punchInEl = document.getElementById('bulk_punch_in');
            var punchOutEl = document.getElementById('bulk_punch_out');
            var startEl = document.getElementById('bulk_start_day');
            var endEl = document.getElementById('bulk_end_day');

            if (!punchInEl || !punchOutEl || !startEl || !endEl) return;

            var punchIn = punchInEl.value.trim();
            var punchOut = punchOutEl.value.trim();
            var startDay = parseInt(startEl.value, 10);
            var endDay = parseInt(endEl.value, 10);
            var maxDays = {{ count($days) }};

            if (!punchIn) {
                alert('Please select a Punch In time.');
                return;
            }

            if (isNaN(startDay) || isNaN(endDay) || startDay < 1 || endDay > maxDays || startDay > endDay) {
                alert('Please enter a valid date range.');
                return;
            }

            var rows = document.querySelectorAll('.mae-row');
            if (rows.length === 0) {
                alert('No days found to apply to.');
                return;
            }

            rows.forEach(function (row) {
                var day = parseInt(row.getAttribute('data-day'), 10);
                if (day < startDay || day > endDay) return;

                var inInput = row.querySelector('.mae-punch-in');
                var outInput = row.querySelector('.mae-punch-out');

                if (inInput) {
                    if (inInput._flatpickr) {
                        inInput._flatpickr.setDate(punchIn, true, "h:i K");
                    } else {
                        inInput.value = punchIn;
                    }
                }

                if (outInput) {
                    if (punchOut) {
                        if (outInput._flatpickr) {
                            outInput._flatpickr.setDate(punchOut, true, "h:i K");
                        } else {
                            outInput.value = punchOut;
                        }
                    } else {
                        if (outInput._flatpickr) {
                            outInput._flatpickr.clear();
                        } else {
                            outInput.value = '';
                        }
                    }
                }
            });

            var modalElement = document.getElementById('bulkFillModal');
            if (modalElement) {
                var modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            }
        }
    </script>

</body>

</html>