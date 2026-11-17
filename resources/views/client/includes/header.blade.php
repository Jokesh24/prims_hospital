<header class="topbar">
        <button class="mob-toggle" onclick="openMobile()"><i class="bi bi-list"></i></button>

        @php
                // Identify if the logged-in user is a Client or an Employee
                $isClient = auth('client')->check();
                $isEmployee = auth('employee')->check();

                $initials = '??';
                $lastLogin = null;
                $displayName = 'User';
                $displayRole = 'Staff';

                if ($isClient) {
                        $user = auth('client')->user();
                        $displayName = $user->client_name;
                        $displayRole = 'Administrator';
                        $initials = $user ? strtoupper(substr($user->client_name, 0, 2)) : '??';

                        // Fetch the *previous* successful login for this client
                        $lastLogin = \App\Models\ClientLoginLog::where('client_id', $user->id)
                                ->where('status', 1)
                                ->where('log_comment', 'LIKE', 'Client Login%') // Filter specifically for client logins
                                ->orderBy('login_datetime', 'desc')
                                ->skip(1) // Skip the current active session
                                ->first();
                } elseif ($isEmployee) {
                        $user = auth('employee')->user();
                        $displayName = $user->employee_name;
                        $displayRole = $user->designationInfo->designation_name ?? 'Employee';
                        $initials = $user ? strtoupper(substr($user->employee_name, 0, 2)) : '??';

                        // Fetch the *previous* successful login for this employee
                        $lastLogin = \App\Models\ClientLoginLog::where('client_id', $user->client_id)
                                ->where('status', 1)
                                ->where('log_comment', 'LIKE', 'Employee Login successful for ID: ' . $user->id . '%')
                                ->orderBy('login_datetime', 'desc')
                                ->skip(1) // Skip the current active session
                                ->first();
                }
            @endphp

        <span class="topbar-title d-flex align-items-center">
                HRMS Dashboard
                @if($isClient)
                        <span class="badge bg-primary ms-2 shadow-sm"
                                style="font-size: 10px; letter-spacing: 0.5px;">ADMIN</span>
                @elseif($isEmployee)
                        <span class="badge bg-secondary ms-2 shadow-sm"
                                style="font-size: 10px; letter-spacing: 0.5px;">{{ strtoupper($displayRole) }}</span>
                @endif
                <small id="todayDate" class="ms-2"></small>
        </span>

        <div class="top-icon ms-auto me-3"><i class="bi bi-bell-fill"></i><span class="dot"></span></div>
        <div class="dropdown">

                <button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <div
                                style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#2e7df7,#0ec97f);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#fff;cursor:pointer;flex-shrink:0; box-shadow: 0 2px 5px rgba(0,0,0,0.15);">
                                {{ $initials }}
                        </div>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2"
                        style="border-radius: 12px; min-width: 240px; overflow: hidden;">

                        <!-- Identity Header -->
                        <li class="px-3 py-3" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <div class="d-flex align-items-center">
                                        <div
                                                style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#2e7df7,#0ec97f);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:#fff;flex-shrink:0; margin-right: 12px;">
                                                {{ $initials }}
                                        </div>
                                        <div>
                                                <div class="fw-bold text-dark"
                                                        style="font-size: 14px; line-height: 1.2;">{{ $displayName }}
                                                </div>
                                                <div class="text-muted"
                                                        style="font-size: 11px; font-weight: 600; margin-top: 2px;">
                                                        {{ $displayRole }}</div>
                                        </div>
                                </div>
                        </li>

                        <li class="px-3 py-2 mb-2 mx-2 mt-3"
                                style="background-color: #f8fafc; border-left: 4px solid #2e7df7; border-radius: 6px;">

                                <!-- Header with Shield Icon -->
                                <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-shield-check text-primary me-2" style="font-size: 14px;"></i>
                                        <span class="mb-0 fw-bold text-dark"
                                                style="font-size: 12px; letter-spacing: 0.3px;">Last Login
                                                Details</span>
                                </div>

                                @if($lastLogin)
                                        <!-- Formatted Data Rows -->
                                        <div style="font-size: 11px; line-height: 1.6; padding-left: 22px;">
                                                <div class="d-flex align-items-start mb-1 text-muted">
                                                        <i class="bi bi-clock me-2 mt-1" style="font-size: 10px;"></i>
                                                        <span class="fw-bold text-secondary">
                                                                {{ $lastLogin->login_datetime->timezone('Asia/Kolkata')->format('d M Y, h:i A T') }}
                                                        </span>
                                                </div>
                                                <div class="d-flex align-items-center text-muted">
                                                        <i class="bi bi-geo-alt me-2" style="font-size: 10px;"></i>
                                                        <span>IP: <span
                                                                        class="fw-bold text-secondary">{{ $lastLogin->ip }}</span></span>
                                                </div>
                                        </div>
                                @else
                                        <!-- First Login State -->
                                        <div style="padding-left: 22px;">
                                                <span
                                                        class="badge bg-success-subtle text-success border border-success-subtle mt-1">
                                                        <i class="bi bi-stars me-1"></i> First Login
                                                </span>
                                        </div>
                                @endif
                        </li>

                        <li>
                                <a class="dropdown-item py-2 small fw-bold text-secondary" href="#">
                                        <i class="bi bi-gear me-2"></i> Account Settings
                                </a>
                        </li>
                        <li>
                                <hr class="dropdown-divider mb-1">
                        </li>

                        <li>
                                <form method="POST" action="{{ route('client.logout') }}">
                                        @csrf
                                        <button type="submit"
                                                class="dropdown-item py-2 mb-1 small fw-bold text-danger border-0 bg-transparent text-start w-100">
                                                <i class="bi bi-box-arrow-right me-2"></i> Secure Logout
                                        </button>
                                </form>
                        </li>
                </ul>
        </div>
</header>