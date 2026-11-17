<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ env('APP_NAME') }}</title>

    @include('client.includes.header_links')
    <style>
        .dept-header td {
            background-color: #e2e8f0 !important;
            border-top: 2px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
        }

        /* --- ADDED: Premium Loader Styles --- */
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
                    // Universally fetch the Client profile regardless of who is logged in
                    $loaderClient = \App\Models\Client::find(session('ClientUser'));
                @endphp

                @if($loaderClient && $loaderClient->logo)
                    <img src="{{ asset('public/uploads/clients/' . $loaderClient->logo) }}" alt="Company Logo"
                        class="loader-logo">
                @else
                    <div class="loader-logo"
                        style="background:#1e293b; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:bold; font-size:12px; border-radius:8px;">
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
                        <h4 class="fw-bold m-0">Daily Attendance Log</h4>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('bulkAttendance') }}" class="btn btn-primary px-4 py-2"
                            style="border-radius:10px;">
                            <i class="bi bi-plus-circle me-2"></i> Add Bulk Attendance
                        </a>
                        <a href="{{ route('createAttendance') }}" class="btn btn-primary px-4 py-2"
                            style="border-radius:10px;">
                            <i class="bi bi-plus-circle me-2"></i> Add Attendance
                        </a>
                        <a href="{{ route('monthlyAttendanceEntry') }}" class="btn btn-primary px-4 py-2"
                            style="border-radius:10px;">
                            <i class="bi bi-plus-circle me-2"></i> Add Monthly Attendance
                        </a>
                    </div>
                </div>

                <!-- FILTER CARD WITH DIRECT EXCEL & PDF DOWNLOAD -->
                <div class="dashboard-card mb-3">
                    <!-- ADDED ID for Filter Form Tracking -->
                    <form method="GET" action="{{ route('manageAttendance') }}" id="attendanceFilterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="filter-label">Search Employee</label>
                                <input type="text" name="search" id="filter_search" class="filter-control w-100"
                                    value="{{ request('search') }}" placeholder="Search Employee...">
                            </div>

                            <div class="col-md-2">
                                <label class="filter-label">From Date</label>
                                <input type="text" id="from_date" name="from_date"
                                    value="{{ request('from_date', date('d-m-Y')) }}"
                                    class="form-control filter-control">
                            </div>

                            <div class="col-md-2">
                                <label class="filter-label">To Date</label>
                                <input type="text" id="to_date" name="to_date"
                                    value="{{ request('to_date', date('d-m-Y')) }}" class="form-control filter-control">
                            </div>

                            <div class="col-md-5">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary" title="Filter Records">
                                        <i class="bi bi-search"></i>
                                    </button>
                                    <a href="{{ route('manageAttendance') }}" class="btn btn-secondary"
                                        title="Reset Filters">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>

                                    <!-- ADDED CLASS: export-btn -->
                                    <button type="submit" formaction="{{ route('exportAttendanceExcel') }}"
                                        class="btn btn-success flex-fill export-btn" title="Download Excel">
                                        <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                    </button>

                                    <button type="button" id="btnDirectDownloadPdf" class="btn btn-danger flex-fill"
                                        title="Download PDF File Directly">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i>
                                        <span id="pdfBtnText">PDF</span>
                                    </button>
                                </div>
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
                                    <th>Employee</th>
                                    <th>Attendance Date</th>
                                    <th>Check In</th>
                                    <th>Check Out (As of now)</th>
                                    <th>Working Hours</th>
                                    <th>Logs</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            // Calculate precise S.No based on current pagination page
                                    $sno = ($attendances->currentPage() - 1) * $attendances->perPage() + 1; 
                                @endphp

                                @forelse($groupedAttendances as $deptName => $deptAttendances)
                                    <!-- Group Header -->
                                    <tr class="dept-header">
                                        <td colspan="9" class="text-start text-primary fw-bold text-uppercase fs-6">
                                            <i class="bi bi-diagram-3-fill me-1"></i> {{ $deptName }}
                                        </td>
                                    </tr>

                                    <!-- Employee Records under Group -->
                                    @foreach($deptAttendances as $attendance)
                                        <tr>
                                            <td class="text-muted fw-bold">{{ $sno++ }}</td>
                                            <td>
                                                <strong
                                                    class="text-dark">{{ $attendance->employee->employee_name ?? 'N/A' }}</strong>
                                                @if(!empty($attendance->employee->employee_id))
                                                    <br><small class="text-muted">{{ $attendance->employee->employee_id }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('d-m-Y') : 'N/A' }}
                                            </td>
                                            <td class="fw-medium text-success">
                                                {{ $attendance->punch_in_time ? \Carbon\Carbon::parse($attendance->punch_in_time)->format('h:i A') : '--' }}
                                            </td>
                                            <td class="fw-medium text-danger">
                                                {{ ($attendance->punch_out_time && $attendance->punch_out_time != $attendance->punch_in_time) ? \Carbon\Carbon::parse($attendance->punch_out_time)->format('h:i A') : '--' }}
                                            </td>
                                            <td class="fw-bold text-center">
                                                {{ $attendance->working_hours }}
                                                @if($attendance->is_ot)
                                                    <br><span class="badge bg-warning text-dark mt-1" style="font-size: 10px;"
                                                        title="Double Shift OT"><i class="bi bi-clock-history me-1"></i>OT
                                                        Shift</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info viewLogs text-white"
                                                    data-id="{{ $attendance->id }}">
                                                    <i class="bi bi-clock-history"></i> View Logs
                                                </button>
                                            </td>
                                            <td>
                                                @if($attendance->punch_in_time)
                                                    <span class="badge-soft"
                                                        style="background:#ecfdf5; color:#10b981;">Present</span>
                                                @else
                                                    <span class="badge-soft"
                                                        style="background:#fef2f2; color:#ef4444;">Absent</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($attendance->punch_in_time && !$attendance->punch_out_time)
                                                    <button type="button"
                                                        class="btn btn-light btn-sm text-warning me-1 checkout-btn"
                                                        data-id="{{ $attendance->id }}"
                                                        data-name="{{ $attendance->employee->employee_name ?? '' }}"
                                                        data-date="{{ \Carbon\Carbon::parse($attendance->punch_in_time)->format('Y-m-d') }}"
                                                        data-default-time="{{ $attendance->default_logout }}" data-bs-toggle="modal"
                                                        data-bs-target="#checkoutModal">
                                                        <i class="bi bi-box-arrow-right"></i>
                                                    </button>
                                                @endif

                                                <a href="{{ route('editAttendance', $attendance->id) }}"
                                                    class="btn btn-light btn-sm text-primary me-1">
                                                    <i class="bi bi-pencil"></i>
                                                </a>

                                                <form action="{{ route('deleteAttendance', $attendance->id) }}" method="POST"
                                                    class="d-inline delete-form">
                                                    @csrf
                                                    <button type="submit"
                                                        onclick="return confirm('Are you sure you want to delete this record?')"
                                                        class="btn btn-light btn-sm text-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">No Attendance Records Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 d-flex justify-content-end">
                        {{ $attendances->withQueryString()->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Check-Out Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Mark Check-Out
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- ADDED ID for tracking submit -->
                <form id="checkoutForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-1"></i> You are marking check-out for:
                            <br><strong id="modalEmpName" class="fs-5"></strong>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="useCurrentTime" name="use_current_time"
                                value="1" checked>
                            <label class="form-check-label fw-semibold" for="useCurrentTime">Use Current Time</label>
                        </div>
                        <div class="mb-3">
                            <div class="text-success fw-bold" id="clockNow"></div>
                        </div>
                        <div id="manualTimeSection" style="display:none;">
                            <label class="form-label">Select Check-Out Time</label>
                            <input type="text" name="custom_checkout_time" id="customTime" class="form-control"
                                placeholder="Select Time">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Check-Out</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Logs Modal -->
    <div class="modal fade" id="logsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Attendance Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="attendanceLogs"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Iframe for silent server downloads -->
    <iframe id="downloadFrame" style="display:none;"></iframe>

    <!-- Off-screen Sandbox for PDF generation -->
    <div id="pdfFallbackSandbox"
        style="position: fixed; left: 0; top: 0; width: max-content; min-width: 1400px; visibility: hidden; z-index: -9999; background: #ffffff;">
    </div>

    @include('client.includes.footer_links')

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /* --- Loader Logic --- */

            // 1. Search / Filter Form
            const filterForm = document.getElementById('attendanceFilterForm');
            if (filterForm) {
                filterForm.addEventListener('submit', function (e) {
                    const isExport = e.submitter && e.submitter.classList.contains('export-btn');
                    document.getElementById('globalLoader').style.display = 'flex';

                    if (isExport) {
                        // Excel download doesn't reload the page. Hide the loader after a few seconds.
                        setTimeout(() => {
                            document.getElementById('globalLoader').style.display = 'none';
                        }, 4000);
                    }
                });
            }

            // 2. Checkout Submit Form
            const checkoutForm = document.getElementById('checkoutForm');
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', function () {
                    document.getElementById('globalLoader').style.display = 'flex';
                });
            }

            // 3. Delete Form
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    // Only show loader if the confirm dialog was accepted
                    document.getElementById('globalLoader').style.display = 'flex';
                });
            });

            /* --- Flatpickr & Modal Logic --- */
            const checkbox = document.getElementById('useCurrentTime');
            const manualSection = document.getElementById('manualTimeSection');
            const clockNow = document.getElementById('clockNow');

            let fpInstance = null;

            document.querySelectorAll('.checkout-btn').forEach(button => {
                button.addEventListener('click', function () {
                    let empName = this.dataset.name;
                    checkoutForm.action = "{{ route('checkOutAttendance', ['id' => '__ID__']) }}".replace('__ID__', this.dataset.id);

                    let attDateStr = this.dataset.date; // Format: YYYY-MM-DD
                    let defaultTimeStr = this.dataset.defaultTime; // Format: HH:mm:ss

                    let dateParts = attDateStr.split('-');
                    let year = parseInt(dateParts[0], 10);
                    let month = parseInt(dateParts[1], 10) - 1;
                    let day = parseInt(dateParts[2], 10);

                    let timeParts = defaultTimeStr.split(':');
                    let hour = parseInt(timeParts[0], 10);
                    let minute = parseInt(timeParts[1], 10);

                    let attDate = new Date(year, month, day);
                    let today = new Date();
                    today.setHours(0, 0, 0, 0);

                    let switchWrapper = document.getElementById('useCurrentTime').closest('.form-check');
                    let clockWrapper = document.getElementById('clockNow').parentElement;
                    let modalEmpNameEl = document.getElementById('modalEmpName');

                    if (fpInstance) {
                        fpInstance.destroy();
                    }

                    if (attDate.getTime() < today.getTime()) {
                        let dmyDate = `${dateParts[2].padStart(2, '0')}-${dateParts[1].padStart(2, '0')}-${dateParts[0]}`;
                        modalEmpNameEl.innerHTML = `${empName} <br><span class="badge bg-warning text-dark mt-2" style="font-size:14px;">Past Date: ${dmyDate}</span>`;

                        switchWrapper.style.display = 'none';
                        clockWrapper.style.display = 'none';
                        checkbox.checked = false;
                        manualSection.style.display = 'block';

                        fpInstance = flatpickr("#customTime", {
                            enableTime: true,
                            dateFormat: "d-m-Y h:i K",
                            time_24hr: false,
                            defaultDate: new Date(year, month, day, hour, minute, 0),
                            minDate: new Date(year, month, day, 0, 0, 0),
                            maxDate: new Date(year, month, day, 23, 59, 59)
                        });

                    } else {
                        modalEmpNameEl.innerText = empName;

                        switchWrapper.style.display = 'block';
                        clockWrapper.style.display = 'block';
                        checkbox.checked = true;
                        manualSection.style.display = 'none';

                        fpInstance = flatpickr("#customTime", {
                            enableTime: true,
                            dateFormat: "d-m-Y h:i K",
                            time_24hr: false,
                            defaultDate: new Date(),
                            maxDate: new Date()
                        });
                    }
                });
            });

            function updateClock() {
                const now = new Date();
                clockNow.innerHTML = '<i class="bi bi-clock me-1"></i>' + now.toLocaleString('en-IN', {
                    day: '2-digit', month: '2-digit', year: 'numeric',
                    hour: '2-digit', minute: '2-digit', second: '2-digit',
                    hour12: true
                });
            }
            updateClock();
            setInterval(updateClock, 1000);

            checkbox.addEventListener('change', function () {
                manualSection.style.display = this.checked ? 'none' : 'block';
            });

            flatpickr("#from_date", {
                dateFormat: "d-m-Y",
                defaultDate: "{{ request('from_date', date('d-m-Y')) }}"
            });

            flatpickr("#to_date", {
                dateFormat: "d-m-Y",
                defaultDate: "{{ request('to_date', date('d-m-Y')) }}"
            });

            // DIRECT SILENT PDF DOWNLOAD HANDLER
            document.getElementById('btnDirectDownloadPdf').addEventListener('click', function () {
                const btn = this;
                const btnText = document.getElementById('pdfBtnText');
                const originalText = btnText.innerText;

                btnText.innerText = 'Downloading...';
                btn.disabled = true;

                // Show the loader immediately
                document.getElementById('globalLoader').style.display = 'flex';

                const params = new URLSearchParams({
                    search: document.getElementById('filter_search').value,
                    from_date: document.getElementById('from_date').value,
                    to_date: document.getElementById('to_date').value,
                    download: 1
                });

                const downloadUrl = "{{ route('exportAttendancePdf') }}?" + params.toString();

                fetch(downloadUrl)
                    .then(response => {
                        const contentType = response.headers.get("content-type");

                        if (contentType && contentType.includes("application/pdf")) {
                            return response.blob().then(blob => {
                                const url = window.URL.createObjectURL(blob);
                                const a = document.createElement('a');
                                a.style.display = 'none';
                                a.href = url;
                                a.download = 'Attendance_Register_' + new Date().toISOString().slice(0, 10) + '.pdf';
                                document.body.appendChild(a);
                                a.click();
                                window.URL.revokeObjectURL(url);

                                btnText.innerText = originalText;
                                btn.disabled = false;
                                // Hide the loader explicitly when the download process finishes
                                document.getElementById('globalLoader').style.display = 'none';
                            });
                        }

                        return response.text().then(html => {
                            const sandbox = document.getElementById('pdfFallbackSandbox');
                            sandbox.innerHTML = html;

                            const target = sandbox.querySelector('#pdfContentArea') || sandbox;

                            const opt = {
                                margin: [4, 4, 4, 4],
                                filename: 'Attendance_Register_' + new Date().toISOString().slice(0, 10) + '.pdf',
                                image: {type: 'jpeg', quality: 0.98},
                                html2canvas: {scale: 2, useCORS: true, scrollY: 0},
                                jsPDF: {unit: 'mm', format: 'a3', orientation: 'landscape'},
                                pagebreak: {mode: ['avoid-all', 'css', 'legacy']}
                            };

                            return html2pdf().set(opt).from(target).save().then(() => {
                                sandbox.innerHTML = '';
                                btnText.innerText = originalText;
                                btn.disabled = false;
                                // Hide the loader explicitly when html2pdf rendering completes
                                document.getElementById('globalLoader').style.display = 'none';
                            });
                        });
                    })
                    .catch(err => {
                        console.error('Error generating PDF:', err);
                        alert('Could not generate PDF. Please try again.');
                        btnText.innerText = originalText;
                        btn.disabled = false;
                        document.getElementById('globalLoader').style.display = 'none';
                    });
            });
        });

        $(document).on("click", ".viewLogs", function () {
            let id = $(this).data("id");
            let url = "{{ route('attendanceLogs', ':id') }}".replace(':id', id);

            $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                success: function (response) {
                    if (!response.status) {
                        alert(response.message);
                        return;
                    }

                    let html = `
                        <div class="row text-center mb-3">
                            <div class="col-md-4">
                                <h6>Total Working</h6>
                                <strong class="text-success">${response.working_time}</strong>
                            </div>
                            <div class="col-md-4">
                                <h6>Total Break</h6>
                                <strong class="text-warning">${response.break_time}</strong>
                            </div>
                            <div class="col-md-4">
                                <h6>Odd Log</h6>
                                <strong class="${response.odd_log ? 'text-danger' : 'text-success'}">
                                    ${response.odd_log ? 'Yes' : 'No'}
                                </strong>
                            </div>
                        </div>
                        <hr>
                    `;

                    response.timeline.forEach(function (row) {
                        if (row.type === "In" || row.type === "Out") {
                            html += `
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold ${row.type === 'In' ? 'text-success' : 'text-danger'}">${row.type}</span>
                                    <span>${row.time}</span>
                                </div>
                            `;
                        } else {
                            html += `
                                <div class="ms-4 mb-3">
                                    <span class="${row.type === 'Working' ? 'text-success' : 'text-warning'} fw-semibold">${row.type} :</span>
                                    <strong>${row.duration}</strong>
                                </div>
                            `;
                        }
                    });

                    $("#attendanceLogs").html(html);
                    new bootstrap.Modal(document.getElementById("logsModal")).show();
                },
                error: function (xhr) {
                    alert("Unable to load attendance logs.");
                }
            });
        });
    </script>
</body>

</html>