<nav id="sidebar">
    <div class="sb-brand">
        <div>
            @php
                $employee = auth('employee')->user();
            @endphp

            @if($employee && $employee->photo)
                <img src="{{ asset('public/' . $employee->photo) }}" alt="Profile Photo"
                    style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
            @else
                <i class="bi bi-mortarboard-fill"></i>
            @endif
        </div>

        <div class="sb-brand-text">
            <div class="school">{{ $employee->employee_name ?? 'User' }}</div>
            <div class="sub">HRMS Portal</div>
        </div>

        <button class="sb-toggle-btn d-none d-lg-flex" id="desktopToggle" onclick="toggleDesktop()"
            title="Toggle sidebar">
            <i class="bi bi-chevron-left" id="toggleIcon"></i>
        </button>
    </div>

    <div class="sb-nav">
        <a href="{{ route('employee.dashboard') }}"
            class="sb-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
        </a>

        <a href="{{ route('employee.profile') }}"
            class="sb-link {{ request()->routeIs('employee.profile') ? 'active' : '' }}">
            <i class="bi bi-person-fill"></i><span>Profile</span>
        </a>

        <!-- <div class="sb-section-label">Leaves</div>
        <a href="{{ route('employee.apply_leave') }}"
            class="sb-link {{ request()->routeIs('employee.apply_leave') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i><span>Apply Leave</span>
        </a>
        <a href="{{ route('employee.leaves.manage') }}"
            class="sb-link {{ request()->routeIs('employee.leaves.manage') ? 'active' : '' }}">
            <i class="bi bi-calendar2-check"></i><span>Manage Leaves</span>
        </a>

        <div class="sb-section-label">Pay Slips & Attendance</div>
        <a href="{{ route('employee.payslip.index') }}"
            class="sb-link {{ request()->routeIs('employee.payslip.index') ? 'active' : '' }}">
            <i class="bi bi-calendar3"></i><span>Pay Slip</span>
        </a>
        <a href="{{ route('employee.attendance.index') }}"
            class="sb-link {{ request()->routeIs('employee.attendance.index') ? 'active' : '' }}">
            <i class="bi bi-calendar2-check"></i><span>Daily Attendance</span>
        </a>
        <a href="{{ route('employee.attendance.monthly') }}"
            class="sb-link {{ request()->routeIs('employee.attendance.monthly') ? 'active' : '' }}">
            <i class="bi bi-calendar2-check"></i><span>Monthly Attendance</span>
        </a> -->
    </div>

    <div class="sb-footer mt-auto">
        <!-- Change Password moved to the top of the footer block -->
        <a href="{{ route('employee.change_password') }}"
            class="sb-link {{ request()->routeIs('employee.change_password') ? 'active' : '' }}"
            style="margin-bottom: 15px;">
            <i class="bi bi-key"></i><span>Change Password</span>
        </a>

        <div class="sb-user">
            @php
                $employee = auth('employee')->user();
            @endphp

            <div>
                @if($employee && $employee->photo)
                    <img src="{{ asset('public/' . $employee->photo) }}" alt="Avatar"
                        style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                @else
                    <div
                        style="width: 40px; height: 40px; border-radius: 50%; background: #6c757d; color: white; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600;">
                        {{ strtoupper(substr($employee->employee_name ?? 'U', 0, 2)) }}
                    </div>
                @endif
            </div>

            <div class="uinfo">
                <div class="uname">{{ $employee->employee_name ?? 'User' }}</div>
                <div class="urole">{{ $employee->designation ?? 'Employee' }}</div>
            </div>

            <i class="bi bi-three-dots-vertical ms-auto" style="color:#4a5a7a;font-size:13px;"></i>
        </div>
    </div>
</nav>