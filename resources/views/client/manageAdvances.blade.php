<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Employee Advances
    </title>

    @include('client.includes.header_links')
    <style>
.select2-container{width:100%!important;}
.select2-container--bootstrap-5 .select2-selection,
.select2-container .select2-selection{
    min-height:38px;
}
</style>

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

                <div class="alert alert-success alert-dismissible fade show mb-3">

                    {{ session('success') }}

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                    </button>

                </div>

                @endif

                @if(session('error'))

                <div class="alert alert-danger alert-dismissible fade show mb-3">

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

                        <h4 class="fw-bold m-0">

                            Employee Advances

                        </h4>

                    </div>

                  <button
    id="addAdvance"
    class="btn btn-primary"

    data-bs-toggle="modal"
    data-bs-target="#advanceModal">

    <i class="bi bi-cash-stack me-2"></i>

    Give Advance

</button>

                </div>

                <div class="dashboard-card mb-3">

                    <form
                        method="GET"
                        action="{{ route('advances') }}">

                        <div class="row g-3 align-items-end">

                            <div class="col-md-4">

                                <label class="filter-label">

                                    Search Employee

                                </label>

                                <input
                                    type="text"
                                    name="search"
                                    class="filter-control w-100"
                                    value="{{ request('search') }}"
                                    placeholder="Employee Name / Employee ID">

                            </div>

                            <div class="col-md-3">

                                <button
                                    class="btn btn-primary">

                                    <i class="bi bi-search"></i>

                                </button>

                                <a
                                    href="{{ route('advances') }}"
                                    class="btn btn-light border">

                                    <i class="bi bi-arrow-clockwise"></i>

                                </a>

                            </div>

                        </div>

                    </form>

                </div>

                <div class="dashboard-card">

                    <div class="table-responsive">

                        <table
                            class="table table-hover table-striped align-middle">

                            <thead>

                                <tr>

                                    <th>Sl.No</th>

                                    <th>Emp ID</th>

                                    <th>Employee</th>

                                    <th>Date</th>

                                    <th class="text-end">

                                        Advance

                                    </th>

                                    <th class="text-end">

                                        Recovered

                                    </th>

                                    <th class="text-end">

                                        Pending

                                    </th>

                                    <th>

                                        Recovery

                                    </th>

                                    <th>

                                        Payment

                                    </th>

                                    <th>

                                        Actions

                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @forelse($advances as $advance)

                                <tr>

                                    <td>

                                        {{
                                        ($advances->currentPage()-1)
                                        *
                                        $advances->perPage()
                                        +
                                        $loop->iteration
                                        }}

                                    </td>

                                    <td>

                                        {{ $advance->employee->employee_id }}

                                    </td>

                                    <td>

                                        {{ $advance->employee->employee_name }}

                                    </td>

                                    <td>

                                        {{
                                        \Carbon\Carbon::parse(
                                        $advance->advance_date
                                        )->format('d-m-Y')
                                        }}

                                    </td>

                                    <td class="text-end">

                                        <i class="bi bi-currency-rupee"></i> {{ number_format(
                                        $advance->advance_amount,
                                        2
                                        ) }}

                                    </td>

                                    <td class="text-end text-success">

                                        <i class="bi bi-currency-rupee"></i> {{ number_format(
                                        $advance->recovered_amount,
                                        2
                                        ) }}

                                    </td>

                                    <td class="text-end text-danger fw-bold">

                                        <i class="bi bi-currency-rupee"></i> {{ number_format(
                                        $advance->balance_amount,
                                        2
                                        ) }}

                                    </td>

                                    <td>

                                        @if($advance->recovery_type==1)

                                        Monthly EMI

                                        @else

                                        Full Recovery

                                        @endif

                                    </td>

                                    <td>

                                        @switch($advance->payment_mode)

                                        @case(1)

                                        Cash

                                        @break

                                        @case(2)

                                        Bank Transfer

                                        @break

                                        @case(3)

                                        Cheque

                                        @break

                                        @default

                                        UPI

                                        @endswitch

                                    </td>

                                    <td>
                                        <a
    href="{{ route('advanceRecovery', $advance->id) }}"
    class="btn btn-light btn-sm text-success"
    title="Advance Recoveries">

    <i class="bi bi-cash-coin"></i>

</a>

                                        <button
    class="btn btn-light btn-sm text-primary editAdvance"

    data-id="{{ $advance->id }}"

    data-employee="{{ $advance->employee_id }}"

    data-date="{{ $advance->advance_date }}"

    data-amount="{{ $advance->advance_amount }}"

    data-recovery="{{ $advance->recovery_type }}"

    data-emi="{{ $advance->monthly_recovery_amount }}"

    data-payment="{{ $advance->payment_mode }}"

    data-reference="{{ $advance->reference_number }}"

    data-remarks="{{ $advance->remarks }}">

    <i class="bi bi-pencil"></i>

</button>

                                        <form
                                            action="{{ route('advances.delete',$advance->id) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Delete this advance?')">

                                            @csrf

                                            <button
                                                class="btn btn-light btn-sm text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </form>

                                    </td>

                                </tr>

                                @empty

                                <tr>

                                    <td
                                        colspan="10"
                                        class="text-center py-4">

                                        No Advances Found.

                                    </td>

                                </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                    <div class="mt-3 d-flex justify-content-end">

                        {{ $advances->links('pagination::bootstrap-5') }}

                    </div>

                </div>
                                <!-- Give Advance Modal -->

                <div class="modal fade"
                    id="advanceModal"
                    tabindex="-1">

                    <div class="modal-dialog modal-lg">

                        <div class="modal-content">

                           <form
id="advanceForm"
action="{{ route('advances.store') }}"
method="POST">

                                @csrf

                                <div class="modal-header">

                                    <h5
id="advanceTitle"
class="modal-title">

Give Employee Advance

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

                                                Employee

                                            </label>

                                           <select name="employee_id"
                id="employee_id"
                class="form-select employee-select"
                required>
            <option value="">Select Employee</option>

            @foreach($employees as $employee)
            <option value="{{ $employee->id }}">
                {{ $employee->employee_id }} - {{ $employee->employee_name }}
            </option>
            @endforeach
        </select>

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                Advance Date

                                            </label>

                                            <input
                                                type="date"
                                                name="advance_date"
                                                value="{{ date('Y-m-d') }}"
                                                class="form-control"
                                                required>

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                Advance Amount

                                            </label>

                                            <input
                                                type="number"
                                                min="1"
                                                step="0.01"
                                                name="advance_amount"
                                                class="form-control"
                                                required>

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                Recovery Type

                                            </label>

                                            <select
                                                name="recovery_type"
                                                id="recovery_type"
                                                class="form-select"
                                                required>

                                                <option value="1">

                                                    Monthly EMI

                                                </option>

                                                <option value="2">

                                                    Full Recovery

                                                </option>

                                            </select>

                                        </div>

                                        <div
                                            class="col-md-6"
                                            id="emiDiv">

                                            <label class="form-label">

                                                Monthly EMI Amount

                                            </label>

                                            <input
                                                type="number"
                                                min="1"
                                                step="0.01"
                                                name="monthly_recovery_amount"
                                                class="form-control">

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                Payment Mode

                                            </label>

                                            <select
                                                name="payment_mode"
                                                class="form-select">

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
                                                class="form-control"
                                                placeholder="Optional">

                                        </div>

                                        <div class="col-md-6">

                                            <label class="form-label">

                                                Remarks

                                            </label>

                                            <textarea
                                                name="remarks"
                                                rows="2"
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
id="saveAdvance"

class="btn btn-primary">

Save Advance

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

                const recovery =
                    document.getElementById(
                        "recovery_type"
                    );

                const emiDiv =
                    document.getElementById(
                        "emiDiv"
                    );

                function toggleEMI() {

                    if (recovery.value == "1") {

                        emiDiv.style.display =
                            "block";

                    } else {

                        emiDiv.style.display =
                            "none";

                    }

                }

                recovery.addEventListener(
                    "change",
                    toggleEMI
                );

                toggleEMI();

            });


            document.addEventListener(
'DOMContentLoaded',

function(){

const form =
document.getElementById(
'advanceForm'
);

const title =
document.getElementById(
'advanceTitle'
);

const button =
document.getElementById(
'saveAdvance'
);

/*
|--------------------------------------------------------------------------
| Add
|--------------------------------------------------------------------------
*/

document
.getElementById(
'addAdvance'
)
.addEventListener(
'click',
function(){

form.reset();

form.action =
"{{ route('advances.store') }}";

title.innerHTML =
"Give Employee Advance";

button.innerHTML =
"Save Advance";

toggleEMI();

});

/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

document
.querySelectorAll(
'.editAdvance'
)
.forEach(function(btn){

btn.addEventListener(
'click',
function(){

form.action =
"{{ url('/advances/update') }}/"+
this.dataset.id;

title.innerHTML =
"Edit Advance";

button.innerHTML =
"Update Advance";

form.employee_id.value =
this.dataset.employee;

form.advance_date.value =
this.dataset.date;

form.advance_amount.value =
this.dataset.amount;

form.recovery_type.value =
this.dataset.recovery;

form.monthly_recovery_amount.value =
this.dataset.emi;

form.payment_mode.value =
this.dataset.payment;

form.reference_number.value =
this.dataset.reference;

form.remarks.value =
this.dataset.remarks;

toggleEMI();

new bootstrap.Modal(
document.getElementById(
'advanceModal'
)
).show();

});

});

function toggleEMI(){

const recovery =
document.getElementById(
'recovery_type'
);

const emi =
document.getElementById(
'emiDiv'
);

emi.style.display =
recovery.value=="1"
?
'block'
:
'none';

}

document
.getElementById(
'recovery_type'
)
.addEventListener(
'change',
toggleEMI
);

toggleEMI();

});

    </script>
    
<script>
$(function () {

    // If used inside Bootstrap Modal
    $('#advanceModal').on('shown.bs.modal', function () {

        $('.employee-select').select2({
            dropdownParent: $('#advanceModal'),
            placeholder: "Search Employee",
            allowClear: true,
            width: '100%'
        });

    });

});

// While editing
function setEmployee(id){
    $('#employee_id')
        .val(id)
        .trigger('change');
}
</script>

</body>

</html>