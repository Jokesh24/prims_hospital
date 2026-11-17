<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OT Employees List</title>
    @include('client.includes.header_links')
</head>

<body>

    @include('client.includes.sidebar')

    <div id="main">
        @include('client.includes.header')

        <div class="content">
            <div class="container-fluid">

                <div class="crm-header d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Overtime (OT) Records</h4>

                    <a href="{{ route('assignOT', ['month' => $month, 'year' => $year]) }}"
                        class="btn btn-success">
                        <i class="bi bi-calendar2-plus me-1"></i> Assign OT
                    </a>
                </div>

                <!-- Filter Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <form action="{{ route('otEmployeesList') }}" method="GET" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="month" class="form-label fw-bold">Select Month</label>
                                <select name="month" id="month" class="form-select">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ sprintf('%02d', $m) }}" {{ $month == $m ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="year" class="form-label fw-bold">Select Year</label>
                                <select name="year" id="year" class="form-select">
                                    @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-funnel-fill me-1"></i> Filter Records
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Data Table Card -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" width="5%">S.No</th>
                                        <th width="10%">Emp ID</th>
                                        <th width="18%">Employee Name</th>
                                        <th width="15%">Department</th>
                                        <th class="text-center" width="9%">OT Days</th>
                                        <th class="text-center" width="10%">OT Time</th>
                                        <th class="text-end" width="12%">OT Amount (₹)</th>
                                        <th width="16%">Reason</th>
                                        <th class="text-center" width="15%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($otRecords as $index => $record)
                                        @php
                                            // Format the decimal hours to human readable (e.g., 16.5 to 16h 30m)
                                            $decimalHours = max(0, (float) $record->ot_hours);
                                            $totalMinutes = (int) round($decimalHours * 60);
                                            $hours = intdiv($totalMinutes, 60);
                                            $minutes = $totalMinutes % 60;
                                            $formattedTime = "{$hours}h {$minutes}m";
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td><span
                                                    class="badge bg-secondary">{{ $record->employee->employee_id ?? '--' }}</span>
                                            </td>
                                            <td class="fw-bold">{{ $record->employee->employee_name ?? '--' }}</td>
                                            <td>
                                                {{ $record->employee->department->department_name ?? '--' }} <br>
                                                <small
                                                    class="text-muted">{{ $record->employee->designationInfo->designation_name ?? '--' }}</small>
                                            </td>
                                            <td class="text-center">
                                                {{ $record->ot_days }}
                                                @if(($record->ot_source ?? 'manual') === 'dates')
                                                    <span class="badge bg-info text-dark d-block mt-1"
                                                        style="font-size:9px;">Date-wise</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{ (float) $record->ot_hours > 0 ? $formattedTime : '—' }}
                                            </td>
                                            <td class="text-end fw-bold text-success">
                                                {{ number_format($record->ot_amount, 2) }}</td>
                                            <td>
                                                <span class="small">
                                                    {{ \Illuminate\Support\Str::limit($record->remarks, 60) ?: '—' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if(($record->ot_source ?? 'manual') === 'dates')
                                                    <a href="{{ route('assignOT', ['month' => $month, 'year' => $year, 'department_id' => $record->employee->department_id ?? null, 'employee_id' => $record->employee_id]) }}"
                                                        class="btn btn-sm btn-outline-primary"
                                                        title="Manage the assigned OT dates">
                                                        <i class="bi bi-calendar2-check"></i> Manage Dates
                                                    </a>
                                                @else
                                                    <a href="{{ route('manageEmployeeOT', ['employee' => $record->employee_id, 'month' => $month, 'year' => $year]) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Manage / Edit OT">
                                                        <i class="bi bi-pencil-square"></i> Manage
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">
                                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                No Overtime records found for {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                                                {{ $year }}.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('client.includes.footer_links')

</body>

</html>