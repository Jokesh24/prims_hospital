<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Attendance Log</title>
    @include('employee.includes.header_links')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .dashboard-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
        }

        .filter-label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            color: #444;
        }

        .filter-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px;
        }

        .badge-soft {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    @include('employee.includes.sidebar')
    <div id="main">
        @include('employee.includes.header')
        <div class="content">
            <div class="container-fluid py-2">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold m-0">My Attendance Log</h4>
                </div>

                <div class="dashboard-card mb-3">
                    <form method="GET" action="{{ route('employee.attendance.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="filter-label">From Date</label>
                                <input type="text" id="from_date" name="from_date"
                                    value="{{ request('from_date', date('d-m-Y')) }}"
                                    class="form-control filter-control" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-md-4">
                                <label class="filter-label">To Date</label>
                                <input type="text" id="to_date" name="to_date"
                                    value="{{ request('to_date', date('d-m-Y')) }}" class="form-control filter-control"
                                    placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-search me-1"></i>
                                    Search</button>
                                <a href="{{ route('employee.attendance.index') }}" class="btn btn-secondary px-4"><i
                                        class="bi bi-arrow-clockwise"></i> Reset</a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="dashboard-card">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Working Hours</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ \Carbon\Carbon::parse($attendance->punch_in_time)->format('d-m-Y') }}</td>
                                        <td>{{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '--' }}
                                        </td>
                                        <td>{{ $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '--' }}
                                        </td>
                                        <td><span class="fw-bold">{{ $attendance->working_hours }}</span></td>
                                        <td>
                                            @if($attendance->punch_in_time)
                                                <span class="badge-soft"
                                                    style="background:#ecfdf5; color:#10b981;">Present</span>
                                            @else
                                                <span class="badge-soft"
                                                    style="background:#fef2f2; color:#ef4444;">Absent</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No Attendance Records Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Added Pagination Links just in case it overflows -->
                    <div class="mt-3">
                        {{ $attendances->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
        @include('employee.includes.footer_links')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr("#from_date", { dateFormat: "d-m-Y" });
            flatpickr("#to_date", { dateFormat: "d-m-Y" });
        });
    </script>
</body>

</html>