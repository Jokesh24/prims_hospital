<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Leave Workflow - HRMS</title>
    @include('client.includes.header_links')
</head>

<body>
    @include('client.includes.sidebar')
    <div id="main">
        @include('client.includes.header')
        <div class="content">
            <div class="container-fluid py-2">
                <h4 class="fw-bold mb-4">Create Leave Approval Workflow</h4>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('client.leave_flows.store') }}" method="POST">
                    @csrf
                    <div class="dashboard-card mb-4 p-4 shadow-sm" style="border-radius:10px;">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Workflow Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="flow_name" class="form-control"
                                    placeholder="e.g. IT Dept Approval Chain" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Target Department</label>
                                <select name="department_id" class="form-select">
                                    <option value="">Global (Applies to All)</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Status</label>
                                <select name="status" class="form-select">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="fw-bold border-bottom pb-2 mb-3">Define Approver Chain</h6>
                        <div id="approvers-container">
                            <!-- Dynamic Rows will be appended here -->
                        </div>
                        <button type="button" id="add-approver" class="btn btn-sm btn-outline-primary mt-2"><i
                                class="bi bi-plus"></i> Add Approver Step</button>
                        <p class="text-muted small mt-2"><i class="bi bi-info-circle"></i> The last person in this chain
                            will act as the Final Approver.</p>

                        <div class="text-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-success px-4" style="border-radius:8px;">Save
                                Workflow</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('client.includes.footer_links')

    <!-- Template for JS -->
    <script type="text/template" id="approver-template">
        <div class="row g-2 align-items-center mb-2 approver-row">
            <div class="col-auto">
                <span class="badge bg-dark step-number">1</span>
            </div>
            <div class="col-md-4">
                <select name="approvers[]" class="form-select select2" required>
                    <option value="">Select Approver</option>
                    @foreach($eligibleApprovers as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->employee_name }} ({{ $emp->employee_id }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-danger remove-approver"><i class="bi bi-x"></i></button>
            </div>
        </div>
    </script>

    <script>
        $(document).ready(function () {
            function updateStepNumbers() {
                $('.approver-row').each(function (index) {
                    $(this).find('.step-number').text('Step ' + (index + 1));
                });
            }

            // Init first row
            $('#approvers-container').append($('#approver-template').html());
            updateStepNumbers();

            $('#add-approver').click(function () {
                $('#approvers-container').append($('#approver-template').html());
                updateStepNumbers();
            });

            $(document).on('click', '.remove-approver', function () {
                if ($('.approver-row').length > 1) {
                    $(this).closest('.approver-row').remove();
                    updateStepNumbers();
                } else {
                    alert("You must have at least one approver.");
                }
            });
        });
    </script>
</body>

</html>