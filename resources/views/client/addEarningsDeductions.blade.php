<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Earnings & Deductions
    </title>

    @include('client.includes.header_links')

</head>

<body>

    <div
        id="overlay"
        onclick="closeMobile()"></div>

    @include('client.includes.sidebar')

    <div id="main">

        @include('client.includes.header')

        <div class="content">

            <div class="container-fluid py-2">

                <div
                    class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h4
                            class="fw-bold m-0">

                            Earnings & Deductions

                        </h4>

                    </div>

                    <div>

                        <a
                            href="{{ route('manageComponents') }}"
                            class="btn btn-outline-secondary">

                            <i class="bi bi-arrow-left me-1"></i>

                            Back

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

                <form
                    method="POST"
                    action="{{ route('storeComponent') }}">

                    @csrf

                    <div class="row g-3">

                        <!-- Earnings -->

                        <div class="col-lg-6">

                            <div class="dashboard-card h-100">

                                <div class="d-flex justify-content-between align-items-center mb-3">

                                    <h5 class="fw-bold m-0">
                                        Earnings
                                    </h5>

                                    <button
                                        type="button"
                                        class="btn btn-success btn-sm"
                                        onclick="addBlock('earningsWrapper','earnings')">

                                        <i class="bi bi-plus-circle me-1"></i>
                                        Add

                                    </button>

                                </div>

                                <div id="earningsWrapper">

                                    @foreach($earnings as $i => $e)

                                    <div class="border rounded p-3 mb-3">

                                        <input
                                            type="hidden"
                                            name="earnings[{{ $i }}][id]"
                                            value="{{ $e->id }}">

                                        <div class="row g-3">

                                            <div class="col-md-6">

                                                <label class="filter-label">
                                                    Title
                                                </label>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    name="earnings[{{ $i }}][title]"
                                                    value="{{ $e->title }}">

                                            </div>

                                            <div class="col-md-3">

                                                <label class="filter-label">
                                                    Type
                                                </label>

                                                <select
                                                    class="form-control value-type"
                                                    name="earnings[{{ $i }}][value_type]">

                                                    <option
                                                        value="1"
                                                        {{ $e->value_type == 1 ? 'selected' : '' }}>
                                                        %
                                                    </option>

                                                    <option
                                                        value="2"
                                                        {{ $e->value_type == 2 ? 'selected' : '' }}>
                                                        ₹
                                                    </option>

                                                </select>

                                            </div>

                                            <div
                                                class="col-md-3 percent-field"
                                                style="{{ $e->value_type == 1 ? '' : 'display:none;' }}">

                                                <label class="filter-label">
                                                    Percentage
                                                </label>

                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    class="form-control"
                                                    name="earnings[{{ $i }}][percent]"
                                                    value="{{ $e->percent }}">

                                            </div>

                                            <div
                                                class="col-md-3 amount-field"
                                                style="{{ $e->value_type == 2 ? '' : 'display:none;' }}">

                                                <label class="filter-label">
                                                    Amount
                                                </label>

                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    class="form-control"
                                                    name="earnings[{{ $i }}][amount]"
                                                    value="{{ $e->amount }}">

                                            </div>

                                        </div>

                                    </div>

                                    @endforeach

                                </div>

                            </div>

                        </div>

                        <!-- Deductions -->

                        <div class="col-lg-6">

                            <div class="dashboard-card h-100">

                                <div class="d-flex justify-content-between align-items-center mb-3">

                                    <h5 class="fw-bold m-0">
                                        Deductions
                                    </h5>

                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm"
                                        onclick="addBlock('deductionsWrapper','deductions')">

                                        <i class="bi bi-plus-circle me-1"></i>
                                        Add

                                    </button>

                                </div>

                                <div id="deductionsWrapper">

                                    @foreach($deductions as $i => $d)

                                    <div class="border rounded p-3 mb-3">

                                        <input
                                            type="hidden"
                                            name="deductions[{{ $i }}][id]"
                                            value="{{ $d->id }}">

                                        <div class="row g-3">

                                            <div class="col-md-6">

                                                <label class="filter-label">
                                                    Title
                                                </label>

                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    name="deductions[{{ $i }}][title]"
                                                    value="{{ $d->title }}">

                                            </div>

                                            <div class="col-md-3">

                                                <label class="filter-label">
                                                    Type
                                                </label>

                                                <select
                                                    class="form-control value-type"
                                                    name="deductions[{{ $i }}][value_type]">

                                                    <option
                                                        value="1"
                                                        {{ $d->value_type == 1 ? 'selected' : '' }}>
                                                        %
                                                    </option>

                                                    <option
                                                        value="2"
                                                        {{ $d->value_type == 2 ? 'selected' : '' }}>
                                                        ₹
                                                    </option>

                                                </select>

                                            </div>

                                            <div
                                                class="col-md-3 percent-field"
                                                style="{{ $d->value_type == 1 ? '' : 'display:none;' }}">

                                                <label class="filter-label">
                                                    Percentage
                                                </label>

                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    class="form-control"
                                                    name="deductions[{{ $i }}][percent]"
                                                    value="{{ $d->percent }}">

                                            </div>

                                            <div
                                                class="col-md-3 amount-field"
                                                style="{{ $d->value_type == 2 ? '' : 'display:none;' }}">

                                                <label class="filter-label">
                                                    Amount
                                                </label>

                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    class="form-control"
                                                    name="deductions[{{ $i }}][amount]"
                                                    value="{{ $d->amount }}">

                                            </div>

                                        </div>

                                    </div>

                                    @endforeach

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="text-end mt-4">

                        <button
                            type="submit"
                            class="btn btn-primary">

                            Save All

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    @include('client.includes.footer_links')

    <script>
        function toggleFields(select) {

            let row =
                select.closest('.row');

            let percentField =
                row.querySelector('.percent-field');

            let amountField =
                row.querySelector('.amount-field');

            if (select.value == '1') {

                percentField.style.display =
                    'block';

                amountField.style.display =
                    'none';

            } else {

                percentField.style.display =
                    'none';

                amountField.style.display =
                    'block';
            }
        }

        document.addEventListener(
            'DOMContentLoaded',
            function() {

                document
                    .querySelectorAll('.value-type')
                    .forEach(function(select) {

                        toggleFields(select);

                    });

            }
        );

        document.addEventListener(
            'change',
            function(e) {

                if (
                    e.target.classList.contains(
                        'value-type'
                    )
                ) {

                    toggleFields(e.target);

                }

            }
        );

        function addBlock(
            wrapperId,
            type
        ) {

            let wrapper =
                document.getElementById(
                    wrapperId
                );

            let index =
                wrapper.children.length;

            let html = `
            <div class="border rounded p-3 mb-3">

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="filter-label">
                            Title
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            name="${type}[${index}][title]">

                    </div>

                    <div class="col-md-3">

                        <label class="filter-label">
                            Type
                        </label>

                        <select
                            class="form-control value-type"
                            name="${type}[${index}][value_type]">

                            <option value="1">%</option>
                            <option value="2">₹</option>

                        </select>

                    </div>

                    <div class="col-md-3 percent-field">

                        <label class="filter-label">
                            Percentage
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="${type}[${index}][percent]">

                    </div>

                    <div
                        class="col-md-3 amount-field"
                        style="display:none;">

                        <label class="filter-label">
                            Amount
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            name="${type}[${index}][amount]">

                    </div>

                </div>

            </div>`;

            wrapper.insertAdjacentHTML(
                'beforeend',
                html
            );
        }
    </script>

</body>

</html>