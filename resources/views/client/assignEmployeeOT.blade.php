<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Assign OT</title>

    @include('client.includes.header_links')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        .ot-filter-label {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 4px;
        }

        .ot-summary-box {
            border: 1px dashed #0d6efd;
            background: #eff6ff;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 15px;
            color: #1e3a8a;
        }

        .ot-summary-box .ot-summary-amount {
            font-size: 22px;
            font-weight: 800;
            color: #0d47a1;
        }

        .ot-date-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 20px;
            padding: 3px 6px 3px 10px;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            margin: 0 6px 6px 0;
        }

        .ot-date-chip .btn {
            line-height: 1;
            font-size: 12px;
        }

        .ot-help {
            font-size: 12px;
            color: #64748b;
        }

        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.375rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            font-size: 14px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        .ot-dept-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e0f2fe;
            color: #075985;
            border: 1px solid #bae6fd;
            border-radius: 20px;
            padding: 6px 14px;
            font-weight: 700;
            font-size: 13px;
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

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                    <div>
                        <h4 class="fw-bold m-0">Assign Overtime (OT)</h4>
                        <p class="text-muted small mb-0">
                            Assign a fixed OT amount per day to an employee for the selected dates.
                            The total is added to payroll and the payslip.
                        </p>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('otEmployeesList', ['month' => $month, 'year' => $year]) }}"
                            class="btn btn-light border">
                            <i class="bi bi-list-ul me-1"></i> OT Records
                        </a>
                        <a href="{{ route('managePayroll', ['month' => $month, 'year' => $year]) }}"
                            class="btn btn-light border">
                            <i class="bi bi-arrow-left me-1"></i> Payroll
                        </a>
                    </div>

                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {!! nl2br(e(session('success'))) !!}
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

                {{-- Month / Year / Department selector --}}
                <div class="dashboard-card mb-3 p-3 shadow-sm border-0 bg-white" style="border-radius:10px;">

                    <form method="GET" action="{{ route('assignOT') }}" class="row g-3 align-items-end">

                        <div class="col-md-2 col-sm-6">
                            <label class="ot-filter-label">Month</label>
                            <select name="month" class="form-select">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <label class="ot-filter-label">Year</label>
                            <select name="year" class="form-select">
                                @for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ (int) $year === $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <label class="ot-filter-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">- Select Department -</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ (string) $departmentId === (string) $department->id ? 'selected' : '' }}>
                                        {{ $department->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <button type="submit" class="btn btn-dark w-100 fw-bold">
                                <i class="bi bi-funnel-fill me-1"></i> Filter
                            </button>
                        </div>

                    </form>

                </div>

                @if(empty($departmentId))
                    <div class="alert alert-info d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>Select a <strong>Department</strong> above to choose an employee and assign OT.</span>
                    </div>
                @else
                    @php
                        $selectedDepartment = $departments->firstWhere('id', (int) $departmentId);
                    @endphp

                    <div class="dashboard-card mb-4 p-3 shadow-sm border-0 bg-white" style="border-radius:10px;">

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div>
                                <h6 class="fw-bold mb-0">
                                    <i class="bi bi-calendar2-plus-fill me-1 text-primary"></i>
                                    New OT Assignment &mdash; {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
                                </h6>
                                <span class="ot-help">All selected dates must fall in this month.</span>
                            </div>
                            <span class="ot-dept-chip">
                                <i class="bi bi-diagram-3-fill"></i>
                                {{ $selectedDepartment->department_name ?? 'Department' }}
                            </span>
                        </div>

                        @if($employees->isEmpty())
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                No active employees found in this department.
                            </div>
                        @else
                            <form method="POST" action="{{ route('storeAssignedOT') }}" id="assignOTForm">

                                @csrf

                                <input type="hidden" name="department_id" value="{{ $departmentId }}">

                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label class="filter-label small fw-bold">Select Employee <span
                                                class="text-danger">*</span></label>
                                        <select name="employee_id" id="employee_id" class="form-select bg-light" required>
                                            <option value="">- Search / Select Employee -</option>
                                            @foreach($employees as $emp)
                                                <option value="{{ $emp->id }}"
                                                    {{ (string) old('employee_id', $selectedEmployeeId) === (string) $emp->id ? 'selected' : '' }}>
                                                    {{ $emp->employee_name }} ({{ $emp->employee_id }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="filter-label small fw-bold">OT Amount (Per One Day) <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="0.01" min="0.01" name="ot_amount_per_day"
                                                id="ot_amount_per_day" class="form-control"
                                                value="{{ old('ot_amount_per_day') }}" placeholder="0.00" required>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="filter-label small fw-bold">OT Dates <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="ot_dates_display" class="form-control"
                                            placeholder="Click to select one or more dates" readonly>
                                        <div id="ot_dates_hidden"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="filter-label small fw-bold">Reason <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="reason" class="form-control" maxlength="500"
                                            value="{{ old('reason') }}"
                                            placeholder="e.g. Covered night shift on weekly off" required>
                                    </div>

                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="ot-summary-box w-100">
                                            <i class="bi bi-calculator me-1"></i>
                                            Total OT Amount for
                                            <strong id="ot_total_days">0</strong> Day(s) =
                                            <span class="ot-summary-amount">₹<span id="ot_total_amount">0.00</span></span>
                                        </div>
                                    </div>

                                    <div class="col-md-12 text-end">
                                        <button type="submit" class="btn btn-success px-4">
                                            <i class="bi bi-check-circle me-2"></i> Assign OT
                                        </button>
                                    </div>

                                </div>

                            </form>
                        @endif

                    </div>
                @endif

                {{-- Existing OT assignments for the selected month --}}
                <div class="dashboard-card p-3 shadow-sm border-0 bg-white" style="border-radius:10px;">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">
                            <i class="bi bi-clock-history me-1 text-primary"></i>
                            OT Assigned &mdash; {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
                        </h6>
                        <span class="ot-help">{{ $otRecords->count() }} employee record(s)</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="4%" class="text-center">#</th>
                                    <th width="20%">Employee</th>
                                    <th width="11%" class="text-center">OT Days</th>
                                    <th width="27%">OT Dates (click × to remove a date)</th>
                                    <th width="9%" class="text-end">Avg / Day</th>
                                    <th width="11%" class="text-end">Total OT</th>
                                    <th width="18%">Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($otRecords as $index => $record)
                                    @php
                                        $recordEmployee = $record->employee;
                                        $isDateWise = ($record->ot_source ?? 'manual') === 'dates';
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $recordEmployee->employee_name ?? '—' }}</div>
                                            <small class="text-muted">
                                                {{ $recordEmployee->employee_id ?? '' }}
                                                @if($recordEmployee && $recordEmployee->department)
                                                    &middot; {{ $recordEmployee->department->department_name }}
                                                @endif
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ (int) $record->ot_days }}</span>
                                        </td>
                                        <td>
                                            @if($isDateWise)
                                                <div class="d-flex flex-wrap">
                                                    @forelse($record->dates as $otDate)
                                                        <span class="ot-date-chip">
                                                            {{ $otDate->ot_date->format('d M Y') }}
                                                            &middot; ₹{{ number_format((float) $otDate->ot_amount, 2) }}
                                                            <form method="POST"
                                                                action="{{ route('destroyAssignedOTDate', $otDate->id) }}"
                                                                class="d-inline"
                                                                onsubmit="return confirm('Remove OT for {{ $otDate->ot_date->format('d M Y') }}?');">
                                                                @csrf
                                                                <button type="submit" class="btn btn-link text-danger p-0"
                                                                    title="Remove this OT date">
                                                                    <i class="bi bi-x-circle-fill"></i>
                                                                </button>
                                                            </form>
                                                        </span>
                                                    @empty
                                                        <span class="text-muted small">—</span>
                                                    @endforelse
                                                </div>
                                            @else
                                                <span class="text-muted small">
                                                    <i class="bi bi-pencil-square me-1"></i>
                                                    Manual entry (day/hour based) &mdash;
                                                    edit from Manage OT
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($isDateWise)
                                                ₹{{ number_format((float) $record->ot_rate, 2) }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            ₹{{ number_format((float) $record->ot_amount, 2) }}
                                        </td>
                                        <td>
                                            <span class="small">{{ \Illuminate\Support\Str::limit($record->remarks, 90) ?: '—' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                            No OT assigned for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}{{ $departmentId ? ' in this department' : '' }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <p class="ot-help mb-0 mt-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Date-wise OT is paid in full and added to the employee's gross salary, net pay and payslip.
                        Removing every date removes the monthly OT record automatically.
                        Re-generate payroll after changing OT so the payslip and net pay reflect the latest total.
                    </p>

                </div>

            </div>

        </div>

    </div>

    @include('client.includes.footer_links')

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            var month = {{ (int) $month }};
            var year = {{ (int) $year }};

            var monthPadded = (month < 10 ? '0' : '') + month;
            var lastDay = new Date(year, month, 0).getDate();
            var minDate = year + '-' + monthPadded + '-01';
            var maxDate = year + '-' + monthPadded + '-' + (lastDay < 10 ? '0' : '') + lastDay;

            var dateInput = document.getElementById('ot_dates_display');
            var hiddenBox = document.getElementById('ot_dates_hidden');
            var amountInput = document.getElementById('ot_amount_per_day');
            var totalDaysEl = document.getElementById('ot_total_days');
            var totalAmountEl = document.getElementById('ot_total_amount');

            if (!dateInput || !hiddenBox) {
                return;
            }

            function currentAmount() {
                var value = amountInput ? parseFloat(amountInput.value) : 0;
                return isNaN(value) || value < 0 ? 0 : value;
            }

            function renderSummary(selectedDates) {
                var days = selectedDates ? selectedDates.length : 0;
                if (totalDaysEl) {
                    totalDaysEl.textContent = days;
                }
                if (totalAmountEl) {
                    totalAmountEl.textContent = (days * currentAmount()).toFixed(2);
                }
            }

            function syncHiddenInputs(selectedDates) {
                hiddenBox.innerHTML = '';
                (selectedDates || []).forEach(function (date) {
                    var hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'ot_dates[]';
                    hidden.value = fp.formatDate(date, 'Y-m-d');
                    hiddenBox.appendChild(hidden);
                });
            }

            var fp = flatpickr(dateInput, {
                mode: 'multiple',
                dateFormat: 'Y-m-d',
                minDate: minDate,
                maxDate: maxDate,
                showMonths: 1,
                allowInput: false,
                onChange: function (selectedDates) {
                    syncHiddenInputs(selectedDates);
                    renderSummary(selectedDates);
                }
            });

            var oldDates = @json(old('ot_dates', []));
            if (oldDates && oldDates.length) {
                fp.setDate(oldDates, true);
            }

            if (amountInput) {
                amountInput.addEventListener('input', function () {
                    renderSummary(fp.selectedDates || []);
                });
            }

            renderSummary(fp.selectedDates || []);

            if (window.jQuery && jQuery('#employee_id').length) {
                jQuery('#employee_id').select2({
                    width: '100%',
                    placeholder: '- Search / Select Employee -',
                    allowClear: true
                });
            }

            var form = document.getElementById('assignOTForm');
            if (form) {
                form.addEventListener('submit', function (event) {
                    if (!fp.selectedDates || fp.selectedDates.length === 0) {
                        event.preventDefault();
                        alert('Please select at least one OT date.');
                    }
                });
            }

        });
    </script>

</body>

</html>
