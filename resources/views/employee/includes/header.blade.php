<header class="topbar">
        <button class="mob-toggle" onclick="openMobile()"><i class="bi bi-list"></i></button>
        <span class="topbar-title">HRMS Dashboard <small id="todayDate"></small></span>

        <div class="top-icon ms-1"><i class="bi bi-bell-fill"></i><span class="dot"></span></div>
        <div class="dropdown">

                @php
                        // Get current user for initials
                        $employee = Auth::guard('employee')->user();
                        $initials = $employee ? strtoupper(substr($employee->employee_name, 0, 2)) : '??';

                        // Fetch the *previous* successful login for this employee
                        $lastLogin = \App\Models\EmployeeLoginLog::where('employee_id', Auth::guard('employee')->id())
                                ->where('status', 1)
                                ->orderBy('login_datetime', 'desc')
                                ->skip(1) // Skip the current active session
                                ->first();
                @endphp

                <button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <div
                                style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#2e7df7,#0ec97f);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#fff;cursor:pointer;flex-shrink:0;">
                                {{ $initials }}
                        </div>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2"
                        style="border-radius: 10px; min-width: 220px;">

                        <li class="px-3 py-2 mb-2 mx-2 mt-1"
                                style="background-color: #f8fafc; border-left: 4px solid #2e7df7; border-radius: 6px;">

                                <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-shield-check text-primary me-2" style="font-size: 14px;"></i>
                                        <span class="mb-0 fw-bold text-dark"
                                                style="font-size: 12px; letter-spacing: 0.3px;">Last Login
                                                Details</span>
                                </div>

                                @if($lastLogin)
                                        <div style="font-size: 11px; line-height: 1.6; padding-left: 22px;">
                                                <div class="d-flex align-items-start mb-1 text-muted">
                                                        <i class="bi bi-clock me-2 mt-1" style="font-size: 10px;"></i>
                                                        <span class="fw-bold text-secondary">
                                                                {{ \Carbon\Carbon::parse($lastLogin->login_datetime)->timezone('Asia/Kolkata')->format('d M Y, h:i A T') }}
                                                        </span>
                                                </div>
                                                <div class="d-flex align-items-center text-muted">
                                                        <i class="bi bi-geo-alt me-2" style="font-size: 10px;"></i>
                                                        <span>IP: <span
                                                                        class="fw-bold text-secondary">{{ $lastLogin->ip }}</span></span>
                                                </div>
                                        </div>
                                @else
                                        <div style="padding-left: 22px;">
                                                <span
                                                        class="badge bg-success-subtle text-success border border-success-subtle mt-1">
                                                        <i class="bi bi-stars me-1"></i> First Login
                                                </span>
                                        </div>
                                @endif
                        </li>

                        <li>
                                <a class="dropdown-item py-2 small fw-bold" href="#">
                                        <i class="bi bi-gear me-2"></i> Settings
                                </a>
                        </li>
                        <li>
                                <hr class="dropdown-divider">
                        </li>

                        <li>
                                <form method="POST" action="{{ route('employee.logout') }}">
                                        @csrf
                                        <button type="submit"
                                                class="dropdown-item py-2 small fw-bold text-danger border-0 bg-transparent text-start w-100">
                                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                                        </button>
                                </form>
                        </li>
                </ul>
        </div>
</header>