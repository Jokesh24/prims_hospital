<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ env('APP_NAME') }}</title>
    @include('client.includes.header_links')


</head>

<body>

    <div id="overlay" onclick="closeMobile()"></div>

    <!-- ═══════════════ SIDEBAR ═══════════════ -->
    @include('client.includes.sidebar')


    <!-- ═══════════════ MAIN ═══════════════ -->
    <div id="main">

        @include('client.includes.header')


        <div class="content">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4 mt-2 shadow-sm" role="alert"
                    style="border-radius: 10px; border-left: 5px solid #10b981;">
                    <i class="bi bi-check-circle-fill me-2" style="color: #10b981;"></i>
                    <strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif


            <!-- ══ ROW 1 ══ -->
            <div class="row g-3 mb-4 mt-2">

                <!-- LEFT: stat-mini cards -->
                <div class="col-12 col-md-3 col-xl-3">

                    <div class="stat-group-card">

                        <!-- Total Employee -->
                        <div class="stat-item">
                            <div class="stat-icon-square">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="stat-info">
                                <div class="sm-lbl">Total Employee</div>
                                <div class="sm-val">{{ $totalEmployees }}</div>
                            </div>
                        </div>

                        <!-- New Employee (With Detailed Data) -->
                        <div class="stat-item flex-column align-items-start">
                            <div class="d-flex align-items-center w-100">
                                <div class="stat-icon-square">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="sm-lbl">Active Employee</div>
                                    <div class="sm-val">{{ $totalActive }}</div>
                                </div>
                            </div>

                            <!-- Expanded Data Section -->
                            <div class="stat-expanded-area">
                                <div class="mini-detail-row">
                                    <div class="mini-detail-item">
                                        <span class="dot" style="background:#3b5bdb;"></span>
                                        <span style="font-size: 0.8rem; color:#6b7280;">Male:
                                            <strong>{{ $maleCount }}</strong></span>
                                    </div>
                                    <div class="mini-detail-item">
                                        <span class="dot" style="background:#d63384;"></span>
                                        <span style="font-size: 0.8rem; color:#6b7280;">Female:
                                            <strong>{{ $femaleCount }}</strong></span>
                                    </div>
                                    @php
                                        $malePercent = $totalActive > 0 ? ($maleCount / $totalActive) * 100 : 0;
                                        $femalePercent = $totalActive > 0 ? ($femaleCount / $totalActive) * 100 : 0;
                                    @endphp
                                </div>
                                <div class="sm-bar-wrap"
                                    style="height: 6px; background: #f3f4f6; border-radius: 10px; display: flex; overflow: hidden;">
                                    <div style="width:{{ $malePercent }}%; background:#3b5bdb;"></div>
                                    <div style="width:{{ $femalePercent }}%; background:#d63384;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Present -->
                        <div class="stat-item">
                            <div class="stat-icon-square">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <div class="stat-info">
                                <div class="sm-lbl">Today Present</div>
                                <div class="sm-val">{{ $presentCount }}</div>
                            </div>
                        </div>

                        <!-- Today Absent -->
                        <div class="stat-item">
                            <div class="stat-icon-square">
                                <i class="bi bi-person-dash"></i>
                            </div>
                            <div class="stat-info">
                                <div class="sm-lbl">Today Absent</div>
                                <div class="sm-val">{{ $absentCount }}</div>
                            </div>
                        </div>

                    </div>

                </div>



                <!-- RIGHT: stacked cards matching image -->
                <div class="col-12 col-md-4 col-xl-5">
                    <div class="right-col">

                        <div class="onleave-card">
                            <!-- Tab Navigation -->
                            <ul class="nav nav-pills card-tabs-nav" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="pills-leave-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-leave" type="button" role="tab">
                                        On Leave <span class="tab-count">{{ $onLeaveCount }}</span>
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-late-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-late" type="button" role="tab">
                                        Late Login <span class="tab-count">{{ $lateCount }}</span>
                                    </button>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content" id="pills-tabContent">

                                <!-- Tab 1: On Leave -->
                                <div class="tab-pane fade show active" id="pills-leave" role="tabpanel">
                                    <div class="av-row">
                                        @forelse($onLeaveEmployees as $leave)
                                            <div class="av-person">
                                                <div class="av-circle" style="overflow:hidden; border:none;">
                                                    <img src="{{ $leave->employee->photo ? asset('public/' . $leave->employee->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($leave->employee->employee_name) }}"
                                                        style="width:100%; height:100%; object-fit:cover;" alt="Profile">
                                                    <span class="av-status leave"></span>
                                                </div>
                                                <div class="av-name">{{ $leave->employee->employee_name }}</div>
                                            </div>
                                        @empty
                                            <div class="empty-state-text"
                                                style="color: #9ca3af; padding: 1rem; text-align: center; width: 100%;">
                                                <i class="bi bi-check-circle"
                                                    style="display: block; font-size: 1.5rem; margin-bottom: 5px;"></i>
                                                No employees are on leave today.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Tab 2: Late Logins -->
                                <div class="tab-pane fade" id="pills-late" role="tabpanel">
                                    <div class="av-row">
                                        @forelse($lateEmployees as $att)
                                            <div class="av-person">
                                                <div class="av-circle" style="overflow:hidden; border:none;">
                                                    <img src="{{ $att->employee->photo ? asset('public/' . $att->employee->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($att->employee->employee_name) }}"
                                                        style="width:100%; height:100%; object-fit:cover;" alt="Profile">
                                                    <span class="av-status late"></span>
                                                </div>
                                                <div class="av-name">{{ $att->employee->employee_name }}</div>
                                            </div>
                                        @empty
                                            <div class="empty-state-text"
                                                style="color: #9ca3af; padding: 1rem; text-align: center; width: 100%;">
                                                <i class="bi bi-shield-check"
                                                    style="display: block; font-size: 1.5rem; margin-bottom: 5px;"></i>
                                                Everyone logged in on time today.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- 3. Time Today (purple card) -->
                        <div class="time-card">
                            <div class="time-card-head">
                                <div class="time-date" id="timeDate">Time Today · Fri, 12 Jun 2026</div>
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
                            <!-- Sparkles/Confetti Layer -->
                            <div class="sparkle-wrap">
                                <!-- We add many to make it look like the image -->
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
                                <!-- Duplicate some with different delays for density -->
                                <div class="sparkle sq" style="left:10%; background:#ffcc00; animation-delay:3s;"></div>
                                <div class="sparkle rect" style="left:40%; background:#33cc33; animation-delay:3.5s;">
                                </div>
                                <div class="sparkle sq" style="left:70%; background:#9933ff; animation-delay:2.8s;">
                                </div>
                            </div>

                            <!-- Header -->
                            <div class="d-flex justify-content-between align-items-center mb-1"
                                style="position:relative; z-index:3;">
                                <span style="font-size: 15px; font-weight: 600; color: #141313;">
                                    Holidays</span>
                                <a href="{{ route('client.holidays') }}"
                                    style="font-size: 13px; color: #5c51ac; text-decoration: none;">View All</a>
                            </div>

                            <div class="holiday-scroll-area" id="hSlider">
                                @forelse($upcomingHolidays as $holiday)
                                    <div class="holiday-item-slide">
                                        <div class="holiday-big-title">{{ $holiday->title }}</div>
                                        <div class="d-flex align-items-center">
                                            <span style="color:#5c6784;">{{ $holiday->date->format('D, d F, Y') }}</span>

                                            {{-- Dynamic Tag based on type --}}
                                            @php
                                                $colors = [1 => '#5c51ac', 2 => '#ea580c', 3 => '#059669', 4 => '#7c3aed'];
                                                $labels = [1 => 'General', 2 => 'Public', 3 => 'Company', 4 => 'Optional'];
                                            @endphp

                                            <span class="floater-tag"
                                                style="background: {{ $colors[$holiday->type] ?? '#5c51ac' }};">
                                                {{ $labels[$holiday->type] ?? 'Holiday' }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="holiday-item-slide">
                                        <div class="holiday-big-title">No upcoming holidays</div>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Navigation Dots or Arrows -->
                            <div class="d-flex justify-content-end mt-2"
                                style="position:relative; z-index:3; gap:10px;">
                                <i class="bi bi-chevron-left" style="cursor:pointer; color:#5c51ac;"
                                    onclick="document.getElementById('hSlider').scrollBy({left: -300, behavior: 'smooth'})"></i>
                                <i class="bi bi-chevron-right" style="cursor:pointer; color:#5c51ac;"
                                    onclick="document.getElementById('hSlider').scrollBy({left: 300, behavior: 'smooth'})"></i>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- CENTER: Pie chart -->
                <!-- CENTER: Attendance Analysis (FIXED HEIGHT CONTAINER) -->
                <div class="col-12 col-md-5 col-xl-4">
                    <div class="pcard h-100 d-flex flex-column">
                        <div class="pcard-head mb-0">
                            <span class="pcard-title">Attendance Analysis</span>
                        </div>
                        <!-- IMPORTANT: Explicit height for chart container -->
                        <div style="position: relative; height: 250px; width: 100%; margin-top: 15px;">
                            <canvas id="pieChart"></canvas>
                        </div>
                        <div class="d-flex justify-content-center gap-4 mt-2 flex-wrap" style="font-size:12px;">
                            <div class="d-flex align-items-center gap-1"><span
                                    style="width:10px;height:10px;border-radius:50%;background:#3b5bdb;display:inline-block;"></span><strong>On
                                    Time</strong></div>
                            <div class="d-flex align-items-center gap-1"><span
                                    style="width:10px;height:10px;border-radius:50%;background:#f06595;display:inline-block;"></span><strong>Late</strong>
                            </div>
                            <div class="d-flex align-items-center gap-1"><span
                                    style="width:10px;height:10px;border-radius:50%;background:#ffd43b;display:inline-block;"></span><strong>OT</strong>
                            </div>
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
                            <h3 class="section-title">Team Highlights</h3>

                            <!-- Nav Tabs -->
                            <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                                <!-- Birthdays Tab -->
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="pills-birthdays-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-birthdays" type="button" role="tab">
                                        <i class="bi bi-cake2-fill me-2"></i>Birthdays
                                    </button>
                                </li>

                                <!-- Anniversaries Tab -->
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-anniversary-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-anniversary" type="button" role="tab">
                                        <i class="bi bi-award-fill me-2"></i>Anniversaries
                                    </button>
                                </li>

                                <!-- New Joiners Tab -->
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pills-newjoiners-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-newjoiners" type="button" role="tab">
                                        <i class="bi bi-person-plus-fill me-2"></i>New Joiners
                                    </button>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content" id="pills-tabContent">

                                <!-- Birthdays Section -->
                                <div class="tab-pane fade show active" id="pills-birthdays" role="tabpanel">
                                    @forelse($birthdays as $emp)
                                        <div class="profile-card">
                                            <img src="{{ $emp->photo ? asset('public/' . $emp->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($emp->employee_name) }}"
                                                alt="Profile" class="profile-img">
                                            <div class="profile-info">
                                                <h6>{{ $emp->employee_name }}</h6>
                                                <p>{{ $emp->designation }}</p>
                                            </div>
                                            <div class="date-badge">{{ $emp->dob->format('d M') }}</div>
                                        </div>
                                    @empty
                                        <p class="text-muted p-3">No upcoming birthdays.</p>
                                    @endforelse
                                </div>

                                <!-- Anniversary Section -->
                                <div class="tab-pane fade" id="pills-anniversary" role="tabpanel">
                                    @forelse($anniversaries as $emp)
                                        <div class="profile-card">
                                            <img src="{{ $emp->photo ? asset('public/' . $emp->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($emp->employee_name) }}"
                                                alt="Profile" class="profile-img">
                                            <div class="profile-info">
                                                <h6>{{ $emp->employee_name }}</h6>
                                                <p>{{ $emp->designation }}</p>
                                            </div>
                                            <div class="date-badge">
                                                @if($emp->marriage_date->age > 0)
                                                    {{ $emp->marriage_date->age }} Years •
                                                @endif
                                                {{ $emp->marriage_date->format('d M') }}
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted p-3">No upcoming work anniversaries.</p>
                                    @endforelse
                                </div>

                                <!-- New Joiners Section -->
                                <div class="tab-pane fade" id="pills-newjoiners" role="tabpanel">
                                    @forelse($newJoiners as $emp)
                                        <div class="profile-card">
                                            <img src="{{ $emp->photo ? asset('public/' . $emp->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($emp->employee_name) }}"
                                                alt="Profile" class="profile-img">
                                            <div class="profile-info">
                                                <h6>{{ $emp->employee_name }}</h6>
                                                <p>{{ $emp->designation }}</p>
                                            </div>
                                            <div class="date-badge">Joined {{ $emp->joining_date->format('d M') }}</div>
                                        </div>
                                    @empty
                                        <p class="text-muted p-3">No new joiners recently.</p>
                                    @endforelse
                                </div>

                            </div> <!-- End Tab Content -->
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-12 col-lg-6">



                <div class="attendance-viz-card">
                    <div>
                        <h6 class=" mb-4" style="color: #1a202c;">Monthly Analytics</h6>
                        <!-- <small class="text-muted">Attendance trends across departments</small> -->
                    </div>
                    <div class="chart-label-header">

                        <div class="d-flex flex-wrap gap-3">


                            <!-- Year Group -->
                            <div class="filter-item">
                                <label for="yearSelect" class="filter-label">Year</label>
                                <select class="form-select filter-pill-input" id="yearSelect"
                                    onchange="window.location.href='?year='+this.value">
                                    @foreach(range(date('Y'), date('Y') - 2) as $y)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Canvas -->
                    <div style="position: relative; height:220px; width:100%">
                        <!-- <canvas id="attendanceBarChart"></canvas> -->
                        <canvas id="attendanceBarChart1"></canvas>
                    </div>

                    <!-- Custom Legend -->
                    <!-- Custom Legend Updated for Present/Absent -->
                    <div class="legend-indicator justify-content-center mt-3">
                        <span><span class="dot-status" style="background: #6366f1;"></span> Present</span>
                        <span><span class="dot-status" style="background: #f87171;"></span> Absent</span>
                    </div>
                </div>


            </div>

        </div><!-- /content -->
    </div><!-- /main -->

    @include('client.includes.footer_links')

</body>

</html>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        // 1. STATIC PIE CHART (No dynamic dependencies)
        const pieCanvas = document.getElementById("pieChart");
        if (pieCanvas) {
            new Chart(pieCanvas, {
                type: "pie",
                data: {
                    labels: ["On Time", "Late", "Over Time"],
                    datasets: [{
                        data: [70, 20, 10], // Fixed static values
                        backgroundColor: ['#3b5bdb', '#f06595', '#ffd43b'],
                        borderWidth: 2,
                        borderColor: "#fff"
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });
        }

        // 2. DYNAMIC BAR CHART
        const barCanvas = document.getElementById('attendanceBarChart1');
        if (barCanvas) {
            const ctx = barCanvas.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [
                        {
                            label: 'Present',
                            data: {!! json_encode($monthlyStats['present'] ?? []) !!},
                            backgroundColor: '#6366f1',
                            borderRadius: 5
                        },
                        {
                            label: 'Absent',
                            data: {!! json_encode($monthlyStats['absent'] ?? []) !!},
                            backgroundColor: '#f87171',
                            borderRadius: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, beginAtZero: true }
                    }
                }
            });
        }
    });
</script>