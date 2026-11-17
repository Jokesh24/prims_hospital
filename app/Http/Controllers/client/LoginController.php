<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ClientLoginLog;
use App\Models\Client;
use App\Models\Employee;
use App\Models\EmployeeLoginAccess;
use Carbon\Carbon;
use Exception;

class LoginController extends Controller
{
    /**
     * Show the login view.
     */
    public function login(Request $request)
    {
        // Prevent redirect loops by verifying session integrity
        if (Auth::guard('client')->check() && session()->has('ClientUser')) {
            return redirect()->intended(route('client.dashboard'));
        }

        if (Auth::guard('employee')->check() && session()->has('EmployeeUser')) {
            return redirect()->intended(route('client.dashboard'));
        }

        // Clean slate: if they hit the login page but sessions are corrupt, log them out fully
        Auth::guard('client')->logout();
        Auth::guard('employee')->logout();

        return view('client.login_client');
    }

    /**
     * Handle the authentication attempt (Dual Login for Client & Employee).
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        try {
            $remember = $request->has('remember');
            $ipAddress = $request->ip();

            /*
             |--------------------------------------------------------------------------
             | 1. ATTEMPT CLIENT LOGIN
             |--------------------------------------------------------------------------
             */
            $clientCredentials = [
                'client_email' => $request->email,
                'password' => $request->password, // Mapped to client_password in Auth Provider
                'status' => 1,
                'trash' => 0,
            ];

            if (Auth::guard('client')->attempt($clientCredentials, $remember)) {
                $request->session()->regenerate();

                $client = Auth::guard('client')->user();
                $request->session()->put('ClientUser', $client->id);
                $request->session()->put('ClientName', $client->client_name);
                $request->session()->put('LoginType', 'Client');

                ClientLoginLog::create([
                    'client_id' => $client->id,
                    'ip' => $ipAddress,
                    'login_datetime' => Carbon::now(),
                    'status' => 1,
                    'log_comment' => 'Client Login successful for ID: ' . $client->id,
                ]);

                return redirect()->intended(route('client.dashboard'))->with('success', 'Welcome ' . $client->client_name . '!');
            }

            /*
             |--------------------------------------------------------------------------
             | 2. ATTEMPT EMPLOYEE LOGIN
             |--------------------------------------------------------------------------
             */
            $employeeCredentials = [
                'email' => $request->email,
                'password' => $request->password,
                'status' => 1,
                'trash' => 0,
            ];

            if (Auth::guard('employee')->attempt($employeeCredentials, $remember)) {
                $employee = Auth::guard('employee')->user();

                // Verify Employee has Login Access Granted
                $access = EmployeeLoginAccess::where('employee_id', $employee->id)
                    ->where('status', 1)
                    ->where('trash', 0)
                    ->first();

                if (!$access) {
                    $clientId = $employee->client_id;
                    Auth::guard('employee')->logout();

                    if ($clientId) {
                        ClientLoginLog::create([
                            'client_id' => $clientId,
                            'ip' => $ipAddress,
                            'login_datetime' => Carbon::now(),
                            'status' => 0,
                            'log_comment' => 'Failed: Employee ID ' . $employee->id . ' attempted login without access permissions.',
                        ]);
                    }

                    return back()->withErrors([
                        'error' => 'Your account does not have dashboard access. Please contact HR.',
                    ])->onlyInput('email');
                }

                $request->session()->regenerate();

                // Set session variables so the rest of the application knows which client data to load
                $request->session()->put('ClientUser', $employee->client_id);
                $request->session()->put('ClientName', $employee->client->client_name ?? 'HRMS');
                $request->session()->put('EmployeeUser', $employee->id);
                $request->session()->put('LoginType', 'Employee');

                ClientLoginLog::create([
                    'client_id' => $employee->client_id,
                    'ip' => $ipAddress,
                    'login_datetime' => Carbon::now(),
                    'status' => 1,
                    'log_comment' => 'Employee Login successful for ID: ' . $employee->id,
                ]);

                return redirect()->intended(route('client.dashboard'))->with('success', 'Welcome back, ' . $employee->employee_name . '!');
            }

            /*
             |--------------------------------------------------------------------------
             | 3. LOGIN FAILED FOR BOTH
             |--------------------------------------------------------------------------
             */
            $clientCheck = Client::where('client_email', $request->email)->first();

            ClientLoginLog::create([
                'client_id' => $clientCheck ? $clientCheck->id : null,
                'ip' => $ipAddress,
                'login_datetime' => Carbon::now(),
                'status' => 0,
                'log_comment' => 'Invalid credentials or inactive account: ' . $request->email,
            ]);

            return back()->withErrors([
                'error' => 'The provided credentials do not match our records or your account is inactive.',
            ])->onlyInput('email');

        } catch (Exception $e) {
            Log::error('Login Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An unexpected error occurred. Please try again.']);
        }
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        if (Auth::guard('employee')->check()) {
            Auth::guard('employee')->logout();
        }

        if (Auth::guard('client')->check()) {
            Auth::guard('client')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('client.login');
    }
}