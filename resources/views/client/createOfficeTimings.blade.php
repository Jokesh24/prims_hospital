<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Office Timings
    </title>

    @include('client.includes.header_links')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

</head>

<body>

    <div id="overlay" onclick="closeMobile()"></div>

    @include('client.includes.sidebar')

    <div id="main">

        @include('client.includes.header')

        <div class="content">

            <div class="container-fluid py-2">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h4 class="fw-bold m-0">

                        Office Timings

                    </h4>

                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">

                        <i class="bi bi-arrow-left me-1"></i>

                        Back

                    </a>

                </div>

                @if(session('success'))

                <div class="alert alert-success">

                    {{ session('success') }}

                </div>

                @endif

                @if(session('error'))

                <div class="alert alert-danger">

                    {{ session('error') }}

                </div>

                @endif

                <div class="dashboard-card">

                    <form action="{{ route('saveOfficeTiming') }}" method="POST">
                        @csrf

                        {{-- DEFAULT OFFICE TIMINGS --}}
                        <div class="mb-4">

                            <h5 class="fw-bold mb-1">
                                <i class="bi bi-building me-2 text-primary"></i>
                                Default Office Timings
                            </h5>

                            <p class="text-muted mb-3">
                                These timings will be used when no department-specific timing is configured.
                            </p>

                            <div class="table-responsive">

                                <table class="table table-bordered align-middle mb-0">

                                    <thead class="table-light">
                                        <tr>
                                            <th>Login Time</th>
                                            <th>Logout Time</th>
                                            <th>Grace Period</th>
                                            <th>Half Day</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <tr>

                                            <td>
                                                <input type="text" id="default_start_time" name="default_start_time"
                                                    class="form-control" value="{{ !empty($settings->start_time)
                                        ? \Carbon\Carbon::parse($settings->start_time)->format('h:i A')
                                        : '09:00 AM' }}" readonly>
                                            </td>

                                            <td>
                                                <input type="text" id="default_end_time" name="default_end_time"
                                                    class="form-control" value="{{ !empty($settings->end_time)
                                        ? \Carbon\Carbon::parse($settings->end_time)->format('h:i A')
                                        : '06:00 PM' }}" readonly>
                                            </td>

                                            <td>
                                                <div class="input-group">

                                                    <input type="number" name="default_grace_time" class="form-control"
                                                        min="0" value="{{ $settings->grace_time ?? 15 }}">

                                                    <span class="input-group-text">
                                                        Min
                                                    </span>

                                                </div>
                                            </td>

                                            <td>
                                                <div class="input-group">

                                                    <input type="number" name="default_half_day_limit"
                                                        class="form-control" min="1" max="12"
                                                        value="{{ $settings->half_day_limit ?? 4 }}">

                                                    <span class="input-group-text">
                                                        Hrs
                                                    </span>

                                                </div>
                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>


                        {{-- DEPARTMENT TIMINGS --}}
                        <div class="mt-5">

                            <h5 class="fw-bold mb-1">
                                <i class="bi bi-diagram-3 me-2 text-primary"></i>
                                Department Timings
                            </h5>

                            <p class="text-muted mb-3">
                                Configure different office timings for each department.
                            </p>

                            <div class="table-responsive">

                                <table class="table table-bordered align-middle mb-0">

                                    <thead class="table-light">

                                        <tr>
                                            <th>Department Name</th>
                                            <th>Login Time</th>
                                            <th>Logout Time</th>
                                            <th>Grace Period</th>
                                            <th>Half Day</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        @foreach($departments as $department)

                                        @php
                                        $timing = $departmentTimings
                                        ->where('dept_id', $department->id)
                                        ->first();
                                        @endphp

                                        <tr>

                                            <td class="fw-semibold">
                                                {{ $department->department_name }}

                                                <input type="hidden" name="department_ids[]"
                                                    value="{{ $department->id }}">
                                            </td>

                                            <td>
                                                <input type="text" id="start_{{ $department->id }}"
                                                    name="start_time[{{ $department->id }}]"
                                                    class="form-control dept-start-time" value="{{ $timing && $timing->start_time
                    ? \Carbon\Carbon::parse($timing->start_time)->format('h:i A')
                    : '09:00 AM' }}" readonly>
                                            </td>

                                            <td>
                                                <input type="text" id="end_{{ $department->id }}"
                                                    name="end_time[{{ $department->id }}]"
                                                    class="form-control dept-end-time" value="{{ $timing && $timing->end_time
                    ? \Carbon\Carbon::parse($timing->end_time)->format('h:i A')
                    : '06:00 PM' }}" readonly>
                                            </td>

                                            <td>
                                                <div class="input-group">

                                                    <input type="number" name="grace_time[{{ $department->id }}]"
                                                        class="form-control" min="0"
                                                        value="{{ $timing ? $timing->grace_time : 15 }}">

                                                    <span class="input-group-text">
                                                        Min
                                                    </span>

                                                </div>
                                            </td>

                                            <td>
                                                <div class="input-group">

                                                    <input type="number" name="half_day_limit[{{ $department->id }}]"
                                                        class="form-control" min="1" max="12"
                                                        value="{{ $timing ? $timing->half_day_limit : 4 }}">

                                                    <span class="input-group-text">
                                                        Hrs
                                                    </span>

                                                </div>
                                            </td>

                                        </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        </div>


                        {{-- SAVE BUTTON --}}
                        <div class="d-flex justify-content-end mt-4">

                            <button type="submit" class="btn btn-primary px-4">

                                <i class="bi bi-check-circle me-1"></i>

                                Save Timings

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

    @include('client.includes.footer_links')

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
    flatpickr("#default_start_time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        minuteIncrement: 1,
        allowInput: false
    });

    flatpickr("#default_end_time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        minuteIncrement: 1,
        allowInput: false
    });


    document.querySelectorAll(".dept-start-time").forEach(function(element) {

        flatpickr(element, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K",
            minuteIncrement: 1,
            allowInput: false
        });

    });


    document.querySelectorAll(".dept-end-time").forEach(function(element) {

        flatpickr(element, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "h:i K",
            minuteIncrement: 1,
            allowInput: false
        });

    });
    </script>

</body>

</html>