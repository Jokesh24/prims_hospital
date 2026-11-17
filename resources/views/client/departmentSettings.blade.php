<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Department Attendance & Payroll Settings | HRMS</title>
    @include('client.includes.header_links')

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
        }

        .form-switch .form-check-input {
            width: 2.5em;
            height: 1.3em;
        }
    </style>
</head>

<body>

    <div id="globalLoader" class="loader-overlay" style="display: none;">
        <div class="loader-content">
            <div class="spinner-border text-primary mb-2" role="status"></div>
            <h6 class="fw-bold m-0">Saving...</h6>
        </div>
    </div>

    <div id="overlay" onclick="closeMobile()"></div>
    @include('client.includes.sidebar')

    <div id="main">
        @include('client.includes.header')

        <div class="content">
            <div class="container-fluid py-2">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="mb-4">
                    <h4 class="fw-bold m-0">Department Attendance & Payroll Settings</h4>
                    <p class="text-muted small m-0">
                        Configure per-department exceptions. <strong>Flexible Timing</strong>: ignore rostered
                        shift login/logout times and calculate attendance against a flat 8-hour working day instead.
                        <strong>No Deductions</strong>: never deduct Late Login or Half Day amounts for this
                        department, in any month.
                    </p>
                </div>

                <div class="dashboard-card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3">Department</th>
                                    <th class="py-3 text-center">Flexible Timing (8hr flat, no roster enforcement)</th>
                                    <th class="py-3 text-center">No Deductions (no Late Login / Half Day cuts)</th>
                                    <th class="py-3 text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departments as $dept)
                                    <tr>
                                        <form action="{{ route('departmentSettings.update', $dept->id) }}" method="POST"
                                            class="loader-form d-contents">
                                            @csrf
                                            <td class="ps-4 fw-bold">{{ $dept->department_name }}</td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-flex">
                                                    <input class="form-check-input" type="checkbox" name="flexible_timing"
                                                        value="1" {{ $dept->flexible_timing == 1 ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-flex">
                                                    <input class="form-check-input" type="checkbox" name="no_deductions"
                                                        value="1" {{ $dept->no_deductions == 1 ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-save me-1"></i> Save
                                                </button>
                                            </td>
                                        </form>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No departments found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('client.includes.footer_links')

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll('.loader-form').forEach(form => {
                form.addEventListener('submit', function () {
                    document.getElementById('globalLoader').style.display = 'flex';
                });
            });
        });
    </script>
</body>

</html>