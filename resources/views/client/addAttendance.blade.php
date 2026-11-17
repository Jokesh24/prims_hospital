<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Attendance</title>

    @include('client.includes.header_links')

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body>

    <div id="overlay" onclick="closeMobile()"></div>

    @include('client.includes.sidebar')

    <div id="main">

        @include('client.includes.header')

        <div class="content">

            <div class="container-fluid py-2">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>
                        <h4 class="fw-bold m-0">
                            Add Attendance
                        </h4>
                    </div>

                    <div>
                        <a href="{{ route('manageAttendance') }}"
                            class="btn btn-primary">
                            <i class="bi bi-list-ul me-1"></i>
                            Manage Attendance
                        </a>
                    </div>

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

                    <form action="{{ route('storeAttendance') }}"
                        method="POST">

                        @csrf

                        <div class="row g-4">

                            <div class="col-md-6">

                                <label class="filter-label">
                                    Employee
                                    <span class="text-danger">*</span>
                                </label>

                                <select
                                    name="emp_id"
                                    class="form-control filter-control @error('emp_id') is-invalid @enderror"
                                    >

                                    <option value="">
                                        Select Employee
                                    </option>

                                    @foreach($employees as $employee)

                                        <option
                                            value="{{ $employee->id }}"
                                            {{ old('emp_id') == $employee->id ? 'selected' : '' }}>

                                            {{ $employee->employee_name }}
                                            ({{ $employee->employee_id }})

                                        </option>

                                    @endforeach

                                </select>

                                @error('emp_id')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>

                            <div class="col-md-3">

                                <label class="filter-label">
                                    Punch In Time
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="punch_in_time"
                                    name="punch_in_time"
                                    value="{{ old('punch_in_time') }}"
                                    class="form-control filter-control @error('punch_in_time') is-invalid @enderror"
                                    placeholder="Select Punch In Time"
                                    >

                                @error('punch_in_time')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>

                            <div class="col-md-3">

                                <label class="filter-label">
                                    Punch Out Time
                                </label>

                                <input
                                    type="text"
                                    id="punch_out_time"
                                    name="punch_out_time"
                                    value="{{ old('punch_out_time') }}"
                                    class="form-control filter-control"
                                    placeholder="Select Punch Out Time">

                            </div>

                        </div>

                        <div class="mt-4 text-end">

                            <button
                                type="reset"
                                class="btn btn-outline-secondary">
                                Reset
                            </button>

                            <button
                                type="submit"
                                class="btn btn-primary">
                                Save Attendance
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

        document.addEventListener('DOMContentLoaded', function () {

            flatpickr("#punch_in_time", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: true,
                defaultDate: new Date()
            });

            flatpickr("#punch_out_time", {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: true
            });

        });

    </script>

</body>

</html>