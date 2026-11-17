<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Earnings & Deductions
    </title>

    @include('client.includes.header_links')

</head>

<body>

    <div id="overlay" onclick="closeMobile()"></div>

    @include('client.includes.sidebar')

    <div id="main">

        @include('client.includes.header')

        <div class="content">

            <div class="container-fluid py-2">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h4 class="fw-bold m-0">

                            Earnings & Deductions

                        </h4>

                    </div>

                    <div>

                        <a href="{{ route('createComponent') }}" class="btn btn-primary">

                            <i class="bi bi-plus-circle me-1"></i>

                            Add Components

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

                <div class="row g-3">

                    <div class="col-lg-6">

                        <div class="dashboard-card h-100">

                            <div class="d-flex justify-content-between align-items-center mb-3">

                                <h5 class="fw-bold m-0">

                                    Earnings

                                </h5>



                            </div>

                            <div class="table-responsive">

                                <table class="table align-middle">

                                    <thead>

                                        <tr>

                                            <th width="60">
                                                #
                                            </th>

                                            <th>
                                                Title
                                            </th>

                                            <th>
                                                Value
                                            </th>

                                            <th class="text-end">
                                                Action
                                            </th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        @forelse($earnings as $key => $e)

                                            <tr>

                                                <td>
                                                    {{ $key + 1 }}
                                                </td>

                                                <td>

                                                    {{ $e->title }}

                                                </td>

                                                <td>

                                                    @if($e->value_type == 1)

                                                        <span class="badge bg-light text-dark">

                                                            {{ number_format($e->percent, 2) }} %

                                                        </span>

                                                    @else

                                                        <span class="badge bg-success">

                                                            <i class="bi bi-currency-rupee"></i> {{ number_format($e->amount, 2) }}

                                                        </span>

                                                    @endif

                                                </td>

                                                <td class="text-end">

                                                    <a href="{{ route('editComponent', ['id' => $e->id, 'type' => 'earning']) }}"
                                                        class="btn btn-light btn-sm text-primary me-1">

                                                        <i class="bi bi-pencil"></i>

                                                    </a>

                                                    <form action="{{ route('deleteComponent', $e->id) }}" method="POST"
                                                        class="d-inline" onsubmit="return confirm('Delete this earning?')">

                                                        @csrf

                                                        <input type="hidden" name="type" value="earning">

                                                        <button type="submit" class="btn btn-light btn-sm text-danger">

                                                            <i class="bi bi-trash"></i>

                                                        </button>

                                                    </form>



                                                </td>

                                            </tr>

                                        @empty

                                            <tr>

                                                <td colspan="4" class="text-center py-4">

                                                    No Earnings Found

                                                </td>

                                            </tr>

                                        @endforelse

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                    <div class="col-lg-6">

                        <div class="dashboard-card h-100">

                            <div class="d-flex justify-content-between align-items-center mb-3">

                                <h5 class="fw-bold m-0">

                                    Deductions

                                </h5>



                            </div>

                            <div class="table-responsive">

                                <table class="table align-middle">

                                    <thead>

                                        <tr>

                                            <th width="60">
                                                #
                                            </th>

                                            <th>
                                                Title
                                            </th>

                                            <th>
                                                Value
                                            </th>

                                            <th class="text-end">
                                                Action
                                            </th>

                                        </tr>

                                    </thead>

                                    <tbody>

                                        @forelse($deductions as $key => $d)

                                            <tr>

                                                <td>
                                                    {{ $key + 1 }}
                                                </td>

                                                <td>

                                                    {{ $d->title }}

                                                </td>

                                                <td>

                                                    @if($d->value_type == 1)

                                                        <span class="badge bg-light text-dark">

                                                            {{ number_format($d->percent, 2) }} %

                                                        </span>

                                                    @else

                                                        <span class="badge bg-danger">

                                                            <i class="bi bi-currency-rupee"></i>  {{ number_format($d->amount, 2) }}

                                                        </span>

                                                    @endif

                                                </td>

                                                <td class="text-end">

                                                    @if($d)
                                                        <a href="{{ route('editComponent', ['id' => $d->id, 'type' => 'deduction']) }}"
                                                            class="btn btn-light btn-sm text-primary me-1">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>

                                                        <form action="{{ route('deleteComponent', $d->id) }}" method="POST"
                                                            class="d-inline"
                                                            onsubmit="return confirm('Delete this deduction?')">
                                                            @csrf
                                                            <input type="hidden" name="type" value="deduction">
                                                            <button type="submit" class="btn btn-light btn-sm text-danger">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                </td>

                                            </tr>

                                        @empty

                                            <tr>

                                                <td colspan="4" class="text-center py-4">

                                                    No Deductions Found

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

        </div>

    </div>

    @include('client.includes.footer_links')

</body>

</html>