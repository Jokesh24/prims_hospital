<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CRM Dashboard - Manage Attendance</title>
    @include('super_admin.includes.header_links')
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body>
    @include('super_admin.includes.sidebar')
    @include('super_admin.includes.header')

    <div class="content">
        <div class="row mb-3">
            <div class="col-lg-12">
                <div class="card border-0">
                    <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                        <h5 class="mb-0 fw-semibold text-dark">Manage Attendance</h5>

                        <div class="d-flex align-items-center gap-2">
                            <a href="javascript:history.back()" class="btn btn-primary" title="Back">
                                <i class="bi bi-chevron-double-left me-1"></i>
                            </a>

                            <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#settingsModal">
                                <i class="bi bi-gear-fill me-1"></i> Timings
                            </button>

                            <a href="{{ route('add-attendance') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-1"></i> Add Single
                            </a>
                            <a href="{{ route('bulk-attendance') }}" class="btn btn-outline-primary">
                                <i class="bi bi-grid-3x3-gap-fill me-1"></i> Bulk Attendance
                            </a>

                            <div class="dropdown">
                                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white border-1" data-bs-toggle="dropdown">
                                    <i class="bi bi-file-earmark-arrow-down me-1"></i> Export
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-2">
                                    <li><a href="#" class="dropdown-item"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</a></li>
                                    <li><a href="#" class="dropdown-item"><i class="bi bi-file-earmark-excel me-1"></i> Excel</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-bottom mt-3 company">
            <div class="border-bottom">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="fw-semibold mb-0">Attendance Details</h5>

                        <form method="GET" action="{{ route('attendence-today') }}" class="d-flex align-items-center flex-wrap justify-content-end gap-2">
                            
                            <input type="text" name="search" class="form-control" placeholder="Search Employee..." 
                                   value="{{ request('search') }}" style="width:180px;">

                            <input type="date" name="date" id="filterDate" class="form-control" 
                                   value="{{ $date }}" style="width:140px;" placeholder="Select Date">

                           <select name="department" class="form-select" style="width:180px;">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->name }}" {{ request('department') == $dept->name ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>

                            <select name="status" class="form-select" style="width:140px;">
                                <option value="">Select Status</option>
                                <option value="Present" {{ request('status') == 'Present' ? 'selected' : '' }}>Present</option>
                                <option value="Late" {{ request('status') == 'Late' ? 'selected' : '' }}>Late</option>
                                <option value="Half Day" {{ request('status') == 'Half Day' ? 'selected' : '' }}>Half Day</option>
                                <option value="Absent" {{ request('status') == 'Absent' ? 'selected' : '' }}>Absent</option>
                                <option value="On Leave" {{ request('status') == 'On Leave' ? 'selected' : '' }}>On Leave</option>
                            </select>

                            <button type="submit" class="btn btn-primary text-white" title="Search">
                                <i class="bi bi-search"></i>
                            </button>
                            <a href="{{ route('attendence-today') }}" class="btn btn-light border-1" title="Reset Filters">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Emp ID</th>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Date</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Hours</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $key => $row)
                                <tr>
                                    <td>{{ $attendances->firstItem() + $key }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $row->employee->employee_id ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $row->employee->employee_name ?? 'Unknown' }}</span>
                                    </td>
                                    <td>{{ $row->employee->department ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($row->attendance_date)->format('d M Y') }}</td>
                                    <td>
                                        {{ $row->check_in_time ? \Carbon\Carbon::parse($row->check_in_time)->format('h:i A') : '—' }}
                                    </td>
                                    <td>
                                        {{ $row->check_out_time ? \Carbon\Carbon::parse($row->check_out_time)->format('h:i A') : '—' }}
                                    </td>
                                    <td>{{ $row->working_hours ?? '—' }}</td>
                                    <td>
                                        @php
                                            $badgeClass = 'secondary';
                                            if($row->status == 'Present') $badgeClass = 'success';
                                            elseif($row->status == 'Late') $badgeClass = 'warning';
                                            elseif($row->status == 'Half Day') $badgeClass = 'warning';
                                            elseif($row->status == 'Absent') $badgeClass = 'danger';
                                            elseif($row->status == 'On Leave') $badgeClass = 'info';
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}-subtle text-{{ $badgeClass }} border border-{{ $badgeClass }}-subtle px-3 py-2">
                                            {{ $row->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center gap-2">
                                            
                                            @if(($row->status == 'Present' || $row->status == 'Late' || $row->status == 'Half Day') && $row->check_in_time && !$row->check_out_time)
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="openCheckoutModal({{ $row->id }}, '{{ addslashes($row->employee->employee_name) }}')" 
                                                        title="Mark Check-Out">
                                                    <i class="bi bi-box-arrow-right"></i> Out
                                                </button>
                                            @endif

                                           
    <a href="{{ route('edit-attendance', $row->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
        <i class="bi bi-pencil"></i>
    </a>
    
    <a href="{{ route('view-attendance', $row->id) }}" class="btn btn-sm btn-outline-info" title="View">
        <i class="bi bi-eye"></i>
    </a>

    <a href="{{ route('delete-attendance', $row->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this record?')" title="Delete">
        <i class="bi bi-trash"></i>
    </a>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-calendar-x fs-1 mb-2"></i>
                                            <p class="mb-0">No attendance records found for this date/search.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    {{ $attendances->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

   <div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold"><i class="bi bi-clock-fill me-2"></i>Office Timings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('update-office-timings') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Start Time</label>
                                <input type="text" name="start_time" class="form-control timepicker" 
                                       value="{{ $settings->start_time ?? '09:00' }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">End Time</label>
                                <input type="text" name="end_time" class="form-control timepicker" 
                                       value="{{ $settings->end_time ?? '18:00' }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Grace Time (Mins)</label>
                                <input type="number" name="grace_time" class="form-control" 
                                       value="{{ $settings->grace_time ?? 15 }}" min="0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Half Day Limit (Hours)</label>
                                <input type="number" name="half_day_hours" class="form-control" 
                                       value="{{ $settings->half_day_hours ?? 4 }}" min="1" max="12" required>
                            </div>
                        </div>
                        <div class="alert alert-light mt-3 mb-0 border">
                            <ul class="mb-0 small text-muted ps-3">
                                <li>Late Login: Check-in after <strong>Start Time + Grace Time</strong>.</li>
                                <li>Half Day: Total working hours less than <strong>Half Day Limit</strong>.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>Mark Check-Out
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form id="checkoutForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis mb-4">
                            <i class="bi bi-info-circle me-1"></i> You are marking check-out for: <br>
                            <strong id="modalEmpName" class="fs-5"></strong>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check p-3 border rounded bg-white shadow-sm hover-shadow cursor-pointer" onclick="document.getElementById('useCurrentTime').click()">
                                <input class="form-check-input" type="checkbox" name="use_current_time" value="1" id="useCurrentTime" checked>
                                <label class="form-check-label fw-bold cursor-pointer" for="useCurrentTime">
                                    Mark Now (Current Time)
                                </label>
                                <div class="text-success small mt-1 fw-bold">
                                    <i class="bi bi-clock me-1"></i> <span id="clockNow">--:--</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-center text-muted my-2">- OR -</div>

                        <div class="mb-2">
                            <label class="form-label text-muted fw-semibold">Select Specific Time</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-clock-history"></i></span>
                                <input type="text" name="custom_checkout_time" id="customTime" 
                                       class="form-control timepicker" placeholder="Select Time (e.g. 18:30)" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4">Confirm Check-Out</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('super_admin.includes.footer_links')

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // 1. Initialize Flatpickr for Date/Time inputs
            flatpickr("#filterDate", { dateFormat: "Y-m-d", allowInput: true });
            flatpickr(".timepicker", { 
                enableTime: true, 
                noCalendar: true, 
                dateFormat: "H:i", 
                time_24hr: true,
                allowInput: true
            });
            
            // 2. Checkout Modal Time Logic
            const fpCustomTime = flatpickr("#customTime", {
                enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true
            });

            // 3. Live Clock
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', hour12: true});
                const clockEl = document.getElementById('clockNow');
                if(clockEl) clockEl.innerText = timeString;
            }
            setInterval(updateClock, 1000);
            updateClock();

            // 4. Checkout Logic (Checkbox toggle)
            const checkbox = document.getElementById('useCurrentTime');
            const customInput = document.getElementById('customTime');
            
            if(checkbox) {
                checkbox.addEventListener('change', function() {
                    if(this.checked) {
                        customInput.disabled = true;
                        customInput.value = '';
                        customInput.classList.remove('bg-white');
                    } else {
                        customInput.disabled = false;
                        customInput.classList.add('bg-white');
                        fpCustomTime.open(); 
                    }
                });
            }
        });

        // 5. Open Modal (Global)
        function openCheckoutModal(id, name) {
            const form = document.getElementById('checkoutForm');
            form.action = "{{ url('attendance_checkout') }}/" + id;
            document.getElementById('modalEmpName').innerText = name;
            
            const checkbox = document.getElementById('useCurrentTime');
            checkbox.checked = true;
            checkbox.dispatchEvent(new Event('change'));
            
            new bootstrap.Modal(document.getElementById('checkoutModal')).show();
        }
    </script>
</body>
</html>