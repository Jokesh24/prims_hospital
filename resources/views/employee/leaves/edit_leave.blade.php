<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Leave Request - Dashboard</title>
    @include('employee.includes.header_links')
</head>

<body>

    <div id="overlay" onclick="closeMobile()"></div>

    @include('employee.includes.sidebar')

    <div id="main">

        @include('employee.includes.header')

        <div class="content">
            <div class="container-fluid py-2">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold m-0">Edit Leave Request</h4>
                        <p class="text-muted small mb-0">Update your leave details below</p>
                    </div>
                    <a href="{{ route('employee.leaves.manage') }}" class="btn btn-outline-secondary px-4 py-2"
                        style="border-radius:10px;">
                        <i class="bi bi-arrow-left me-2"></i> Back to Manage Leaves
                    </a>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="dashboard-card shadow-sm border-0">
                    <div class="card-body p-4">
                        <form action="{{ route('employee.leaves.update', $leave->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                <i class="bi bi-calendar-event me-2"></i>Edit Leave Details
                            </h6>

                            <div class="row g-3 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Subject <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="subject"
                                        class="form-control custom-input @error('subject') is-invalid @enderror"
                                        value="{{ old('subject', $leave->subject) }}" required>
                                    @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">From Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="leave_date_from"
                                        class="form-control custom-input @error('leave_date_from') is-invalid @enderror"
                                        value="{{ old('leave_date_from', $leave->leave_date_from) }}" required>
                                    @error('leave_date_from') <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">To Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="to_leave_date"
                                        class="form-control custom-input @error('to_leave_date') is-invalid @enderror"
                                        value="{{ old('to_leave_date', $leave->to_leave_date) }}" required>
                                    @error('to_leave_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Reason for Leave <span
                                            class="text-danger">*</span></label>
                                    <textarea name="reason" rows="3"
                                        class="form-control custom-input @error('reason') is-invalid @enderror"
                                        required>{{ old('reason', $leave->reason) }}</textarea>
                                    @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Supportive Document (Optional)</label>
                                    <input type="file" name="documents"
                                        class="form-control custom-input @error('documents') is-invalid @enderror"
                                        accept="image/*,application/pdf">
                                    @if($leave->documents)
                                        <small class="text-muted d-block mt-1">Current File: <a
                                                href="{{ asset('storage/' . $leave->documents) }}" target="_blank">View
                                                File</a></small>
                                    @endif
                                    @error('documents') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mt-5 d-flex justify-content-end gap-3 border-top pt-4">
                                <a href="{{ route('employee.leaves.manage') }}"
                                    class="btn btn-light px-5 fw-bold border">Cancel</a>
                                <button type="submit" class="btn btn-primary py-2 px-5 fw-bold">Update Request</button>
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