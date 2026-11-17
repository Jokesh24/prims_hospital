<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Monthly Attendance Sheet</title>

    @include('client.includes.header_links')

    <!-- ADDED: Premium Loader Styles -->
    <style>
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(255, 255, 255, 0.92);
            z-index: 999999;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .loader-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: slideUpFade 0.6s ease-out;
        }
        .logo-loader-container {
            position: relative;
            width: 90px;
            height: 90px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
        }
        .loader-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: #0d6efd; /* Primary Color */
            border-right-color: rgba(13, 110, 253, 0.3);
            border-bottom-color: rgba(13, 110, 253, 0.1);
            border-left-color: transparent;
            animation: spinRing 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }
        .loader-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 8px;
            animation: pulseLogo 2s ease-in-out infinite;
        }
        .processing-text {
            letter-spacing: 0.5px;
            color: #1e293b;
        }
        .dots::after {
            content: '';
            animation: ellipsis 1.5s infinite;
            display: inline-block;
            width: 20px;
            text-align: left;
        }
        
        /* Keyframe Animations */
        @keyframes spinRing {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes pulseLogo {
            0% { transform: scale(0.95); opacity: 0.85; filter: drop-shadow(0 0 0 rgba(13, 110, 253, 0)); }
            50% { transform: scale(1.05); opacity: 1; filter: drop-shadow(0 0 10px rgba(13, 110, 253, 0.4)); }
            100% { transform: scale(0.95); opacity: 0.85; filter: drop-shadow(0 0 0 rgba(13, 110, 253, 0)); }
        }
        @keyframes ellipsis {
            0% { content: ''; }
            25% { content: '.'; }
            50% { content: '..'; }
            75% { content: '...'; }
            100% { content: ''; }
        }
        @keyframes slideUpFade {
            0% { transform: translateY(20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        .attendance-wrap {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .att-header {
            padding: 12px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .legend {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .legend-item {
            font-size: 12px;
            font-weight: 500;
        }

        /* Top Synced Scrollbar */
        .top-scrollbar-wrapper {
            overflow-x: auto;
            overflow-y: hidden;
            height: 14px;
            border-bottom: 1px solid #dee2e6;
            background: #f8fafc;
        }
        .top-scrollbar-inner {
            height: 14px;
        }

        /* Responsive Viewport with locked container height */
        .table-scroll-container {
            max-height: calc(100vh - 240px);
            overflow: auto;
            position: relative;
        }

        /* Clean scrollbar styling */
        .table-scroll-container::-webkit-scrollbar,
        .top-scrollbar-wrapper::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }
        .table-scroll-container::-webkit-scrollbar-thumb,
        .top-scrollbar-wrapper::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 4px;
        }

        /* Table Layout */
        .table-att {
            border-collapse: separate;
            border-spacing: 0;
            white-space: nowrap;
            margin-bottom: 0;
        }

        .table-att th,
        .table-att td {
            vertical-align: middle;
            font-size: 12px;
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f1f5f9;
        }

        /* Sticky Table Headers (Top) */
        .table-att thead th {
            position: sticky;
            top: 0;
            background: #f8fafc;
            z-index: 10;
            box-shadow: inset 0 -2px 0 #cbd5e1;
        }

        /* Sticky Left Column 1: S.No */
        .col-sticky-sno {
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 5;
            min-width: 50px;
            max-width: 50px;
        }

        /* Sticky Left Column 2: Employee Name */
        .col-sticky-name {
            position: sticky;
            left: 50px;
            background: #fff;
            z-index: 5;
            min-width: 220px;
            max-width: 240px;
            border-right: 2px solid #cbd5e1 !important;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.03);
        }

        /* Intersecting Top-Left Headers must have higher z-index */
        .table-att thead th.col-sticky-sno,
        .table-att thead th.col-sticky-name {
            z-index: 20;
            background: #f1f5f9;
        }

        /* Department Header Row (Sticky Left override) */
        .dept-row td {
            background: #e2e8f0 !important;
            font-size: 13px;
            border-top: 2px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
        }

        /* Hover row consistency for sticky cells */
        .table-att tbody tr:hover td:not(.dept-bg) {
            background-color: #f8fafc !important;
        }
        .table-att tbody tr:hover td.col-sticky-sno:not(.dept-bg),
        .table-att tbody tr:hover td.col-sticky-name:not(.dept-bg) {
            background-color: #f1f5f9 !important;
        }

        /* Time Display formatting */
        .att-time {
            display: block;
            font-size: 9px;
            color: #64748b;
            line-height: 1.2;
            margin-top: 2px;
            white-space: nowrap; /* Forces AM/PM to stay on the same line as the numbers */
        }
    </style>
</head>

<body>

    <!-- ADDED: Premium Global Full Page Loader -->
<div id="globalLoader" class="loader-overlay" style="display: none;">
        <div class="loader-content">
            <div class="logo-loader-container">
                <div class="loader-ring"></div>
                @php
                    $loaderClient = \App\Models\Client::find(session('ClientUser'));
                @endphp
                @if($loaderClient && $loaderClient->logo)
                    <img src="{{ asset('public/uploads/clients/' . $loaderClient->logo) }}" alt="Company Logo" class="loader-logo">
                @else
                    <div class="loader-logo" style="background:#1e293b; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:bold; font-size:12px; border-radius:8px; width:50px; height:50px;">
                        HRMS
                    </div>
                @endif
            </div>
            <h5 class="fw-bold m-0 processing-text">Processing<span class="dots"></span></h5>
            <p class="text-muted small mt-2 mb-0" style="font-weight: 500;">Please wait, this might take a moment.</p>
        </div>
    </div>

    <div id="overlay" onclick="closeMobile()"></div>

    @include('client.includes.sidebar')

    <div id="main">

        @include('client.includes.header')

        <div class="content">

            <div class="container-fluid py-2">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h4 class="fw-bold m-0">Monthly Attendance Sheet</h4>
                </div>

                <div class="dashboard-card mb-2">
                    <!-- ADDED ID: filterForm -->
                    <form method="GET" action="{{ route('monthlyAttendance') }}" id="filterForm">
                        <!-- Perfectly aligned grid: 2 + 2 + 4 + 2 + 2 = 12 cols -->
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="filter-label small fw-bold">Select Month</label>
                                <select name="month" class="form-select filter-control">
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                            {{ date('F', mktime(0,0,0,$i,1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="filter-label small fw-bold">Year</label>
                                <select name="year" class="form-select filter-control">
                                    @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                        <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <!-- ADDED: Department Filter Dropdown -->
                            <div class="col-md-4">
                                <label class="filter-label small fw-bold">Department</label>
                                <select name="department_id" class="form-select filter-control">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ $selectedDepartment == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-1"></i> Search
                                </button>
                            </div>

                            <div class="col-md-2">
                                <!-- ADDED CLASS: export-btn -->
                                <button type="submit" formaction="{{ route('exportMonthlyAttendance') }}" class="btn btn-success w-100 export-btn">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Export Sheet
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="attendance-wrap">
                    <div class="att-header">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-calendar3 me-2 text-primary"></i>
                            Attendance — {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}
                        </h6>

                        <div class="legend">
                            <div class="legend-item"><i class="bi bi-check-circle-fill text-success"></i> Present</div>
                            <div class="legend-item"><i class="bi bi-x-circle-fill text-danger"></i> Absent</div>
                            <div class="legend-item"><i class="bi bi-circle-half text-warning"></i> Half Day</div>
                            <div class="legend-item"><i class="bi bi-clock-fill text-primary"></i> Late</div>
                            <div class="legend-item"><span class="badge bg-success-subtle text-success border">PL</span> Paid Leave</div>
                            <div class="legend-item"><span class="badge bg-danger-subtle text-danger border">UL</span> Unpaid Leave</div>
                            <div class="legend-item"><span class="badge bg-info">HO</span> Holiday</div>
                            <div class="legend-item"><span class="text-secondary fw-bold">-</span> Week Off</div>
                        </div>
                    </div>

                    <div class="top-scrollbar-wrapper" id="topScrollWrapper">
                        <div class="top-scrollbar-inner" id="topScrollInner"></div>
                    </div>

                    <div class="table-scroll-container" id="mainTableContainer">
                        <table class="table table-att text-center align-middle" id="attendanceTable">
                            <thead>
                                <tr>
                                    <th class="col-sticky-sno">S.No</th>
                                    <th class="col-sticky-name text-start">Employee Name</th>
                                    <th style="background:#E8F5E9;color:#2E7D32;" title="Present">✓</th>
                                    <th style="background:#FFEBEE;color:#C62828;" title="Absent">✗</th>
                                    <th style="background:#FFF8E1;color:#F57F17;" title="Half Day">½</th>
                                    <th style="background:#E3F2FD;color:#1565C0;" title="Late Login">🕒</th>
                                    <th style="background:#E8F5E9;color:#1B5E20;" title="Paid Leaves">PL</th>
                                    <th style="background:#FFEBEE;color:#B71C1C;" title="Unpaid Leaves">UL</th>
                                    <th style="background:#E0F7FA;color:#006064;" title="Holidays">HO</th>
                                    <th style="background:#F5F5F5;color:#616161;" title="Week Offs">W</th>

                                    @for($day = 1; $day <= $daysCount; $day++)
                                        @php
                                            $cDate = \Carbon\Carbon::create($year, $month, $day);
                                            $isSunday = $cDate->isSunday();
                                        @endphp
                                        <th class="{{ $isSunday ? 'bg-light text-danger fw-bold' : '' }}">
                                            {{ $day }}
                                        </th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @php $sno = 1; @endphp

                                @forelse($groupedEmployees as $deptName => $deptEmployees)
                                    <!-- Department Header Row -->
                                    <tr class="dept-row">
                                        <td class="col-sticky-sno dept-bg"></td>
                                        <td class="col-sticky-name dept-bg text-start text-primary fw-bold text-uppercase border-end-0">
                                            <i class="bi bi-diagram-3-fill me-1"></i> {{ $deptName }}
                                        </td>
                                        <td colspan="{{ $daysCount + 8 }}" class="dept-bg text-start"></td>
                                    </tr>

                                    <!-- Employees in Department -->
                                    @foreach($deptEmployees as $employee)
                                        @php
                                            $present = 0; $absent = 0; $half = 0; $late = 0;
                                            $paidLeavesCount = 0; $unpaidLeavesCount = 0; $holidaysCount = 0; $weekOffsCount = 0;

                                            for ($day = 1; $day <= $daysCount; $day++) {
                                                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                                $status = $attendanceStatus[$employee->id][$date]['status'] ?? 'A';

                                                switch ($status) {
                                                    case 'P': $present++; break;
                                                    case 'L': $late++; $present++; break;
                                                    case 'H': $half++; break;
                                                    case 'PL': $paidLeavesCount++; break;
                                                    case 'UL': $unpaidLeavesCount++; break;
                                                    case 'HO': $holidaysCount++; break;
                                                    case 'W': $weekOffsCount++; break;
                                                    case 'A': default: $absent++; break;
                                                }
                                            }
                                        @endphp

                                        <tr>
                                            <td class="col-sticky-sno fw-bold text-muted">{{ $sno++ }}</td>
                                            <td class="col-sticky-name text-start">
                                                <span class="fw-bold text-dark">{{ $employee->employee_name }}</span>
                                                @if(!empty($employee->employee_id))
                                                    <br><small class="text-muted" style="font-size: 11px;">{{ $employee->employee_id }}</small>
                                                @endif
                                            </td>
                                            <td class="fw-bold text-success">{{ $present }}</td>
                                            <td class="fw-bold text-danger">{{ $absent }}</td>
                                            <td class="fw-bold text-warning">{{ $half }}</td>
                                            <td class="fw-bold text-primary">{{ $late }}</td>
                                            <td class="fw-bold" style="color:#2e7d32;">{{ $paidLeavesCount }}</td>
                                            <td class="fw-bold" style="color:#c62828;">{{ $unpaidLeavesCount }}</td>
                                            <td class="fw-bold text-info">{{ $holidaysCount }}</td>
                                            <td class="fw-bold text-secondary">{{ $weekOffsCount }}</td>

                                            @for($day = 1; $day <= $daysCount; $day++)
                                                @php
                                                    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                                    $attData = $attendanceStatus[$employee->id][$date] ?? ['status' => 'A', 'in' => null, 'out' => null];
                                                @endphp

                                                <td>
                                                    @switch($attData['status'])
                                                        @case('P')
                                                            <i class="bi bi-check-circle-fill text-success" title="Present"></i>
                                                            @break
                                                        @case('L')
                                                            <i class="bi bi-clock-fill text-primary" title="Late"></i>
                                                            @break
                                                        @case('H')
                                                            <i class="bi bi-circle-half text-warning" title="Half Day"></i>
                                                            @break
                                                        @case('PL')
                                                            <span class="badge bg-success-subtle text-success border" style="font-size:10px;" title="Paid Leave">PL</span>
                                                            @break
                                                        @case('UL')
                                                            <span class="badge bg-danger-subtle text-danger border" style="font-size:10px;" title="Unpaid Leave">UL</span>
                                                            @break
                                                        @case('HO')
                                                            <span class="badge bg-info" style="font-size:10px;" title="Holiday">HO</span>
                                                            @break
                                                        @case('W')
                                                            <span class="text-secondary fw-bold" title="Week Off">-</span>
                                                            @break
                                                        @default
                                                            <i class="bi bi-x-circle-fill text-danger" title="Absent"></i>
                                                    @endswitch

                                                    @if($attData['in'])
                                                        <span class="att-time">{{ $attData['in'] }}<br>{{ $attData['out'] }}</span>
                                                    @endif
                                                </td>
                                            @endfor
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="{{ $daysCount + 10 }}" class="text-center py-4 text-muted">
                                            No Employees Found..
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>  
                </div>

            </div>

        </div>

        @include('client.includes.footer_links')

    </div>

    <!-- Scroll Synchronization and Loader Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            /* --- Loader Logic --- */
            const filterForm = document.getElementById('filterForm');
            if (filterForm) {
                filterForm.addEventListener('submit', function () {
                    document.getElementById('globalLoader').style.display = 'flex';
                });
            }

            document.querySelectorAll('.export-btn').forEach(button => {
                button.addEventListener('click', function () {
                    document.getElementById('globalLoader').style.display = 'flex';
                    // Since exports trigger file downloads, the page doesn't reload.
                    // Automatically hide the loader after a short delay so the UI remains usable.
                    setTimeout(() => {
                        document.getElementById('globalLoader').style.display = 'none';
                    }, 4000); 
                });
            });

            /* --- Scroll Synchronization --- */
            const table = document.getElementById('attendanceTable');
            const mainContainer = document.getElementById('mainTableContainer');
            const topWrapper = document.getElementById('topScrollWrapper');
            const topInner = document.getElementById('topScrollInner');

            function syncWidth() {
                if (table && topInner) {
                    topInner.style.width = table.scrollWidth + 'px';
                }
            }

            syncWidth();
            window.addEventListener('resize', syncWidth);

            let isSyncingMain = false;
            let isSyncingTop = false;

            // Sync Main container horizontal scroll to Top scrollbar
            mainContainer.addEventListener('scroll', function () {
                if (!isSyncingMain) {
                    isSyncingTop = true;
                    topWrapper.scrollLeft = mainContainer.scrollLeft;
                }
                isSyncingMain = false;
            });

            // Sync Top scrollbar to Main container
            topWrapper.addEventListener('scroll', function () {
                if (!isSyncingTop) {
                    isSyncingMain = true;
                    mainContainer.scrollLeft = topWrapper.scrollLeft;
                }
                isSyncingTop = false;
            });
        });
    </script>
</body>

</html>