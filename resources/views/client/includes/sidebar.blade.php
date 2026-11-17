@php
    $isClient = auth('client')->check();
    $isEmployee = auth('employee')->check();
    $userPerms = [];

    // If an employee is logged in, fetch their specific accessible sections
    if ($isEmployee) {
        $access = \App\Models\EmployeeLoginAccess::where('employee_id', auth('employee')->id())
            ->where('status', 1)
            ->where('trash', 0)
            ->first();

        $userPerms = $access ? ($access->accessible_sections ?? []) : [];
    }

    // Helper function to verify access
    $hasAccess = function ($module) use ($isClient, $isEmployee, $userPerms) {
        if ($isClient)
            return true; // Client (Admin) always sees everything
        if ($isEmployee)
            return in_array($module, $userPerms);
        return false;
    };
@endphp

<nav id="sidebar">
    <div class="sb-brand">
        <div>
            @if(session('ClientUser'))
                @php $clientData = \App\Models\Client::find(session('ClientUser')); @endphp
                @if($clientData && $clientData->logo)
                    <img src="{{ asset('public/uploads/clients/' . $clientData->logo) }}" alt="Company Logo"
                        style="width: 40px; height: 40px; object-fit: contain;">
                @else
                    <i class="bi bi-mortarboard-fill"></i>
                @endif
            @else
                <i class="bi bi-mortarboard-fill"></i>
            @endif
        </div>

        <div class="sb-brand-text">
            <div class="school">
                {{ session('ClientName') }}
            </div>
            <div class="sub">HRMS Portal</div>
        </div>

        <button class="sb-toggle-btn d-none d-lg-flex" id="desktopToggle" onclick="toggleDesktop()"
            title="Toggle sidebar">
            <i class="bi bi-chevron-left" id="toggleIcon"></i>
        </button>
    </div>

    <div class="sb-nav">

        {{-- Dashboard --}}
        <a href="{{ route('client.dashboard') }}"
            class="sb-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        @if(isset($isSuperAdmin) && $isSuperAdmin && $isClient)
            <a href="{{ route('manageClients') }}"
                class="sb-link {{ request()->routeIs('manageClients') ? 'active' : '' }}">
                <i class="bi bi-buildings-fill"></i>
                <span>Clients</span>
            </a>
        @endif


        {{-- Master Data --}}
        @if($hasAccess('holidays') || $hasAccess('paidleaves') || $hasAccess('shifts') || $hasAccess('departmentsettings'))
            <div class="sb-section-label">Master Data</div>

            @if($hasAccess('holidays'))
                <a href="{{ route('client.holidays') }}"
                    class="sb-link {{ request()->routeIs('client.holidays') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event-fill"></i>
                    <span>Holidays</span>
                </a>
            @endif

            @if($hasAccess('paidleaves'))
                <a href="{{ route('client.paidleaves') }}"
                    class="sb-link {{ request()->routeIs('client.paidleaves') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-check-fill"></i>
                    <span>Paid Leaves</span>
                </a>
            @endif

            @if($hasAccess('shifts'))
                <a href="{{ route('shifts.manage') }}"
                    class="sb-link {{ request()->routeIs('shifts.manage') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-check-fill"></i>
                    <span>Shifts</span>
                </a>
            @endif

            @if($hasAccess('departmentsettings'))
                <a href="{{ route('departmentSettings') }}"
                    class="sb-link {{ request()->routeIs('departmentSettings') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-check-fill"></i>
                    <span>Department Settings</span>
                </a>
            @endif

        @endif

        {{-- Employee --}}
        @if($hasAccess('departments') || $hasAccess('sub_departments') || $hasAccess('designations') || $hasAccess('roster') || $hasAccess('employees'))
            <div class="sb-section-label">Employee</div>

            @if($hasAccess('departments'))
                <a href="{{ route('client.departments') }}"
                    class="sb-link {{ request()->routeIs('client.departments') ? 'active' : '' }}">
                    <i class="bi bi-diagram-3-fill"></i>
                    <span>Departments</span>
                </a>
            @endif

            @if($hasAccess('sub_departments'))
                <a href="{{ route('client.sub_departments') }}"
                    class="sb-link {{ request()->routeIs('client.sub_departments') ? 'active' : '' }}">
                    <i class="bi bi-diagram-2-fill"></i>
                    <span>Sub-Departments</span>
                </a>
            @endif

            @if($hasAccess('designations'))
                <a href="{{ route('client.designations') }}"
                    class="sb-link {{ request()->routeIs('client.designations') ? 'active' : '' }}">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Designations</span>
                </a>
            @endif

            @if($hasAccess('roster'))
                <a href="{{ route('roster.manage') }}"
                    class="sb-link {{ request()->routeIs('roster.manage') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Roster Manage</span>
                </a>
            @endif

            @if($hasAccess('employees'))
                <a href="{{ route('client.employees') }}"
                    class="sb-link {{ request()->routeIs('client.employees') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Employees</span>
                </a>
            @endif
        @endif


        {{-- Leaves --}}
        @if($hasAccess('manage_leaves') || $isEmployee)
            <div class="sb-section-label">Leaves</div>

            @if($hasAccess('manage_leaves'))
                <a href="{{ route('client.leaves') }}"
                    class="sb-link {{ request()->routeIs('client.leaves') ? 'active' : '' }}">
                    <i class="bi bi-calendar-minus-fill"></i>
                    <span>Manage Leaves</span>
                </a>
            @endif

            @if($isEmployee)
                <!-- Pending Approvals for Employees with Login Access -->
                <a href="{{ route('employee.leave_approvals') }}"
                    class="sb-link {{ request()->routeIs('employee.leave_approvals') ? 'active' : '' }}">
                    <i class="bi bi-check2-square"></i>
                    <span>Pending Approvals</span>
                </a>
            @endif
        @endif


        {{-- Payroll --}}
        @if($hasAccess('daily_attendance') || $hasAccess('monthly_attendance') || $hasAccess('payslips') || $hasAccess('advances'))
            <div class="sb-section-label">Payroll</div>

            @if($hasAccess('daily_attendance'))
                <a href="{{ route('manageAttendance') }}"
                    class="sb-link {{ request()->routeIs('manageAttendance') ? 'active' : '' }}">
                    <i class="bi bi-person-check-fill"></i>
                    <span>Daily Attendance</span>
                </a>
            @endif

            @if($hasAccess('monthly_attendance'))
                <a href="{{ route('monthlyAttendance') }}"
                    class="sb-link {{ request()->routeIs('monthlyAttendance') ? 'active' : '' }}">
                    <i class="bi bi-calendar-range-fill"></i>
                    <span>Monthly Attendance</span>
                </a>
            @endif

            @if($hasAccess('payslips'))
                <a href="{{ route('managePayroll') }}"
                    class="sb-link {{ request()->routeIs('managePayroll') ? 'active' : '' }}">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>Payslips</span>
                </a>
            @endif

            @if($hasAccess('otDetails'))
                <a href="{{ route('otEmployeesList') }}"
                    class="sb-link {{ request()->routeIs('otEmployeesList') ? 'active' : '' }}">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>OT Details</span>
                </a>

                <!-- <a href="{{ route('assignOT') }}"
                    class="sb-link {{ request()->routeIs('assignOT') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-plus-fill"></i>
                    <span>Assign OT</span>
                </a> -->
            @endif

            @if($hasAccess('advances'))
                <a href="{{ route('advances') }}" class="sb-link {{ request()->routeIs('advances') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i>
                    <span>Advances</span>
                </a>
            @endif
        @endif


        {{-- Settings --}}
        @if($hasAccess('payroll_settings') || $isClient)
            <div class="sb-section-label">Settings</div>

            @if($hasAccess('payroll_settings'))
                <a href="{{ route('manageComponents') }}"
                    class="sb-link {{ request()->routeIs('manageComponents') ? 'active' : '' }}">
                    <i class="bi bi-sliders2"></i>
                    <span>Payroll Settings</span>
                </a>
            @endif

            @if($isClient)
                <!-- Leave Workflows Configuration (Client Only) -->
                <a href="{{ route('client.leave_flows.index') }}"
                    class="sb-link {{ request()->routeIs('client.leave_flows.*') ? 'active' : '' }}">
                    <i class="bi bi-diagram-3-fill"></i>
                    <span>Leave Workflows</span>
                </a>

                <!-- Only the Client (Owner) can assign Login Access to employees -->
                <a href="{{ route('client.login_access') }}"
                    class="sb-link {{ request()->routeIs('client.login_access', 'login_access.create', 'login_access.edit') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Login Access</span>
                </a>
            @endif

            <a href="{{ route('changePassword') }}"
                class="sb-link {{ request()->routeIs('changePassword') ? 'active' : '' }}">
                <i class="bi bi-key-fill"></i>
                <span>Change Password</span>
            </a>
        @endif

        <form action="{{ route('client.logout') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="sb-link border-0 bg-transparent w-100 text-start">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </button>
        </form>

    </div>
</nav>