<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Manage Clients</title>

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

                @if(session('success'))

                    <div class="alert alert-success alert-dismissible fade show mb-4 mt-2 shadow-sm" role="alert"
                        style="border-radius:10px;border-left:5px solid #10b981;">

                        <i class="bi bi-check-circle-fill me-2"></i>

                        <strong>{{ session('success') }}</strong>

                        <button type="button" class="btn-close" data-bs-dismiss="alert">
                        </button>

                    </div>

                @endif

                @if(session('error'))

                    <div class="alert alert-danger alert-dismissible fade show">

                        {{ session('error') }}

                        <button type="button" class="btn-close" data-bs-dismiss="alert">
                        </button>

                    </div>

                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h4 class="fw-bold m-0">

                            Client Directory

                        </h4>

                    </div>

                    <a href="{{ route('createClient') }}" class="btn btn-primary px-4 py-2" style="border-radius:10px;">

                        <i class="bi bi-building-add me-2"></i>

                        Add Client

                    </a>

                </div>

                <div class="dashboard-card mb-3">

                    <form method="GET" action="{{ route('manageClients') }}">

                        <div class="row g-3 align-items-end">

                            <div class="col-md-4">

                                <label class="filter-label">

                                    Search Client

                                </label>

                                <input type="text" name="search" class="filter-control w-100"
                                    value="{{ request('search') }}" placeholder="Name, Email, Mobile...">

                            </div>

                            <div class="col-md-2">

                                <label class="filter-label">

                                    Status

                                </label>

                                <select name="status" class="form-select filter-control">

                                    <option value="">All</option>

                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>

                                        Active

                                    </option>

                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>

                                        Inactive

                                    </option>

                                </select>

                            </div>

                            <div class="col-md-3">

                                <div class="d-flex gap-2">

                                    <button class="btn btn-search-trigger bg-primary text-white">

                                        <i class="bi bi-search"></i>

                                    </button>

                                    <a href="{{ route('manageClients') }}" class="btn btn-refresh bg-light border">

                                        <i class="bi bi-arrow-clockwise"></i>

                                    </a>

                                </div>

                            </div>

                        </div>

                    </form>

                </div>

                <div class="dashboard-card">

                    <div class="table-responsive">

                        <table class="table table-hover table-striped align-middle">

                            <thead class="bg-light">

                                <tr>

                                    <th width="60">Sl.No</th>

                                    <th>Logo</th>

                                    <th>Client Name</th>

                                    <th>Email</th>

                                    <th>Mobile</th>

                                    <th>City</th>

                                    <th>Status</th>

                                    <th class="text-end">

                                        Actions

                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @forelse($clients as $client)

                                    <tr>

                                        <td>

                                            {{ ($clients->currentPage() - 1) * $clients->perPage() + $loop->iteration }}

                                        </td>

                                        <td>

                                            @if($client->logo)

                                                <img src="{{ asset('public/uploads/clients/' . $client->logo) }}"
                                                    style="width:45px;height:45px;border-radius:50%;object-fit:cover;">

                                            @else

                                                <div class="avatar-circle bg-primary text-white"
                                                    style="width:45px;height:45px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;">

                                                    {{ strtoupper(substr($client->client_name, 0, 1)) }}

                                                </div>

                                            @endif

                                        </td>

                                        <td>

                                            <strong>

                                                {{ $client->client_name }}

                                            </strong>

                                        </td>

                                        <td>

                                            {{ $client->client_email }}

                                        </td>

                                        <td>

                                            {{ $client->client_mobile }}

                                        </td>

                                        <td>

                                            {{ $client->city ?? '-' }}

                                        </td>

                                        <td>

                                            @if($client->status)

                                                <span class="badge-soft" style="background:#ecfdf5;color:#10b981;">

                                                    Active

                                                </span>

                                            @else

                                                <span class="badge-soft" style="background:#fef2f2;color:#ef4444;">

                                                    Inactive

                                                </span>

                                            @endif

                                        </td>

                                        <td class="text-end">

                                            <a href="{{ route('editClient', $client->id) }}"
                                                class="btn btn-light btn-sm text-primary">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <form action="{{ route('deleteClient', $client->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Delete this client?')">

                                                @csrf

                                                <button class="btn btn-light btn-sm text-danger">

                                                    <i class="bi bi-trash"></i>

                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="8" class="text-center py-4">

                                            No Clients Found.

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                    <div class="mt-4 d-flex justify-content-end">

                        {{ $clients->links('pagination::bootstrap-5') }}

                    </div>

                </div>

            </div>

        </div>

    </div>

    @include('client.includes.footer_links')

</body>

</html>