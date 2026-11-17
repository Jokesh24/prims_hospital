<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Office Timings
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

                    <h4 class="fw-bold m-0">
                        Office Timings
                    </h4>

                    <button type="button" class="btn btn-primary"
                        onclick="window.location='{{ route('createOfficeTimings') }}'">

                        <i class="bi bi-pencil-square me-1"></i>

                        Update Timings

                    </button>

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

                <div class="dashboard-card">

                    {{-- DEFAULT OFFICE TIMING --}}
                    <div class="mb-4">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-building me-2 text-primary"></i>
                            Default Timings
                        </h5>

                        <div class="table-responsive">

                            <table class="table align-middle">

                                <thead>
                                    <tr>

                                        <th>
                                            Login Time
                                        </th>

                                        <th>
                                            Logout Time
                                        </th>

                                        <th>
                                            Grace Period
                                        </th>

                                        <th>
                                            Half Day
                                        </th>

                                    </tr>
                                </thead>

                                <tbody>

                                    <tr>

                                        <td>
                                            {{ !empty($settings->start_time)
                                ? \Carbon\Carbon::parse($settings->start_time)->format('h:i A')
                                : '-' }}
                                        </td>

                                        <td>
                                            {{ !empty($settings->end_time)
                                ? \Carbon\Carbon::parse($settings->end_time)->format('h:i A')
                                : '-' }}
                                        </td>

                                        <td>
                                            {{ $settings->grace_time ?? '-' }}

                                            @if($settings && $settings->grace_time !== null)
                                            Minutes
                                            @endif
                                        </td>

                                        <td>
                                            {{ $settings->half_day_limit ?? '-' }}

                                            @if($settings && $settings->half_day_limit !== null)
                                            Hours
                                            @endif
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>


                    {{-- DEPARTMENT TIMINGS --}}
                    <div class="mt-4">

                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-diagram-3 me-2 text-primary"></i>
                            Department Timings
                        </h5>

                        <div class="table-responsive">

                            <table class="table align-middle">

                                <thead>

                                    <tr>

                                        <th>
                                            Department Name
                                        </th>

                                        <th>
                                            Login Time
                                        </th>

                                        <th>
                                            Logout Time
                                        </th>

                                        <th>
                                            Grace Period
                                        </th>

                                        <th>
                                            Half Day
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @forelse($departmentTimings as $timing)

                                    <tr>

                                        <td class="fw-semibold">

                                            {{ $timing->department->department_name ?? '-' }}

                                        </td>

                                        <td>

                                            {{ !empty($timing->start_time)
                                    ? \Carbon\Carbon::parse($timing->start_time)->format('h:i A')
                                    : '-' }}

                                        </td>

                                        <td>

                                            {{ !empty($timing->end_time)
                                    ? \Carbon\Carbon::parse($timing->end_time)->format('h:i A')
                                    : '-' }}

                                        </td>

                                        <td>

                                            {{ $timing->grace_time ?? '-' }}

                                            @if($timing->grace_time !== null)
                                            Minutes
                                            @endif

                                        </td>

                                        <td>

                                            {{ $timing->half_day_limit ?? '-' }}

                                            @if($timing->half_day_limit !== null)
                                            Hours
                                            @endif

                                        </td>

                                    </tr>

                                    @empty

                                    <tr>

                                        <td colspan="5" class="text-center text-muted py-4">

                                            No department-specific timings configured.

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

    @include('client.includes.footer_links')


</body>

</html>