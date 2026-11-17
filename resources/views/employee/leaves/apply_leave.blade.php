<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Apply for Leave - Dashboard</title>
    @include('employee.includes.header_links')
</head>

<body>

    <div id="overlay" onclick="closeMobile()"></div>

    @include('employee.includes.sidebar')

    <div id="main">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show floating-alert" role="alert"
                style="position: fixed; top: 20px; right: 20px; z-index: 9999; border: none; border-left: 4px solid #059669; background: #fff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); color: #0f172a; font-weight: 500;">
                <i class="bi bi-check-circle-fill" style="color: #059669; margin-right: 8px;"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                    style="font-size: 0.8rem;"></button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="alert alert-danger alert-dismissible fade show floating-alert" role="alert"
                style="position: fixed; top: 20px; right: 20px; z-index: 9999; border: none; border-left: 4px solid #dc2626; background: #fff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); color: #0f172a; font-weight: 500;">
                <i class="bi bi-exclamation-triangle-fill" style="color: #dc2626; margin-right: 8px;"></i>
                {{ session('error') ?? $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                    style="font-size: 0.8rem;"></button>
            </div>
        @endif

        @include('employee.includes.header')

        <div class="content">
            <div class="container-fluid py-2">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold m-0">Apply for Leave</h4>
                        <p class="text-muted small mb-0">Submit your leave request for approval</p>
                    </div>
                    <a href="#" class="btn btn-outline-secondary px-4 py-2" style="border-radius:10px;">
                        <i class="bi bi-arrow-left me-2"></i> Back to Leaves
                    </a>
                </div>

                <!-- @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif -->

                <div class="dashboard-card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form action="{{ route('leaves.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                <i class="bi bi-calendar-event me-2"></i>Leave Details
                            </h6>

                            <div class="row g-3 mb-4">

                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Subject <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="subject" class="form-control custom-input" required
                                        value="{{ old('subject') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">From Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="leave_date_from"
                                        class="form-control custom-input @error('leave_date_from') is-invalid @enderror"
                                        value="{{ old('leave_date_from') }}" required>
                                    @error('leave_date_from') <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">To Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="to_leave_date"
                                        class="form-control custom-input @error('to_leave_date') is-invalid @enderror"
                                        value="{{ old('to_leave_date') }}" required>
                                    @error('to_leave_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Reason for Leave <span
                                            class="text-danger">*</span></label>
                                    <textarea name="reason" rows="3"
                                        class="form-control custom-input @error('reason') is-invalid @enderror" required
                                        placeholder="Briefly describe the reason for your leave...">{{ old('reason') }}</textarea>
                                    @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Supportive Document (Optional)</label>
                                    <input type="file" name="documents"
                                        class="form-control custom-input @error('documents') is-invalid @enderror"
                                        accept="image/*,application/pdf">
                                    <small class="text-muted">Allowed: JPG, PNG, PDF</small>
                                    @error('documents') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mt-5 d-flex justify-content-end gap-3 border-top pt-4">
                                <a href="#" class="btn btn-light px-5 fw-bold border">Cancel</a>
                                <button type="submit" class="btn btn-primary py-2 px-5 fw-bold">Submit Request</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
        @include('employee.includes.footer_links')
    </div>

</body>

</html>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Find all floating alerts
        let alerts = document.querySelectorAll('.floating-alert');

        if (alerts.length > 0) {
            // Wait 5 seconds, then fade them out
            setTimeout(function () {
                alerts.forEach(function (alertNode) {
                    // Check if Bootstrap JS is loaded and use its Alert API to dismiss smoothly
                    if (typeof bootstrap !== 'undefined') {
                        let bsAlert = new bootstrap.Alert(alertNode);
                        bsAlert.close();
                    } else {
                        // Fallback if bootstrap JS isn't initialized yet
                        alertNode.style.display = 'none';
                    }
                });
            }, 5000); // 5000ms = 5 seconds
        }
    });
</script>