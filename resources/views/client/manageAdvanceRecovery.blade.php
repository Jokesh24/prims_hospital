<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>

        Advance Recovery

    </title>

    @include('client.includes.header_links')

</head>

<body>

    <div id="overlay"
        onclick="closeMobile()">
    </div>

    @include('client.includes.sidebar')

    <div id="main">

        @include('client.includes.header')

        <div class="content">

            <div class="container py-2">

                @if(session('success'))

                <div class="alert alert-success alert-dismissible fade show">

                    {{ session('success') }}

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                    </button>

                </div>

                @endif

                @if(session('error'))

                <div class="alert alert-danger alert-dismissible fade show">

                    {{ session('error') }}

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                    </button>

                </div>

                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h4 class="fw-bold mb-1">

                            Advance Recovery
                             <small class="text-muted">

                            -

                            {{ $advance->employee->employee_name }}

                        </small>

                        </h4>

                       

                    </div>

                    <div>

                        <a
                            href="{{ route('advances') }}"
                            class="btn btn-light border">

                            <i class="bi bi-arrow-left"></i>

                            Back

                        </a>

                        <button
                            class="btn btn-primary"
                            id="addRecoveryBtn"
                            data-bs-toggle="modal"
                            data-bs-target="#recoveryModal">

                            <i class="bi bi-plus-circle me-2"></i>

                            Add Recovery

                        </button>

                    </div>

                </div>

                <div class="row mb-3">

                    <div class="col-md-3">

                        <div class="dashboard-card">

                            <small>

                                Advance Amount

                            </small>

                            <h5 class="mt-2">

                               <i class="bi bi-currency-rupee"></i> {{ number_format($advance->advance_amount,2) }}

                            </h5>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="dashboard-card">

                            <small>

                                Recovered

                            </small>

                            <h5 class="mt-2 text-success">

                               <i class="bi bi-currency-rupee"></i> {{ number_format($advance->recovered_amount,2) }}

                            </h5>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="dashboard-card">

                            <small>

                                Pending

                            </small>

                            <h5 class="mt-2 text-danger">

                               <i class="bi bi-currency-rupee"></i> {{ number_format($advance->balance_amount,2) }}

                            </h5>

                        </div>

                    </div>

                    <div class="col-md-3">

                        <div class="dashboard-card">

                            <small>

                                Recovery Type

                            </small>

                            <h5 class="mt-2">

                                @if($advance->recovery_type==1)

                                Monthly EMI

                                @else

                                Full Recovery

                                @endif

                            </h5>

                        </div>

                    </div>

                </div>

                <div class="dashboard-card">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>

                                <tr>

                                    <th>

                                        Sl No

                                    </th>

                                    <th>

                                        Recovery Date

                                    </th>

                                    <th>

                                        Amount

                                    </th>

                                    <th>

                                        Balance Before

                                    </th>

                                    <th>

                                        Balance After

                                    </th>

                                    <th>

                                        Recovery

                                    </th>

                                    <th>

                                        Payment

                                    </th>

                                    <th>

                                        Reference No

                                    </th>

                                    <th>

                                        Remarks

                                    </th>

                                    <th>

                                        Action

                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @forelse($recoveries as $recovery)

                                <tr>

                                    <td>

                                        {{ $loop->iteration }}

                                    </td>

                                    <td>

                                        {{ \Carbon\Carbon::parse($recovery->recovery_date)->format('d-m-Y') }}

                                    </td>

                                    <td>

                                       <i class="bi bi-currency-rupee"></i> {{ number_format($recovery->recovered_amount,2) }}

                                    </td>

                                    <td>

                                       <i class="bi bi-currency-rupee"></i> {{ number_format($recovery->balance_before,2) }}

                                    </td>

                                    <td>

                                        <i class="bi bi-currency-rupee"></i>{{ number_format($recovery->balance_after,2) }}

                                    </td>

                                    <td>

                                        @if($recovery->recovery_type==1)

                                        <span class="badge bg-primary">

                                            Payroll

                                        </span>

                                        @else

                                        <span class="badge bg-success">

                                            Manual

                                        </span>

                                        @endif

                                    </td>

                                    <td>

                                        @switch($recovery->payment_mode)

                                        @case(1)

                                        Cash

                                        @break

                                        @case(2)

                                        Bank

                                        @break

                                        @case(3)

                                        Cheque

                                        @break

                                        @case(4)

                                        UPI

                                        @break

                                        @default

                                        Salary Deduction

                                        @endswitch

                                    </td>

                                    <td>

                                        {{ $recovery->reference_number ?? '-' }}

                                    </td>

                                    <td>

                                        {{ $recovery->remarks ?? '-' }}

                                    </td>

                                    <td>

                                        @if($recovery->recovery_type==2)

                                        <button

                                            class="btn btn-light btn-sm text-primary editRecovery"

                                            data-id="{{ $recovery->id }}"

                                            data-date="{{ $recovery->recovery_date }}"

                                            data-amount="{{ $recovery->recovered_amount }}"

                                            data-payment="{{ $recovery->payment_mode }}"

                                            data-reference="{{ $recovery->reference_number }}"

                                            data-remarks="{{ $recovery->remarks }}">

                                            <i class="bi bi-pencil"></i>

                                        </button>

                                        @endif

                                    </td>

                                </tr>

                                @empty

                                <tr>

                                    <td colspan="10"
                                        class="text-center">

                                        No Recovery Found.

                                    </td>

                                </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>
                                <!-- Recovery Modal -->

                <div class="modal fade"
                    id="recoveryModal"
                    tabindex="-1">

                    <div class="modal-dialog modal-lg">

                        <div class="modal-content">

                            <form
                                id="recoveryForm"
                                method="POST"
                                action="{{ route('advanceRecovery.store') }}">

                                @csrf

                                <input
                                    type="hidden"
                                    name="advance_id"
                                    value="{{ $advance->id }}">

                                <div class="modal-header">

                                    <h5
                                        class="modal-title"
                                        id="modalTitle">

                                        Add Recovery

                                    </h5>

                                    <button
                                        type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal">
                                    </button>

                                </div>

                                <div class="modal-body">

                                    <div class="row g-3">

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                Recovery Date

                                            </label>

                                            <input
                                                type="date"
                                                name="recovery_date"
                                                id="recovery_date"
                                                value="{{ date('Y-m-d') }}"
                                                class="form-control"
                                                required>

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                Recovery Amount

                                            </label>

                                            <input
                                                type="number"
                                                min="1"
                                                step="0.01"
                                                name="recovered_amount"
                                                id="recovered_amount"
                                                class="form-control"
                                                required>

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                Payment Mode

                                            </label>

                                            <select
                                                name="payment_mode"
                                                id="payment_mode"
                                                class="form-select"
                                                required>

                                                <option value="1">

                                                    Cash

                                                </option>

                                                <option value="2">

                                                    Bank Transfer

                                                </option>

                                                <option value="3">

                                                    Cheque

                                                </option>

                                                <option value="4">

                                                    UPI

                                                </option>

                                            </select>

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                Reference No

                                            </label>

                                            <input
                                                type="text"
                                                name="reference_number"
                                                id="reference_number"
                                                class="form-control"
                                                placeholder="Optional">

                                        </div>

                                        <div class="col-md-12">

                                            <label class="form-label">

                                                Remarks

                                            </label>

                                            <textarea
                                                name="remarks"
                                                id="remarks"
                                                rows="3"
                                                class="form-control"
                                                placeholder="Optional"></textarea>

                                        </div>

                                    </div>

                                </div>

                                <div class="modal-footer">

                                    <button
                                        type="button"
                                        class="btn btn-light"
                                        data-bs-dismiss="modal">

                                        Cancel

                                    </button>

                                    <button
                                        type="submit"
                                        id="saveBtn"
                                        class="btn btn-primary">

                                        Save Recovery

                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    @include('client.includes.footer_links')

    <script>

        document.addEventListener(
            "DOMContentLoaded",
            function () {

                const form =
                    document.getElementById(
                        "recoveryForm"
                    );

                const title =
                    document.getElementById(
                        "modalTitle"
                    );

                const saveBtn =
                    document.getElementById(
                        "saveBtn"
                    );

                /*
                |--------------------------------------------------------------------------
                | Add Recovery
                |--------------------------------------------------------------------------
                */

                document
                    .getElementById(
                        "addRecoveryBtn"
                    )
                    .addEventListener(
                        "click",
                        function () {

                            form.reset();

                            form.action =
                                "{{ route('advanceRecovery.store') }}";

                            title.innerHTML =
                                "Add Recovery";

                            saveBtn.innerHTML =
                                "Save Recovery";

                            document
                                .getElementById(
                                    "recovery_date"
                                )
                                .value =
                                "{{ date('Y-m-d') }}";

                        }
                    );

                /*
                |--------------------------------------------------------------------------
                | Edit Recovery
                |--------------------------------------------------------------------------
                */

                document
                    .querySelectorAll(
                        ".editRecovery"
                    )
                    .forEach(function (button) {

                        button.addEventListener(
                            "click",
                            function () {

                                form.action =
                                    "{{ url('/advanceRecovery/update') }}/" +
                                    this.dataset.id;

                                title.innerHTML =
                                    "Edit Recovery";

                                saveBtn.innerHTML =
                                    "Update Recovery";

                                document
                                    .getElementById(
                                        "recovery_date"
                                    )
                                    .value =
                                    this.dataset.date;

                                document
                                    .getElementById(
                                        "recovered_amount"
                                    )
                                    .value =
                                    this.dataset.amount;

                                document
                                    .getElementById(
                                        "payment_mode"
                                    )
                                    .value =
                                    this.dataset.payment;

                                document
                                    .getElementById(
                                        "reference_number"
                                    )
                                    .value =
                                    this.dataset.reference;

                                document
                                    .getElementById(
                                        "remarks"
                                    )
                                    .value =
                                    this.dataset.remarks;

                                new bootstrap.Modal(
                                    document.getElementById(
                                        "recoveryModal"
                                    )
                                ).show();

                            });

                    });

            });

    </script>

</body>

</html>