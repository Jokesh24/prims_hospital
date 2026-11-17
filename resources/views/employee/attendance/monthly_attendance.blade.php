<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Monthly Attendance Sheet</title>
    @include('employee.includes.header_links')
    <style>
        .attendance-wrap {
            background: #fff;
            border-radius: 12px;
            overflow: auto;
            padding: 20px;
        }

        .att-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .legend {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .legend-item {
            font-size: 0.85rem;
            font-weight: 500;
        }

        .ic-present { color: #10b981; } /* Emerald */
        .ic-absent { color: #ef4444; }  /* Red */
        .ic-half { color: #f59e0b; }    /* Amber */
        .ic-late { color: #3b82f6; }    /* Blue */
        .ic-weekend { color: #94a3b8; } /* Slate */
        .ic-holiday { color: #8b5cf6; } /* Indigo */
        
        .table th { font-size: 0.9rem; color: #475569; }
        .table td { vertical-align: middle; height: 50px; }
    </style>
</head>

<body>
    @include('employee.includes.sidebar')
    <div id="main">
        @include('employee.includes.header')
        <div class="content">
            <div class="container-fluid py-2">
                <h4 class="fw-bold mb-3">Monthly Attendance Sheet</h4>

                <div class="dashboard-card mb-3" style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
                    <form method="GET" action="{{ route('employee.attendance.monthly') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="fw-bold small mb-1">Select Month</label>
                                <select name="month" class="form-select">
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ sprintf('%02d', $i) }}" {{ $month == $i ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold small mb-1">Year</label>
                                <select name="year" class="form-select">
                                    @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                        <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Search</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="attendance-wrap shadow-sm">
                    <div class="att-header">
                        <h6 class="fw-bold mb-0">Attendance Status — {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</h6>
                        <div class="legend">
                            <div class="legend-item"><i class="bi bi-check-circle-fill ic-present me-1"></i> Present</div>
                            <div class="legend-item"><i class="bi bi-circle-half ic-half me-1"></i> Half Day</div>
                            <div class="legend-item"><i class="bi bi-clock-fill ic-late me-1"></i> Late Login</div>
                            <div class="legend-item"><i class="bi bi-x-circle-fill ic-absent me-1"></i> Absent</div>
                            <div class="legend-item"><i class="bi bi-dash-circle-fill ic-weekend me-1"></i> Week Off</div>
                            <div class="legend-item"><i class="bi bi-star-fill ic-holiday me-1"></i> Holiday</div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table text-center table-bordered mb-0">
                            <thead class="bg-light">
                                <tr>
                                    @for($day = 1; $day <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $day++)
                                        <th>{{ sprintf('%02d', $day) }}</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @for($day = 1; $day <= cal_days_in_month(CAL_GREGORIAN, $month, $year); $day++)
                                        @php 
                                            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                            $status = $attendanceStatus[$date] ?? 'A';
                                        @endphp
                                        <td>
                                            @if($status == 'W') 
                                                <i class="bi bi-dash-circle-fill ic-weekend" data-bs-toggle="tooltip" title="Week Off"></i>
                                            @elseif($status == 'HOL') 
                                                <i class="bi bi-star-fill ic-holiday" data-bs-toggle="tooltip" title="Holiday"></i>
                                            @elseif($status == 'P') 
                                                <i class="bi bi-check-circle-fill ic-present" data-bs-toggle="tooltip" title="Present"></i>
                                            @elseif($status == 'H') 
                                                <i class="bi bi-circle-half ic-half" data-bs-toggle="tooltip" title="Half Day"></i>
                                            @elseif($status == 'L') 
                                                <i class="bi bi-clock-fill ic-late" data-bs-toggle="tooltip" title="Late Login"></i>
                                            @else 
                                                <i class="bi bi-x-circle-fill ic-absent" data-bs-toggle="tooltip" title="Absent"></i>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
       
            </div>
        </div>
        @include('employee.includes.footer_links')
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof bootstrap !== 'undefined') {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            }
        });
    </script>
</body>
</html>