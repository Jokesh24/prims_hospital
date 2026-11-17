<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Employee TDS
    </title>

    @include('client.includes.header_links')

</head>

<body>

    <div id="overlay" onclick="closeMobile()">
    </div>

    @include('client.includes.sidebar')

    <div id="main">

        @include('client.includes.header')

        <div class="content">

            <div class="container py-2">


                {{-- Success Message --}}
                @if(session('success'))

                    <div class="alert alert-success alert-dismissible fade show">

                        {{ session('success') }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert">
                        </button>

                    </div>

                @endif


                {{-- Error Message --}}
                @if(session('error'))

                    <div class="alert alert-danger alert-dismissible fade show">

                        {{ session('error') }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert">
                        </button>

                    </div>

                @endif


                {{-- Page Header --}}
                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h4 class="fw-bold mb-1">
                            Employee TDS
                        </h4>

                        <small class="text-muted">
                            Tax Deduction Settings
                        </small>

                    </div>


                    <a href="{{ route('managePayroll') }}" class="btn btn-light border">

                        <i class="bi bi-arrow-left"></i>

                        Back

                    </a>

                </div>


                {{-- Employee Details --}}
                <div class="row mb-4">


                    {{-- Employee ID --}}
                    <div class="col-md-3">

                        <div class="dashboard-card">

                            <small>
                                Employee ID
                            </small>

                            <h6 class="mt-2">
                                {{ $employee->employee_id }}
                            </h6>

                        </div>

                    </div>


                    {{-- Employee Name --}}
                    <div class="col-md-3">

                        <div class="dashboard-card">

                            <small>
                                Employee Name
                            </small>

                            <h6 class="mt-2">
                                {{ $employee->employee_name }}
                            </h6>

                        </div>

                    </div>


                    {{-- Department --}}
                    <div class="col-md-3">

                        <div class="dashboard-card">

                            <small>
                                Department
                            </small>

                            <h6 class="mt-2">
                                {{ $employee->department->department_name ?? '-' }}
                            </h6>

                        </div>

                    </div>


                    {{-- Monthly Gross Salary --}}
                    <div class="col-md-3">

                        <div class="dashboard-card">

                            <small>
                                Monthly Gross Salary
                            </small>

                            <h6 class="mt-2">

                                <i class="bi bi-currency-rupee"></i>

                                {{ number_format($employee->gross_salary, 2) }}

                            </h6>

                        </div>

                    </div>

                </div>


                {{-- TDS Calculation --}}
                <div class="dashboard-card">


                    <form method="POST" action="{{ route('updateEmployeeTDS', $employee->id) }}">

                        @csrf


                        {{-- New Tax Regime --}}
                        <input type="hidden" name="tax_regime" value="2">


                        <div class="row g-3">


                            {{-- Calculation Card --}}
                            <div class="col-md-12">

                                <div class="card border-0 bg-light">

                                    <div class="card-body">


                                        {{-- Calculation Header --}}
                                        <div class="d-flex justify-content-between align-items-center mb-4">

                                            <div>

                                                <h6 class="fw-bold mb-1">

                                                    <i class="bi bi-calculator me-2"></i>

                                                    New Regime TDS Calculation

                                                </h6>

                                                <small class="text-muted">

                                                    TDS is calculated automatically based on salary.

                                                </small>

                                            </div>


                                            <span class="badge bg-primary">

                                                New Tax Regime

                                            </span>

                                        </div>


                                        <div class="row g-3">


                                            {{-- Monthly Gross --}}
                                            <div class="col-md-3">

                                                <label class="form-label text-muted">

                                                    Monthly Gross Salary

                                                </label>

                                                <div class="form-control bg-white">

                                                    {{ number_format($employee->gross_salary, 2) }}

                                                </div>

                                            </div>


                                            {{-- Annual Gross --}}
                                            <div class="col-md-3">

                                                <label class="form-label text-muted">

                                                    Annual Gross Salary

                                                </label>

                                                <div class="form-control bg-white">

                                                    <i class="bi bi-currency-rupee"></i>

                                                    <span id="annualGross">
                                                        0.00
                                                    </span>

                                                </div>

                                            </div>


                                            {{-- Standard Deduction --}}
                                            <div class="col-md-3">

                                                <label class="form-label text-muted">

                                                    Standard Deduction

                                                </label>

                                                <div class="form-control bg-white">

                                                    <i class="bi bi-currency-rupee"></i>

                                                    75,000.00

                                                </div>

                                            </div>


                                            {{-- Taxable Income --}}
                                            <div class="col-md-3">

                                                <label class="form-label text-muted">

                                                    Taxable Income

                                                </label>

                                                <div class="form-control bg-white">

                                                    <i class="bi bi-currency-rupee"></i>

                                                    <span id="taxableIncome">
                                                        0.00
                                                    </span>

                                                </div>

                                            </div>


                                            {{-- Applicable Tax Slab --}}
                                            <div class="col-md-3">

                                                <label class="form-label text-muted">

                                                    Applicable Tax Slab

                                                </label>

                                                <div class="form-control bg-white" id="tdsPercentageDisplay">

                                                    0.00%

                                                </div>


                                                {{-- Saved to database --}}
                                                <input type="hidden" name="tds_percentage" id="tds_percentage" value="">

                                            </div>


                                            {{-- Income Tax --}}
                                            <div class="col-md-3">

                                                <label class="form-label text-muted">

                                                    Income Tax

                                                </label>

                                                <div class="form-control bg-white">

                                                    <i class="bi bi-currency-rupee"></i>

                                                    <span id="incomeTax">
                                                        0.00
                                                    </span>

                                                </div>

                                            </div>


                                            {{-- 87A Rebate --}}
                                            <div class="col-md-3">

                                                <label class="form-label text-muted">

                                                    87A Rebate

                                                </label>

                                                <div class="form-control bg-white text-success" id="rebateAmount">

                                                    0.00

                                                </div>

                                            </div>


                                            {{-- Cess --}}
                                            <div class="col-md-3">

                                                <label class="form-label text-muted">

                                                    Health & Education Cess

                                                </label>

                                                <div class="form-control bg-white">

                                                    <i class="bi bi-currency-rupee"></i>

                                                    <span id="cessAmount">
                                                        0.00
                                                    </span>

                                                </div>

                                            </div>


                                            {{-- Annual TDS --}}
                                            <div class="col-md-3">

                                                <label class="form-label text-muted">

                                                    Annual TDS

                                                </label>

                                                <div class="form-control bg-white text-danger fw-bold">

                                                    <i class="bi bi-currency-rupee"></i>

                                                    <span id="annualTds">
                                                        0.00
                                                    </span>

                                                </div>

                                            </div>


                                            {{-- Monthly TDS --}}
                                            <div class="col-md-3">

                                                <label class="form-label text-muted">

                                                    Monthly TDS

                                                </label>

                                                <div class="form-control bg-white text-primary fw-bold">

                                                    <i class="bi bi-currency-rupee"></i>

                                                    <span id="monthlyTds">
                                                        0.00
                                                    </span>

                                                </div>


                                                {{-- Saved to database --}}
                                                <input type="hidden" name="tds_amount" id="tds_amount" value="">

                                            </div>


                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- Save Button --}}
                            <div class="col-md-12">

                                <button type="submit" class="btn btn-primary px-4">

                                    <i class="bi bi-check-circle me-2"></i>

                                    Save TDS Details

                                </button>

                            </div>


                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>


    @include('client.includes.footer_links')


    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {


                /*
                |--------------------------------------------------------------------------
                | Employee Monthly Gross Salary
                |--------------------------------------------------------------------------
                */

                const monthlyGross =
                    {{ (float) $employee->gross_salary }};


                /*
                |--------------------------------------------------------------------------
                | Standard Deduction
                |--------------------------------------------------------------------------
                */

                const standardDeduction =
                    75000;


                /*
                |--------------------------------------------------------------------------
                | Currency Formatter
                |--------------------------------------------------------------------------
                */

                function formatCurrency(value) {

                    return '' +
                        Number(value).toLocaleString(
                            'en-IN',
                            {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }
                        );

                }


                /*
                |--------------------------------------------------------------------------
                | Calculate TDS
                |--------------------------------------------------------------------------
                */

                function calculateTDS() {


                    /*
                    |--------------------------------------------------------------------------
                    | Annual Gross
                    |--------------------------------------------------------------------------
                    */

                    const annualGross =
                        monthlyGross * 12;


                    /*
                    |--------------------------------------------------------------------------
                    | Taxable Income
                    |--------------------------------------------------------------------------
                    */

                    const taxableIncome =
                        Math.max(
                            0,
                            annualGross - standardDeduction
                        );


                    let tax = 0;

                    let slabPercentage = 0;


                    /*
                    |--------------------------------------------------------------------------
                    | New Tax Regime Slabs
                    |--------------------------------------------------------------------------
                    */

                    if (taxableIncome <= 400000) {

                        slabPercentage = 0;

                        tax = 0;

                    }
                    else if (taxableIncome <= 800000) {

                        slabPercentage = 5;

                        tax =
                            (taxableIncome - 400000)
                            * 0.05;

                    }
                    else if (taxableIncome <= 1200000) {

                        slabPercentage = 10;

                        tax =
                            20000 +
                            (
                                (taxableIncome - 800000)
                                * 0.10
                            );

                    }
                    else if (taxableIncome <= 1600000) {

                        slabPercentage = 15;

                        tax =
                            60000 +
                            (
                                (taxableIncome - 1200000)
                                * 0.15
                            );

                    }
                    else if (taxableIncome <= 2000000) {

                        slabPercentage = 20;

                        tax =
                            120000 +
                            (
                                (taxableIncome - 1600000)
                                * 0.20
                            );

                    }
                    else if (taxableIncome <= 2400000) {

                        slabPercentage = 25;

                        tax =
                            200000 +
                            (
                                (taxableIncome - 2000000)
                                * 0.25
                            );

                    }
                    else {

                        slabPercentage = 30;

                        tax =
                            300000 +
                            (
                                (taxableIncome - 2400000)
                                * 0.30
                            );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | 87A Rebate
                    |--------------------------------------------------------------------------
                    */

                    let rebate = 0;


                    if (taxableIncome <= 1200000) {

                        rebate =
                            Math.min(
                                tax,
                                60000
                            );


                        tax =
                            Math.max(
                                0,
                                tax - rebate
                            );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Health & Education Cess
                    |--------------------------------------------------------------------------
                    */

                    const cess =
                        tax * 0.04;


                    /*
                    |--------------------------------------------------------------------------
                    | Annual TDS
                    |--------------------------------------------------------------------------
                    */

                    const annualTds =
                        tax + cess;


                    /*
                    |--------------------------------------------------------------------------
                    | Monthly TDS
                    |--------------------------------------------------------------------------
                    */

                    const monthlyTds =
                        annualTds / 12;


                    /*
                    |--------------------------------------------------------------------------
                    | Display Values
                    |--------------------------------------------------------------------------
                    */

                    document.getElementById(
                        'annualGross'
                    ).innerText =
                        formatCurrency(
                            annualGross
                        );


                    document.getElementById(
                        'taxableIncome'
                    ).innerText =
                        formatCurrency(
                            taxableIncome
                        );


                    document.getElementById(
                        'tdsPercentageDisplay'
                    ).innerText =
                        slabPercentage.toFixed(2) + '%';


                    document.getElementById(
                        'incomeTax'
                    ).innerText =
                        formatCurrency(
                            tax
                        );


                    document.getElementById(
                        'rebateAmount'
                    ).innerText =
                        formatCurrency(
                            rebate
                        );


                    document.getElementById(
                        'cessAmount'
                    ).innerText =
                        formatCurrency(
                            cess
                        );


                    document.getElementById(
                        'annualTds'
                    ).innerText =
                        formatCurrency(
                            annualTds
                        );


                    document.getElementById(
                        'monthlyTds'
                    ).innerText =
                        formatCurrency(
                            monthlyTds
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Hidden Database Values
                    |--------------------------------------------------------------------------
                    */

                    document.getElementById(
                        'tds_percentage'
                    ).value =
                        slabPercentage.toFixed(2);


                    document.getElementById(
                        'tds_amount'
                    ).value =
                        monthlyTds.toFixed(2);

                }


                /*
                |--------------------------------------------------------------------------
                | Run Automatically
                |--------------------------------------------------------------------------
                */

                calculateTDS();

            }
        );

    </script>


</body>

</html>