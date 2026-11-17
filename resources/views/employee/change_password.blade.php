<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Settings | {{ env('APP_NAME') }}</title>
    @include('employee.includes.header_links')
    <style>
        /* Core Brand Theming */
        :root {
            --brand-primary: #5642b2;
            --brand-primary-dark: #43338c;
            /* Slightly darker for gradient depth */
            --brand-primary-light: #ece9f7;
            /* Very light tint for icon backgrounds */
            --brand-primary-shadow: rgba(86, 66, 178, 0.25);
        }

        .content-wrapper {
            background-color: #f8fafc;
            min-height: calc(100vh - 70px);
        }

        .security-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border: none;
            max-width: 650px;
            margin: 0 auto;
            overflow: hidden;
        }

        /* Brand Gradient Header */
        .security-header {
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-dark) 100%);
            padding: 35px 40px;
            color: #ffffff;
            position: relative;
        }

        .security-header::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -20px;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .security-icon-wrap {
            width: 55px;
            height: 55px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #ffffff;
            position: relative;
            z-index: 1;
        }

        .security-body {
            padding: 40px;
        }

        /* Smart Input Groups with Brand Focus */
        .custom-input-group .input-group-text {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #64748b;
            transition: all 0.3s ease;
        }

        .custom-input-group .form-control {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: none;
            border-right: none;
            box-shadow: none;
            transition: all 0.3s ease;
        }

        .custom-input-group .password-toggle {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            color: #64748b;
            transition: all 0.3s ease;
        }

        .custom-input-group .password-toggle:hover {
            color: var(--brand-primary);
        }

        .custom-input-group:focus-within .input-group-text,
        .custom-input-group:focus-within .form-control,
        .custom-input-group:focus-within .password-toggle {
            background: #ffffff;
            border-color: var(--brand-primary);
        }

        .strength-meter {
            height: 6px;
            border-radius: 10px;
            background-color: #e2e8f0;
            overflow: hidden;
            margin-top: 8px;
        }

        .strength-meter-bar {
            height: 100%;
            width: 0;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.4s ease;
        }

        .req-list {
            list-style: none;
            padding-left: 0;
            margin-top: 15px;
            font-size: 0.82rem;
            color: #64748b;
        }

        .req-list li {
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .req-met {
            color: #10b981;
        }

        .req-unmet {
            color: #cbd5e1;
        }

        /* Brand Themed Button */
        .btn-theme-primary {
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-primary-dark) 100%);
            border: none;
            color: #fff;
            transition: all 0.3s ease;
        }

        .btn-theme-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px var(--brand-primary-shadow);
            color: #fff;
        }

        .brand-icon-bg {
            background-color: var(--brand-primary-light);
            color: var(--brand-primary);
        }
    </style>
</head>

<body>

    @include('employee.includes.sidebar')

    <div id="main">

        @include('employee.includes.header')

        <div class="content content-wrapper">
            <div class="container-fluid py-5">

                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-dismissible fade show floating-alert d-flex align-items-center" role="alert"
                        style="position: fixed; top: 24px; right: 24px; z-index: 9999; min-width: 320px;
                                   background-color: #ecfdf5; border: 1px solid #a7f3d0; border-left: 5px solid #059669; 
                                   box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15); color: #064e3b; font-weight: 500; border-radius: 10px; padding: 16px 20px;">
                        <i class="bi bi-check-circle-fill fs-5 me-3" style="color: #059669;"></i>
                        <div class="flex-grow-1">{{ session('success') }}</div>
                        <button type="button" class="btn-close position-static p-0 ms-3" data-bs-dismiss="alert"
                            style="opacity: 0.7;"></button>
                    </div>
                @endif

                @if(session('error') || $errors->has('error'))
                    <div class="alert alert-dismissible fade show floating-alert d-flex align-items-center" role="alert"
                        style="position: fixed; top: 24px; right: 24px; z-index: 9999; min-width: 320px;
                                   background-color: #fef2f2; border: 1px solid #fecaca; border-left: 5px solid #dc2626; 
                                   box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15); color: #7f1d1d; font-weight: 500; border-radius: 10px; padding: 16px 20px;">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-3" style="color: #dc2626;"></i>
                        <div class="flex-grow-1">{{ session('error') ?? $errors->first('error') }}</div>
                        <button type="button" class="btn-close position-static p-0 ms-3" data-bs-dismiss="alert"
                            style="opacity: 0.7;"></button>
                    </div>
                @endif

                <!-- Security Card -->
                <div class="security-card">
                    <!-- Themed Header -->
                    <div class="security-header d-flex align-items-center gap-4">
                        <div class="security-icon-wrap">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div style="position: relative; z-index: 1;">
                            <h4 class="fw-bold mb-1">Account Security</h4>
                            <span class="text-white-50 small" style="letter-spacing: 0.3px;">Manage and update your
                                secure password</span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="security-body">
                        <form action="{{ route('employee.update_password') }}" method="POST">
                            @csrf

                            <!-- Current Password -->
                            <div class="mb-4 pb-2">
                                <label class="form-label small fw-bold text-dark">Current Password <span
                                        class="text-danger">*</span></label>
                                <div class="input-group custom-input-group">
                                    <span class="input-group-text border-end-0"><i class="bi bi-key-fill"></i></span>
                                    <input type="password" name="current_password" id="current_password"
                                        class="form-control @error('current_password') is-invalid @enderror" required
                                        placeholder="Enter your current password">
                                    <span class="input-group-text password-toggle border-start-0"
                                        onclick="togglePassword('current_password', this)">
                                        <i class="bi bi-eye-slash"></i>
                                    </span>
                                </div>
                                @error('current_password')
                                    <div class="text-danger small mt-2 fw-medium"><i
                                            class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="position-relative mb-4">
                                <hr style="border-color: #e2e8f0;">
                                <span
                                    class="position-absolute top-50 start-50 translate-middle bg-white px-3 small text-muted fw-bold rounded-pill border"
                                    style="border-color: #e2e8f0;">NEW CREDENTIALS</span>
                            </div>

                            <!-- New Password -->
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-dark">New Password <span
                                        class="text-danger">*</span></label>
                                <div class="input-group custom-input-group mb-2">
                                    <span class="input-group-text border-end-0"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" name="new_password" id="new_password"
                                        class="form-control @error('new_password') is-invalid @enderror" required
                                        placeholder="Create a strong new password">
                                    <span class="input-group-text password-toggle border-start-0"
                                        onclick="togglePassword('new_password', this)">
                                        <i class="bi bi-eye-slash"></i>
                                    </span>
                                </div>
                                @error('new_password')
                                    <div class="text-danger small mt-2 fw-medium"><i
                                            class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror

                                <!-- Strength Meter -->
                                <div class="bg-light rounded p-3 mt-3 border" style="border-color: #f1f5f9;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small fw-bold text-muted text-uppercase"
                                            style="font-size: 0.7rem; letter-spacing: 0.5px;">Password Strength</span>
                                        <span class="small fw-bold" id="strength-text"
                                            style="font-size: 0.75rem;">Waiting...</span>
                                    </div>
                                    <div class="strength-meter">
                                        <div class="strength-meter-bar" id="strength-bar"></div>
                                    </div>

                                    <!-- Dynamic Requirements List -->
                                    <ul class="req-list mb-0">
                                        <li id="req-length"><i class="bi bi-dash-circle req-unmet"></i> 8+ characters
                                            long</li>
                                        <li id="req-upper"><i class="bi bi-dash-circle req-unmet"></i> One uppercase
                                            letter</li>
                                        <li id="req-number"><i class="bi bi-dash-circle req-unmet"></i> One number</li>
                                        <li id="req-special"><i class="bi bi-dash-circle req-unmet"></i> One special
                                            character (@$!%*#?&)</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-5">
                                <label class="form-label small fw-bold text-dark">Confirm New Password <span
                                        class="text-danger">*</span></label>
                                <div class="input-group custom-input-group">
                                    <span class="input-group-text border-end-0"><i
                                            class="bi bi-check-circle-fill"></i></span>
                                    <input type="password" name="new_password_confirmation"
                                        id="new_password_confirmation" class="form-control" required
                                        placeholder="Retype your new password">
                                    <span class="input-group-text password-toggle border-start-0"
                                        onclick="togglePassword('new_password_confirmation', this)">
                                        <i class="bi bi-eye-slash"></i>
                                    </span>
                                </div>
                                <div id="match-text" class="small mt-2 fw-medium"
                                    style="display: none; padding-left: 5px;"></div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-2 pt-4 border-top">
                                <a href="{{ route('employee.dashboard') }}"
                                    class="btn btn-light border px-4 fw-medium text-muted">Cancel</a>
                                <button type="submit" class="btn btn-theme-primary px-4 fw-bold" id="submitBtn">
                                    <i class="bi bi-save2 me-2"></i>Update Password
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>

        @include('employee.includes.footer_links')

        <script>
            // Toggle Password Visibility
            function togglePassword(inputId, iconElement) {
                const input = document.getElementById(inputId);
                const icon = iconElement.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            }

            // Password Strength and Requirement Validation Logic
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('new_password_confirmation');
            const strengthBar = document.getElementById('strength-bar');
            const strengthText = document.getElementById('strength-text');
            const matchText = document.getElementById('match-text');

            // Requirement Elements
            const reqLength = document.getElementById('req-length').querySelector('i');
            const reqUpper = document.getElementById('req-upper').querySelector('i');
            const reqNumber = document.getElementById('req-number').querySelector('i');
            const reqSpecial = document.getElementById('req-special').querySelector('i');

            function updateRequirementIcon(element, isValid) {
                if (isValid) {
                    element.classList.remove('req-unmet', 'bi-dash-circle');
                    element.classList.add('req-met', 'bi-check-circle-fill');
                } else {
                    element.classList.remove('req-met', 'bi-check-circle-fill');
                    element.classList.add('req-unmet', 'bi-dash-circle');
                }
            }

            newPassword.addEventListener('input', function () {
                const val = this.value;
                let strength = 0;

                // Validate specific requirements
                const hasLength = val.length >= 8;
                const hasUpper = /[A-Z]/.test(val);
                const hasNumber = /[0-9]/.test(val);
                const hasSpecial = /[@$!%*#?&]/.test(val);

                updateRequirementIcon(reqLength, hasLength);
                updateRequirementIcon(reqUpper, hasUpper);
                updateRequirementIcon(reqNumber, hasNumber);
                updateRequirementIcon(reqSpecial, hasSpecial);

                // Calculate total strength percentage
                if (hasLength) strength += 25;
                if (hasUpper) strength += 25;
                if (hasNumber) strength += 25;
                if (hasSpecial) strength += 25;

                // Update Progress Bar UI
                strengthBar.style.width = strength + '%';

                if (val.length === 0) {
                    strengthBar.style.backgroundColor = 'transparent';
                    strengthText.innerText = 'Waiting...';
                    strengthText.style.color = '#94a3b8';
                } else if (strength <= 25) {
                    strengthBar.style.backgroundColor = '#ef4444'; // Red
                    strengthText.innerText = 'Weak';
                    strengthText.style.color = '#ef4444';
                } else if (strength <= 50) {
                    strengthBar.style.backgroundColor = '#f59e0b'; // Amber
                    strengthText.innerText = 'Fair';
                    strengthText.style.color = '#f59e0b';
                } else if (strength <= 75) {
                    strengthBar.style.backgroundColor = '#3b82f6'; // Blue
                    strengthText.innerText = 'Good';
                    strengthText.style.color = '#3b82f6';
                } else {
                    strengthBar.style.backgroundColor = '#10b981'; // Green
                    strengthText.innerText = 'Strong';
                    strengthText.style.color = '#10b981';
                }

                checkMatch();
            });

            confirmPassword.addEventListener('input', checkMatch);

            function checkMatch() {
                if (confirmPassword.value.length > 0) {
                    matchText.style.display = 'block';
                    if (newPassword.value === confirmPassword.value) {
                        matchText.innerHTML = '<i class="bi bi-shield-check me-1"></i> Passwords match';
                        matchText.className = 'small mt-2 fw-bold text-success';
                    } else {
                        matchText.innerHTML = '<i class="bi bi-shield-exclamation me-1"></i> Passwords do not match';
                        matchText.className = 'small mt-2 fw-bold text-danger';
                    }
                } else {
                    matchText.style.display = 'none';
                }
            }

            // Dismiss floating alerts automatically
            document.addEventListener('DOMContentLoaded', function () {
                let alerts = document.querySelectorAll('.floating-alert');
                if (alerts.length > 0) {
                    setTimeout(function () {
                        alerts.forEach(function (alertNode) {
                            if (typeof bootstrap !== 'undefined') {
                                let bsAlert = new bootstrap.Alert(alertNode);
                                bsAlert.close();
                            } else {
                                alertNode.style.display = 'none';
                            }
                        });
                    }, 5000);
                }
            });
        </script>
    </div>
</body>

</html>