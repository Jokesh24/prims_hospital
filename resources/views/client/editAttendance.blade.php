<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendance</title>

    @include('client.includes.header_links')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- ADDED: Premium Loader Styles -->
    <style>
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(255, 255, 255, 0.92);
            z-index: 999999;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .loader-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: slideUpFade 0.6s ease-out;
        }

        .logo-loader-container {
            position: relative;
            width: 90px;
            height: 90px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
        }

        .loader-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: #0d6efd;
            /* Primary Color */
            border-right-color: rgba(13, 110, 253, 0.3);
            border-bottom-color: rgba(13, 110, 253, 0.1);
            border-left-color: transparent;
            animation: spinRing 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }

        .loader-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 8px;
            animation: pulseLogo 2s ease-in-out infinite;
        }

        .processing-text {
            letter-spacing: 0.5px;
            color: #1e293b;
        }

        .dots::after {
            content: '';
            animation: ellipsis 1.5s infinite;
            display: inline-block;
            width: 20px;
            text-align: left;
        }

        /* Keyframe Animations */
        @keyframes spinRing {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes pulseLogo {
            0% {
                transform: scale(0.95);
                opacity: 0.85;
                filter: drop-shadow(0 0 0 rgba(13, 110, 253, 0));
            }

            50% {
                transform: scale(1.05);
                opacity: 1;
                filter: drop-shadow(0 0 10px rgba(13, 110, 253, 0.4));
            }

            100% {
                transform: scale(0.95);
                opacity: 0.85;
                filter: drop-shadow(0 0 0 rgba(13, 110, 253, 0));
            }
        }

        @keyframes ellipsis {
            0% {
                content: '';
            }

            25% {
                content: '.';
            }

            50% {
                content: '..';
            }

            75% {
                content: '...';
            }

            100% {
                content: '';
            }
        }

        @keyframes slideUpFade {
            0% {
                transform: translateY(20px);
                opacity: 0;
            }

            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body>

    <!-- ADDED: Premium Global Full Page Loader -->
    <div id="globalLoader" class="loader-overlay" style="display: none;">
        <div class="loader-content">
            <div class="logo-loader-container">
                <div class="loader-ring"></div>
                @php
                    $loaderClient = \App\Models\Client::find(session('ClientUser'));
                @endphp
                @if($loaderClient && $loaderClient->logo)
                    <img src="{{ asset('public/uploads/clients/' . $loaderClient->logo) }}" alt="Company Logo"
                        class="loader-logo">
                @else
                    <div class="loader-logo"
                        style="background:#1e293b; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:bold; font-size:12px; border-radius:8px; width:50px; height:50px;">
                        HRMS
                    </div>
                @endif
            </div>
            <h5 class="fw-bold m-0 processing-text">Processing<span class="dots"></span></h5>
            <p class="text-muted small mt-2 mb-0" style="font-weight: 500;">Please wait, this might take a moment.</p>
        </div>
    </div>

    <div id="overlay" onclick="closeMobile()"></div>

    @include('client.includes.sidebar')

    <div id="main">

        @include('client.includes.header')

        <div class="content">

            <div class="container-fluid py-2">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>
                        <h4 class="fw-bold m-0">
                            Edit Attendance
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

                    <!-- ADDED ID: editAttendanceForm for Loader Hook -->
                    <form action="{{ route('updateAttendance', $attendance->id) }}" method="POST"
                        id="editAttendanceForm">

                        @csrf

                        <div class="row g-4">

                            <div class="col-md-6">

                                <label class="filter-label">
                                    Employee
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="emp_id"
                                    class="form-control filter-control @error('emp_id') is-invalid @enderror">

                                    <option value="">
                                        Select Employee
                                    </option>

                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('emp_id', $attendance->emp_id) == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->employee_name }} ({{ $employee->employee_id }})
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

                                <input type="text" id="punch_in_time" name="punch_in_time"
                                    value="{{ old('punch_in_time', \Carbon\Carbon::parse($attendance->punch_in_time)->format('d-m-Y h:i A')) }}"
                                    class="form-control filter-control @error('punch_in_time') is-invalid @enderror"
                                    placeholder="Select Punch In Time">

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

                                <input type="text" id="punch_out_time" name="punch_out_time"
                                    value="{{ old('punch_out_time', $attendance->punch_out_time ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('d-m-Y h:i A') : '') }}"
                                    class="form-control filter-control @error('punch_out_time') is-invalid @enderror"
                                    placeholder="Select Punch Out Time">

                                @error('punch_out_time')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>

                        </div>

                        <div class="mt-4 text-end">

                            <a href="{{ route('manageAttendance') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                Update Attendance
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

            /* --- Loader Logic Setup --- */
            const editAttendanceForm = document.getElementById('editAttendanceForm');
            if (editAttendanceForm) {
                editAttendanceForm.addEventListener('submit', function () {
                    document.getElementById('globalLoader').style.display = 'flex';
                });
            }

            /* --- Flatpickr Setup --- */
            flatpickr("#punch_in_time", {
                enableTime: true,
                dateFormat: "d-m-Y h:i K", // K is for AM/PM in Flatpickr
                time_24hr: false
            });

            flatpickr("#punch_out_time", {
                enableTime: true,
                dateFormat: "d-m-Y h:i K", // K is for AM/PM in Flatpickr
                time_24hr: false
            });

        });
    </script>

</body>

</html>