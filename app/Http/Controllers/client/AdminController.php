<?php

namespace App\Http\Controllers\client;

use App\Models\Client;
use App\Models\OfficeTiming;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Exception;

class AdminController extends Controller
{
    /*==============================================================
    AUTHORIZATION
    ==============================================================*/

    private function authorizeAccess()
    {
        if (!session()->has('ClientUser')) {
            abort(403, 'Unauthorized access.');
        }

        $client = Client::where('id', session('ClientUser'))
            ->where('status', 1)
            ->where('trash', 0)
            ->first();

        if (!$client) {
            session()->forget('ClientUser');
            abort(403, 'Unauthorized access.');
        }
    }

    private function getLoggedInUser()
    {
        return Client::where('id', session('ClientUser'))
            ->where('status', 1)
            ->where('trash', 0)
            ->first();
    }
    private function isSuperAdmin()
    {
        $client = $this->getLoggedInUser();

        return $client && ($client->id == $client->parent_id);
    }

    /*==============================================================
    MANAGE CLIENTS
    ==============================================================*/

    public function manageClients(Request $request)
    {
        $this->authorizeAccess();

        $user = $this->getLoggedInUser();

        if (!$this->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $query = Client::where('parent_id', $user->id)
            ->where('trash', 0);

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('client_name', 'like', "%{$search}%")
                    ->orWhere('client_email', 'like', "%{$search}%")
                    ->orWhere('client_mobile', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");

            });
        }

        $clients = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('client.manageClients', compact('clients'));
    }

    /*==============================================================
    CREATE CLIENT
    ==============================================================*/

    public function createClient()
    {
        $this->authorizeAccess();

        if (!$this->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        return view('client.createClient');
    }

    /*==============================================================
    STORE CLIENT
    ==============================================================*/

    public function storeClient(Request $request)
    {
        $this->authorizeAccess();

        $user = $this->getLoggedInUser();

        if (!$this->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'client_name' => 'required|max:255',
            'client_email' => 'required|email',
            'client_mobile' => 'required|digits:10',
            'client_password' => 'required|min:6',
            'client_gst' => 'nullable|max:30',
            'client_pan' => 'nullable|max:20',
            'city' => 'nullable|max:150',
            'address' => 'nullable',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $client = new Client();

        $client->parent_id = $user->id;
        $client->client_name = $request->client_name;
        $client->client_email = $request->client_email;
        $client->client_mobile = $request->client_mobile;
        $client->client_password = Hash::make($request->client_password);
        $client->client_gst = $request->client_gst;
        $client->client_pan = $request->client_pan;
        $client->city = $request->city;
        $client->address = $request->address;
        $client->status = $request->status ?? 1;
        $client->trash = 0;
        $client->created_by = $user->id;

        if ($request->hasFile('logo')) {

            $file = $request->file('logo');

            $filename = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path('uploads/clients'), $filename);

            $client->logo = $filename;
        }

        $client->save();
        OfficeTiming::create([
            'client_id' => $client->id,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'grace_time' => 15,
            'half_day_limit' => 4,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('manageClients')
            ->with('success', 'Client created successfully.');
    }

    /*==============================================================
    EDIT CLIENT
    ==============================================================*/

    public function editClient($id)
    {
        $this->authorizeAccess();

        $user = $this->getLoggedInUser();

        if (!$this->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $client = Client::where('id', $id)
            ->where('parent_id', $user->id)
            ->where('trash', 0)
            ->firstOrFail();

        return view('client.createClient', compact('client'));
    }

    /*==============================================================
    UPDATE CLIENT
    ==============================================================*/

    public function updateClient(Request $request, $id)
    {
        $this->authorizeAccess();

        if (!$this->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $user = $this->getLoggedInUser();

        $client = Client::where('id', $id)
            ->where('parent_id', $user->id)
            ->where('trash', 0)
            ->firstOrFail();

        $request->validate([
            'client_name' => 'required|max:255',
            'client_email' => 'required|email',
            'client_mobile' => 'required|digits:10',
            'client_gst' => 'nullable|max:30',
            'client_pan' => 'nullable|max:20',
            'city' => 'nullable|max:150',
            'address' => 'nullable',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $client->client_name = $request->client_name;
        $client->client_email = $request->client_email;
        $client->client_mobile = $request->client_mobile;
        $client->client_gst = $request->client_gst;
        $client->client_pan = $request->client_pan;
        $client->city = $request->city;
        $client->address = $request->address;
        $client->status = $request->status ?? 1;
        $client->updated_by = $user->id;

        if ($request->filled('client_password')) {
            $client->client_password = Hash::make($request->client_password);
        }

        if ($request->hasFile('logo')) {

            if ($client->logo && file_exists(public_path('uploads/clients/' . $client->logo))) {
                unlink(public_path('uploads/clients/' . $client->logo));
            }

            $file = $request->file('logo');

            $filename = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path('uploads/clients'), $filename);

            $client->logo = $filename;
        }

        $client->save();

        return redirect()
            ->route('manageClients')
            ->with('success', 'Client updated successfully.');
    }

    /*==============================================================
    DELETE CLIENT
    ==============================================================*/

    public function deleteClient($id)
    {
        $this->authorizeAccess();

        if (!$this->isSuperAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $user = $this->getLoggedInUser();

        $client = Client::where('id', $id)
            ->where('parent_id', $user->id)
            ->where('trash', 0)
            ->firstOrFail();

        $client->trash = 1;
        $client->updated_by = $user->id;

        $client->save();

        return redirect()
            ->route('manageClients')
            ->with('success', 'Client deleted successfully.');
    }

    
    public function showChangePassword()
    {
        return view('client.changepassword');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
                'confirmed',
                'different:current_password',
            ],
        ], [
            'new_password.regex' => 'Your new password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'new_password.different' => 'Your new password must be different from your current password.',
        ]);

        try {

            $client = Auth::guard('client')->user();

            if (!Hash::check($request->current_password, $client->client_password)) {

                return back()->withErrors([
                    'current_password' => 'The provided password does not match your current password.'
                ]);

            }

            $client->client_password = Hash::make($request->new_password);
            $client->save();

            // Optional: Log password change
            /*
            ClientLoginLog::create([
                'client_id'      => $client->id,
                'ip'             => $request->ip(),
                'login_datetime' => Carbon::now(),
                'status'         => 1,
                'log_comment'    => 'Password changed successfully for client ID: ' . $client->id,
            ]);
            */

            return redirect()
                ->route('client.dashboard')
                ->with('success', 'Your password has been successfully updated.');

        } catch (Exception $e) {

            return back()->withErrors([
                'error' => 'An unexpected error occurred while updating your password. Please try again.'
            ]);

        }

    }
}