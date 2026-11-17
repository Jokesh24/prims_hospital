<?php

namespace App\Http\Controllers\employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\EmployeeLoginLog;
use Illuminate\Support\Facades\Hash; // Ensure this is imported
use App\Models\Employee;
use Carbon\Carbon;
use Exception;

class LoginController extends Controller
{
    /**
     * Show the login view.
     */
    public function login(Request $request)
    {
        // If already logged in, redirect to dashboard
        if (Auth::guard('employee')->check()) {
            return redirect()->route('employee.dashboard'); // Update with your dashboard route
        }

        return view('employee.login_employee');
    }

    /**
     * Handle the authentication attempt.
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'employee_email' => 'required|email',
            'employee_password' => 'required|string|min:6',
        ]);

        try {
            // Define the login credentials using the correct database column name: 'email'
            $credentials = [
                'email' => $request->employee_email, // Changed from 'employee_email' to 'email'
                'password' => $request->employee_password,
                'status' => 1,
                'trash' => 0,
            ];

            $remember = $request->has('remember');

            if (Auth::guard('employee')->attempt($credentials, $remember)) {
                $request->session()->regenerate();

                $employeeId = Auth::guard('employee')->id();
                $request->session()->put('EmployeeUser', $employeeId);

                EmployeeLoginLog::create([
                    'employee_id' => $employeeId,
                    'ip' => $request->ip(),
                    'login_datetime' => Carbon::now(),
                    'status' => 1,
                    'log_comment' => 'Login successful for employee ID: ' . $employeeId,
                ]);

                $employeeName = Auth::guard('employee')->user()->employee_name;
                return redirect()->intended(route('employee.dashboard'))->with('success', 'Welcome ' . $employeeName . '!');
            }

            // --- LOG FAILED ATTEMPT ---
            // Use 'email' here as well to match your database structure
            $employee = Employee::where('email', $request->employee_email)->first();

            EmployeeLoginLog::create([
                'employee_id' => $employee ? $employee->id : null,
                'ip' => $request->ip(),
                'login_datetime' => Carbon::now(),
                'status' => 0,
                'log_comment' => 'Invalid credentials or inactive account' . ($employee ? ' for employee ID: ' . $employee->id : ' with non-existent email'),
            ]);

            return back()->withErrors([
                'error' => 'The provided credentials do not match our records or your account is inactive.',
            ])->onlyInput('employee_email');

        } catch (Exception $e) {
            Log::error('Employee Login Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred. Please try again.']);
        }
    }

    /**
     * Show the change password view.
     */
    public function showChangePasswordForm()
    {
        return view('employee.change_password');
    }

    /**
     * Securely update the employee's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:8',             // Must be at least 8 characters
                'regex:/[a-z]/',     // Must contain at least one lowercase letter
                'regex:/[A-Z]/',     // Must contain at least one uppercase letter
                'regex:/[0-9]/',     // Must contain at least one digit
                'regex:/[@$!%*#?&]/',// Must contain a special character
                'confirmed',         // Must match new_password_confirmation
                'different:current_password' // Cannot be the same as the old password
            ],
        ], [
            'new_password.regex' => 'Your new password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'new_password.different' => 'Your new password must be different from your current password.',
        ]);

        try {
            /** @var \App\Models\Employee $employee */
            $employee = Auth::guard('employee')->user();

            // Verify the current password matches the database hash
            if (!Hash::check($request->current_password, $employee->password)) {
                return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
            }

            // Hash and save the new password
            $employee->password = Hash::make($request->new_password);
            $employee->save();

            // Log the password change event for security auditing
            EmployeeLoginLog::create([
                'employee_id' => $employee->id,
                'ip' => $request->ip(),
                'login_datetime' => Carbon::now(),
                'status' => 1,
                'log_comment' => 'Password changed successfully for employee ID: ' . $employee->id,
            ]);

            return redirect()->route('employee.dashboard')->with('success', 'Your password has been successfully updated.');

        } catch (Exception $e) {
            Log::error('Employee Password Update Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred while updating your password. Please try again.']);
        }
    }

    /**
     * Log the employee out.
     */
    public function logout(Request $request)
    {
        Auth::guard('employee')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('employee.login');
    }
}