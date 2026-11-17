<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>School HRMS Dashboard</title>
    @include('employee.includes.header_links')


</head>

<body>

    <div id="overlay" onclick="closeMobile()"></div>

    <!-- ═══════════════ SIDEBAR ═══════════════ -->
    @include('employee.includes.sidebar')


    <!-- ═══════════════ MAIN ═══════════════ -->
    <div id="main">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show floating-alert" role="alert"
                style="position: fixed; top: 20px; right: 20px; z-index: 9999; border: none; border-left: 4px solid #059669; background: #fff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); color: #0f172a; font-weight: 500;">
                <i class="bi bi-check-circle-fill" style="color: #059669; margin-right: 8px;"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                    style="font-size: 0.8rem;"></button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert"
                style="position: fixed; top: 20px; right: 20px; z-index: 9999; border: none; border-left: 4px solid #dc2626; background: #fff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); color: #0f172a; font-weight: 500;">
                <i class="bi bi-exclamation-triangle-fill" style="color: #dc2626; margin-right: 8px;"></i>
                {{ session('error') ?? $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                    style="font-size: 0.8rem;"></button>
            </div>
        @endif

        @include('employee.includes.header')


        <div class="content">


            <!-- ══ ROW 1 ══ -->
            <div class="row g-3 mb-4 mt-2">

                <!-- LEFT: stat-mini cards -->
                <div class="col-12 col-md-3 col-xl-3">

                    <div class="stat-group-card">

                        <div class="stat-item">
                            <div class="stat-icon-square">
                                <i class="bi bi-calendar2-plus"></i>
                            </div>
                            <div class="stat-info">
                                <div class="sm-lbl">Total Leaves Applied</div>
                                <div class="sm-val">{{ $totalLeavesApplied }}</div>
                            </div>
                        </div>

                        <div class="stat-item flex-column align-items-start">
                            <div class="d-flex align-items-center w-100">
                                <div class="stat-icon-square">
                                    <i class="bi bi-wallet2"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="sm-lbl">Paid Leaves</div>
                                    <div class="sm-val">{{ $leftPaidLeaves }} <span
                                            style="font-size: 0.8rem; color:#6b7280; font-weight:normal;">Left</span>
                                    </div>
                                </div>
                            </div>

                            <div class="stat-expanded-area">
                                <div class="mini-detail-row">
                                    <div class="mini-detail-item">
                                        <span class="dot" style="background:#3b5bdb;"></span>
                                        <span style="font-size: 0.8rem; color:#6b7280;">Left:
                                            <strong>{{ $leftPaidLeaves }}</strong></span>
                                    </div>
                                    <div class="mini-detail-item">
                                        <span class="dot" style="background:#d63384;"></span>
                                        <span style="font-size: 0.8rem; color:#6b7280;">Used:
                                            <strong>{{ $usedPaidLeaves }}</strong></span>
                                    </div>
                                </div>
                                <div class="sm-bar-wrap"
                                    style="height: 6px; background: #f3f4f6; border-radius: 10px; display: flex; overflow: hidden;">
                                    <div style="width:{{ $leftLeavesPercentage }}%; background:#3b5bdb;"></div>
                                    <div style="width:{{ $usedLeavesPercentage }}%; background:#d63384;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="stat-item flex-column align-items-start">
                            <div class="d-flex align-items-center w-100">
                                <div class="stat-icon-square">
                                    <i class="bi bi-person-check"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="sm-lbl">This Month Present</div>
                                    <div class="sm-val">{{ $totalPresent }}</div>
                                </div>
                            </div>

                            <div class="stat-expanded-area">
                                <div class="mini-detail-row">
                                    <div class="mini-detail-item">
                                        <span class="dot" style="background:#10b981;"></span>
                                        <span style="font-size: 0.8rem; color:#6b7280;">Full:
                                            <strong>{{ $fullDays }}</strong></span>
                                    </div>
                                    <div class="mini-detail-item">
                                        <span class="dot" style="background:#f59e0b;"></span>
                                        <span style="font-size: 0.8rem; color:#6b7280;">Half:
                                            <strong>{{ $halfDays }}</strong></span>
                                    </div>
                                </div>
                                <div class="sm-bar-wrap"
                                    style="height: 6px; background: #f3f4f6; border-radius: 10px; display: flex; overflow: hidden;">
                                    <div style="width:{{ $fullDayPercentage }}%; background:#10b981;"></div>
                                    <div style="width:{{ $halfDayPercentage }}%; background:#f59e0b;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="stat-item flex-column align-items-start">
                            <div class="d-flex align-items-center w-100">
                                <div class="stat-icon-square">
                                    <i class="bi bi-person-dash"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="sm-lbl">This Month Absent</div>
                                    <div class="sm-val">{{ $totalAbsent }}</div>
                                </div>
                            </div>

                            <div class="stat-expanded-area">
                                <div class="mini-detail-row">
                                    <div class="mini-detail-item">
                                        <span class="dot" style="background:#ef4444;"></span>
                                        <span style="font-size: 0.8rem; color:#6b7280;">Absent:
                                            <strong>{{ $totalAbsent }}</strong></span>
                                    </div>
                                    <div class="mini-detail-item">
                                        <span class="dot" style="background:#f97316;"></span>
                                        <span style="font-size: 0.8rem; color:#6b7280;">Late:
                                            <strong>{{ $totalLateLogins }}</strong></span>
                                    </div>
                                </div>
                                <div class="sm-bar-wrap"
                                    style="height: 6px; background: #f3f4f6; border-radius: 10px; display: flex; overflow: hidden;">
                                    <div style="width:{{ $absentPercentage }}%; background:#ef4444;"></div>
                                    <div style="width:{{ $latePercentage }}%; background:#f97316;"></div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>



                <!-- RIGHT: stacked cards matching image -->
                <div class="col-12 col-md-4 col-xl-5">
                    <div class="right-col">

                        <div class="onleave-card">
                            <ul class="nav nav-pills card-tabs-nav" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="pills-leave-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-leave" type="button" role="tab">
                                        Leave Dates <span class="tab-count">{{ $leaveRecords->count() }}</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-late-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-late" type="button" role="tab">
                                        Late Logins <span class="tab-count">{{ count($lateLoginDates) }}</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-halfday-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-halfday" type="button" role="tab">
                                        Half Days <span class="tab-count">{{ count($halfDayDates) }}</span>
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="pills-tabContent">

                                <div class="tab-pane fade show active" id="pills-leave" role="tabpanel">
                                    <div class="av-row" style="flex-wrap: wrap;">
                                        @forelse($leaveRecords as $leave)
                                            <div class="av-person mb-2">
                                                <div class="av-circle"
                                                    style="background:linear-gradient(135deg,#3b5bdb,#7c3aed); font-weight: bold; font-size: 14px;">
                                                    {{ \Carbon\Carbon::parse($leave->leave_date_from)->format('d') }}
                                                    <span class="av-status leave" style="background: #ef4444;"></span>
                                                </div>
                                                <div class="av-name">
                                                    {{ \Carbon\Carbon::parse($leave->leave_date_from)->format('M y') }}
                                                </div>
                                            </div>
                                        @empty
                                            <span style="font-size: 0.85rem; color:#6b7280;">No leaves taken.</span>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="pills-late" role="tabpanel">
                                    <div class="av-row" style="flex-wrap: wrap;">
                                        @forelse($lateLoginDates as $date)
                                            <div class="av-person mb-2">
                                                <div class="av-circle"
                                                    style="background:linear-gradient(135deg,#f59e0b,#d97706); font-weight: bold; font-size: 14px;">
                                                    {{ $date->format('d') }}
                                                    <span class="av-status late" style="background: #ef4444;"></span>
                                                </div>
                                                <div class="av-name">{{ $date->format('M y') }}</div>
                                            </div>
                                        @empty
                                            <span style="font-size: 0.85rem; color:#6b7280;">No late logins! Keep it
                                                up.</span>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="pills-halfday" role="tabpanel">
                                    <div class="av-row" style="flex-wrap: wrap;">
                                        @forelse($halfDayDates as $date)
                                            <div class="av-person mb-2">
                                                <div class="av-circle"
                                                    style="background:linear-gradient(135deg,#10b981,#059669); font-weight: bold; font-size: 14px;">
                                                    {{ $date->format('d') }}
                                                    <span class="av-status late" style="background: #ef4444;"></span>
                                                </div>
                                                <div class="av-name">{{ $date->format('M y') }}</div>
                                            </div>
                                        @empty
                                            <span style="font-size: 0.85rem; color:#6b7280;">No half days.</span>
                                        @endforelse
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="time-card">
                            <div class="time-card-head">
                                <div class="time-date" id="timeDate">Time Today · {{ now()->format('D, d M Y') }}</div>
                                <a href="#" class="time-view-all">View All</a>
                            </div>
                            <div class="time-label">Current Time</div>
                            <div class="time-row">
                                <div>
                                    <span class="time-display" id="liveClock">--:--</span><span class="time-ampm"
                                        id="liveAmpm"></span>
                                </div>
                                <div class="time-btns">
                                    <button class="btn-clockout">Clock-out</button>
                                    <button class="btn-other">Other <i class="bi bi-chevron-down"
                                            style="font-size:10px;"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="holiday-container">
                            <div class="sparkle-wrap">
                                <div class="sparkle sq" style="left:5%; background:#ff4d4d; animation-delay:0s;"></div>
                                <div class="sparkle rect" style="left:15%; background:#4d79ff; animation-delay:1.2s;">
                                </div>
                                <div class="sparkle sq" style="left:25%; background:#ffcc00; animation-delay:0.5s;">
                                </div>
                                <div class="sparkle rect" style="left:35%; background:#9933ff; animation-delay:2.1s;">
                                </div>
                                <div class="sparkle sq" style="left:45%; background:#33cc33; animation-delay:1.5s;">
                                </div>
                                <div class="sparkle rect" style="left:55%; background:#ff3399; animation-delay:0.8s;">
                                </div>
                                <div class="sparkle sq" style="left:65%; background:#00cccc; animation-delay:2.5s;">
                                </div>
                                <div class="sparkle rect" style="left:75%; background:#ff8000; animation-delay:1.1s;">
                                </div>
                                <div class="sparkle sq" style="left:85%; background:#4d79ff; animation-delay:0.2s;">
                                </div>
                                <div class="sparkle rect" style="left:95%; background:#ff4d4d; animation-delay:1.8s;">
                                </div>
                                <div class="sparkle sq" style="left:10%; background:#ffcc00; animation-delay:3s;"></div>
                                <div class="sparkle rect" style="left:40%; background:#33cc33; animation-delay:3.5s;">
                                </div>
                                <div class="sparkle sq" style="left:70%; background:#9933ff; animation-delay:2.8s;">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-1"
                                style="position:relative; z-index:3;">
                                <span style="font-size: 15px; font-weight: 600; color: #141313;">Upcoming
                                    Holidays</span>
                                <!-- <a href="#" style="font-size: 13px; color: #5c51ac; text-decoration: none;">View All</a> -->
                            </div>

                            <div class="holiday-scroll-area" id="hSlider">
                                @forelse($upcomingHolidays as $holiday)
                                    <div class="holiday-item-slide">
                                        <div class="holiday-big-title">{{ $holiday->name ?? $holiday->title }}</div>
                                        <div class="d-flex align-items-center">
                                            <span
                                                style="color:#5c6784;">{{ \Carbon\Carbon::parse($holiday->date)->format('D, d F, Y') }}</span>
                                            <span class="floater-tag" style="background:#ea580c;">Company</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="holiday-item-slide">
                                        <div class="holiday-big-title">No Holidays</div>
                                        <div class="d-flex align-items-center">
                                            <span style="color:#5c6784;">No upcoming holidays scheduled.</span>
                                        </div>
                                    </div>
                                @endforelse
                            </div>

                            @if(count($upcomingHolidays) > 1)
                                <div class="d-flex justify-content-end mt-2"
                                    style="position:relative; z-index:3; gap:10px;">
                                    <i class="bi bi-chevron-left" style="cursor:pointer; color:#5c51ac;"
                                        onclick="document.getElementById('hSlider').scrollBy({left: -300, behavior: 'smooth'})"></i>
                                    <i class="bi bi-chevron-right" style="cursor:pointer; color:#5c51ac;"
                                        onclick="document.getElementById('hSlider').scrollBy({left: 300, behavior: 'smooth'})"></i>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
                <!-- CENTER: Pie chart -->
                <div class="col-12 col-md-5 col-xl-4">
                    <div class="pcard h-100 d-flex flex-column">
                        <div class="pcard-head mb-0">
                            <span class="pcard-title">Attendance Analysis</span>
                            <select class="form-select form-select-sm"
                                style="width:auto;font-size:12px;border-color:var(--border);border-radius:8px;">
                                <option>This Month</option>
                                <option>Last Month</option>
                                <option>This Year</option>
                            </select>
                        </div>

                        <p style="font-size:12px;color:var(--muted);margin-bottom:8px;">Based on current month's
                            attendance</p>

                        <div style="">
                            <canvas id="pieChart" data-ontime="{{ $onTimePiePercentage }}"
                                data-late="{{ $latePiePercentage }}" data-ot="{{ $otPiePercentage }}">
                            </canvas>
                        </div>

                        <div class="d-flex justify-content-center gap-4 mt-2 flex-wrap" style="font-size:12px;">
                            <div class="d-flex align-items-center gap-1">
                                <span
                                    style="width:10px;height:10px;border-radius:50%;background:#3b5bdb;display:inline-block;"></span>
                                <strong>On Time</strong>
                                <span style="color:var(--muted);">{{ $onTimePiePercentage }}%</span>
                            </div>

                            <div class="d-flex align-items-center gap-1">
                                <span
                                    style="width:10px;height:10px;border-radius:50%;background:#f06595;display:inline-block;"></span>
                                <strong>Late</strong>
                                <span style="color:var(--muted);">{{ $latePiePercentage }}%</span>
                            </div>

                            <div class="d-flex align-items-center gap-1">
                                <span
                                    style="width:10px;height:10px;border-radius:50%;background:#ffd43b;display:inline-block;"></span>
                                <strong>OT</strong>
                                <span style="color:var(--muted);">{{ $otPiePercentage }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /row 1 -->


            <!-- ══ ROW 3 ══ -->
            <div class="row g-3">
                <div class="col-12 col-lg-6">

                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <div class="highlight-section">
                                <h3 class="section-title">My Recent Leaves</h3>

                                <div class="tab-content mt-3" id="pills-tabContent">
                                    <div class="tab-pane fade show active" id="pills-leaves" role="tabpanel">

                                        @forelse($recentLeaves as $leave)
                                            <div class="profile-card d-flex align-items-center mb-3">
                                                <div class="profile-img d-flex align-items-center justify-content-center"
                                                    style="background: #f8fafc; color: #3b5bdb; border: 1px solid #e2e8f0; width: 45px; height: 45px; border-radius: 50%;">
                                                    <i class="bi bi-calendar2-week" style="font-size: 1.2rem;"></i>
                                                </div>

                                                <div class="profile-info ms-3 flex-grow-1">
                                                    <h6 class="mb-1"
                                                        style="font-size: 14px; font-weight: 600; color: #1a202c;">
                                                        {{ \Carbon\Carbon::parse($leave->leave_date_from)->format('d M, Y') }}
                                                        @if($leave->leave_date_from != $leave->to_leave_date)
                                                            -
                                                            {{ \Carbon\Carbon::parse($leave->to_leave_date)->format('d M, Y') }}
                                                        @endif
                                                    </h6>
                                                    <p class="mb-0" style="font-size: 12px; color: #64748b;">
                                                        {{ $leave->leave_subject ?? 'Leave Application' }}
                                                    </p>
                                                </div>

                                                @php
                                                    $badgeBg = '#f1f5f9';
                                                    $badgeText = '#64748b';

                                                    // 1 = Applied, 2 = In Progress
                                                    if ($leave->leave_status == 1 || $leave->leave_status == 2) {
                                                        $badgeBg = '#fef3c7';
                                                        $badgeText = '#d97706';
                                                    }
                                                    // 3 = Accepted
                                                    elseif ($leave->leave_status == 3) {
                                                        $badgeBg = '#d1fae5';
                                                        $badgeText = '#059669';
                                                    }
                                                    // 4 = Cancelled, 5 = Rejected
                                                    elseif ($leave->leave_status == 4 || $leave->leave_status == 5) {
                                                        $badgeBg = '#fee2e2';
                                                        $badgeText = '#dc2626';
                                                    }
                                                @endphp
                                                <div class="date-badge ms-auto"
                                                    style="background: {{ $badgeBg }}; color: {{ $badgeText }}; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                                    {{ $leave->status_label }}
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4">
                                                <span style="font-size: 0.85rem; color:#6b7280;">No recent leave
                                                    applications found.</span>
                                            </div>
                                        @endforelse

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-12 col-lg-6">



                    <div class="attendance-viz-card">
                        <div>
                            <h6 class="mb-4" style="color: #1a202c;">Monthly Analytics</h6>
                        </div>
                        <div class="chart-label-header">
                            <div class="d-flex flex-wrap gap-3">
                                <div class="filter-item">
                                    <label for="yearSelect" class="filter-label">Year</label>
                                    <select class="form-select filter-pill-input" id="yearSelect"
                                        style="min-width: 190px;" onchange="window.location.href='?year='+this.value">
                                        @php
                                            $currentYr = date('Y');
                                        @endphp
                                        @for($y = $currentYr; $y >= $currentYr - 4; $y--)
                                            <option value="{{ $y }}" {{ (isset($selectedYear) && $selectedYear == $y) ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div style="position: relative; height:220px; width:100%">
                            <canvas id="attendanceBarChart"
                                data-present="{{ json_encode($yearlyPresentData ?? array_fill(0, 12, 0)) }}"
                                data-absent="{{ json_encode($yearlyAbsentData ?? array_fill(0, 12, 0)) }}">
                            </canvas>
                        </div>

                        <div class="legend-indicator justify-content-center mt-3">
                            <span><span class="dot-status" style="background: #6366f1;"></span> Present</span>
                            <span><span class="dot-status" style="background: #f87171;"></span> Absent</span>
                        </div>
                    </div>


                </div>

            </div><!-- /content -->
        </div><!-- /main -->

        @include('employee.includes.footer_links')

</body>

</html>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Find all floating alerts
        let alerts = document.querySelectorAll('.floating-alert');

        if (alerts.length > 0) {
            // Wait 5 seconds, then fade them out
            setTimeout(function () {
                alerts.forEach(function (alertNode) {
                    // Check if Bootstrap JS is loaded and use its Alert API to dismiss smoothly
                    if (typeof bootstrap !== 'undefined') {
                        let bsAlert = new bootstrap.Alert(alertNode);
                        bsAlert.close();
                    } else {
                        // Fallback if bootstrap JS isn't initialized yet
                        alertNode.style.display = 'none';
                    }
                });
            }, 5000); // 5000ms = 5 seconds
        }
    });
</script>