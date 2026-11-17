<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>

        {{ isset($client) ? 'Edit Client' : 'Create Client' }}

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

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h4 class="fw-bold">

                            {{ isset($client) ? 'Edit Client' : 'Create Client' }}

                        </h4>

                    </div>

                    <a href="{{ route('manageClients') }}"
                        class="btn btn-light border">

                        <i class="bi bi-arrow-left me-2"></i>

                        Back

                    </a>

                </div>

                <div class="dashboard-card">

                    <form method="POST"
                        action="{{ isset($client) ? route('updateClient',$client->id) : route('storeClient') }}"
                        enctype="multipart/form-data">

                        @csrf

                        <div class="row g-4">

                            <div class="col-md-3">

                                <label class="form-label">

                                    Client Name
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="text"
                                    name="client_name"
                                    class="form-control @error('client_name') is-invalid @enderror"
                                    value="{{ old('client_name',$client->client_name ?? '') }}"
                                    >

                                @error('client_name')

                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>

                                @enderror

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">

                                    Email
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="email"
                                    name="client_email"
                                    class="form-control @error('client_email') is-invalid @enderror"
                                    value="{{ old('client_email',$client->client_email ?? '') }}"
                                    >

                                @error('client_email')

                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>

                                @enderror

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">

                                    Mobile
                                    <span class="text-danger">*</span>

                                </label>

                                <input type="text"
                                    name="client_mobile"
                                    maxlength="10"
                                    class="form-control @error('client_mobile') is-invalid @enderror"
                                    value="{{ old('client_mobile',$client->client_mobile ?? '') }}"
                                    >

                                @error('client_mobile')

                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>

                                @enderror

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">

                                    Password

                                    @if(!isset($client))

                                    <span class="text-danger">*</span>

                                    @endif

                                </label>

                                <input type="password"
                                    name="client_password"
                                    class="form-control @error('client_password') is-invalid @enderror"
                                    {{ isset($client) ? '' : '' }}>

                                @if(isset($client))

                                <small class="text-muted">

                                    Leave blank to keep existing password.

                                </small>

                                @endif

                                @error('client_password')

                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>

                                @enderror

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">

                                    GST Number

                                </label>

                                <input type="text"
                                    name="client_gst"
                                    class="form-control"
                                    value="{{ old('client_gst',$client->client_gst ?? '') }}">

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">

                                    PAN Number

                                </label>

                                <input type="text"
                                    name="client_pan"
                                    class="form-control"
                                    value="{{ old('client_pan',$client->client_pan ?? '') }}">

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">

                                    City

                                </label>

                                <input type="text"
                                    name="city"
                                    class="form-control"
                                    value="{{ old('city',$client->city ?? '') }}">

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">

                                    Status

                                </label>

                                <select name="status"
                                    class="form-select">

                                    <option value="1"
                                        {{ old('status',$client->status ?? 1)==1?'selected':'' }}>

                                        Active

                                    </option>

                                    <option value="0"
                                        {{ old('status',$client->status ?? 1)==0?'selected':'' }}>

                                        Inactive

                                    </option>

                                </select>

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">

                                    Address

                                </label>

                                <textarea name="address"
                                    rows="3"
                                    class="form-control">{{ old('address',$client->address ?? '') }}</textarea>

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">

                                    Logo

                                </label>

                                <input type="file"
                                    name="logo"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    onchange="previewImage(event)">

                            </div>

                            <div class="col-md-3">

                                <label class="form-label">

                                    Preview

                                </label>

                                <div>

                                    @if(isset($client) && $client->logo)

                                    <img id="preview"

                                        src="{{ asset('public/uploads/clients/'.$client->logo) }}"

                                        style="width:120px;height:120px;object-fit:cover;border-radius:10px;border:1px solid #ddd;">

                                    @else

                                    <img id="preview"

                                        src="https://placehold.co/120x120?text=Logo"

                                        style="width:120px;height:120px;object-fit:cover;border-radius:10px;border:1px solid #ddd;">

                                    @endif

                                </div>

                            </div>

                            <div class="col-md-3 text-end">

                                <button class="btn btn-primary px-5">

                                    <i class="bi bi-check-circle me-2"></i>

                                    {{ isset($client) ? 'Update Client' : 'Save Client' }}

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

        function previewImage(event) {

            const reader = new FileReader();

            reader.onload = function () {

                document.getElementById('preview').src = reader.result;

            };

            reader.readAsDataURL(event.target.files[0]);

        }

    </script>

</body>

</html>