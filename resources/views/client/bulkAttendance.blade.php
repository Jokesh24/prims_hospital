<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Attendance</title>

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

                    <div>
                        <h4 class="fw-bold m-0">
                            Bulk Attendance
                        </h4>
                    </div>

                    <div>
                        <a href="{{ route('manageAttendance') }}" class="btn btn-primary">
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

                    <form action="{{ route('storeBulkAttendance') }}" method="POST">

                        @csrf

                        <div class="table-responsive">
                            <div class="row mb-4">

                                <div class="col-md-4">

                                    <label class="form-label fw-semibold">
                                        Login Time
                                    </label>

                                    <input type="text" id="globalPunchIn" class="form-control"
                                        placeholder="Select Login Time">

                                </div>

                                <div class="col-md-4">

                                    <label class="form-label fw-semibold">
                                        Logout Time
                                    </label>

                                    <input type="text" id="globalPunchOut" class="form-control"
                                        placeholder="Select Logout Time">

                                </div>

                                <div class="col-md-4 d-flex align-items-end">

                                    <button type="button" id="applyToAll" class="btn btn-primary">

                                        <i class="bi bi-check2-square me-1"></i>
                                        Apply to All

                                    </button>

                                </div>

                            </div>

                            <table class="table table-bordered align-middle">

                                <thead>

                                    <tr>

                                        <th width="80">
                                            #
                                        </th>

                                        <th>
                                            Employee ID
                                        </th>

                                        <th>
                                            Employee Name
                                        </th>

                                        <th>
                                            Punch In Time
                                        </th>

                                        <th>
                                            Punch Out Time
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @forelse($employees as $employee)

                                        <tr>

                                            <td>
                                                {{ $loop->iteration }}
                                            </td>

                                            <td>
                                                {{ $employee->employee_id }}
                                            </td>

                                            <td>
                                                {{ $employee->employee_name }}
                                            </td>

                                            <td>

                                                <input type="text" class="form-control punch-in"
                                                    name="attendance[{{ $employee->id }}][punch_in_time]"
                                                    placeholder="Select Punch In">

                                            </td>

                                            <td>

                                                <input type="text" class="form-control punch-out"
                                                    name="attendance[{{ $employee->id }}][punch_out_time]"
                                                    placeholder="Select Punch Out">

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="5" class="text-center">

                                                No Employees Found

                                            </td>

                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                        <div class="mt-4 text-end">

                            <button type="submit" class="btn btn-primary">

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

            // Global Pickers
            const globalPunchIn = flatpickr("#globalPunchIn", {
                enableTime: true,
                dateFormat: "d-m-Y h:i K",
                time_24hr: false,
                allowInput: true,
                defaultDate: new Date()
            });

            const globalPunchOut = flatpickr("#globalPunchOut", {
                enableTime: true,
                dateFormat: "d-m-Y h:i K",
                time_24hr: false,
                allowInput: true
            });

            // Row Pickers
            document.querySelectorAll('tbody tr').forEach(function (row) {

                const punchInInput = row.querySelector('.punch-in');
                const punchOutInput = row.querySelector('.punch-out');

                if (!punchInInput || !punchOutInput) {
                    return;
                }

                const punchOutPicker = flatpickr(punchOutInput, {
                    enableTime: true,
                    dateFormat: "d-m-Y h:i K",
                    time_24hr: false,
                    allowInput: true
                });

                flatpickr(punchInInput, {
                    enableTime: true,
                    dateFormat: "d-m-Y h:i K",
                    time_24hr: false,
                    allowInput: true,

                    onChange: function (selectedDates) {

                        if (selectedDates.length) {
                            punchOutPicker.set('minDate', selectedDates[0]);
                        }

                    }

                });

            });

            // Apply Global Time to All Employees
            document.getElementById('applyToAll').addEventListener('click', function () {

                const loginTime = document.getElementById('globalPunchIn').value;
                const logoutTime = document.getElementById('globalPunchOut').value;

                document.querySelectorAll('.punch-in').forEach(function (input) {
                    input._flatpickr.setDate(loginTime, true);
                });

                document.querySelectorAll('.punch-out').forEach(function (input) {
                    input._flatpickr.setDate(logoutTime, true);
                });

            });

        });
    </script>

</body>

</html>