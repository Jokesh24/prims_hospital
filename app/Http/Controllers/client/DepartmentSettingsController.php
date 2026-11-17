<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DepartmentSettingsController extends Controller
{
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

    public function index()
    {
        $this->authorizeAccess();
        $user = $this->getLoggedInUser();

        $departments = Department::where('client_id', $user->id)
            ->where('trash', 0)
            ->where('status', 1)
            ->orderBy('department_name', 'asc')
            ->get();

        return view('client.departmentSettings', compact('departments'));
    }

    public function update(Request $request, $id)
    {
        try {
            $this->authorizeAccess();
            $user = $this->getLoggedInUser();

            $department = Department::where('client_id', $user->id)->findOrFail($id);

            $department->update([
                'flexible_timing' => $request->boolean('flexible_timing') ? 1 : 0,
                'no_deductions' => $request->boolean('no_deductions') ? 1 : 0,
            ]);

            return redirect()
                ->route('departmentSettings')
                ->with('success', 'Department attendance/payroll settings updated for "' . $department->department_name . '".');
        } catch (\Throwable $e) {
            Log::error('Department Settings Update Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->with('error', 'Unable to update department settings.');
        }
    }
}