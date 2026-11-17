<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shift;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class ShiftController extends Controller
{
    /**
     * Display a listing of the shifts.
     */
    public function manage(Request $request)
    {
        try {
            $clientId = session('ClientUser');

            $query = Shift::where('client_id', $clientId)->where('trash', 0);

            if ($request->filled('search')) {
                $query->where('shift_name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $shifts = $query->orderBy('login_time', 'asc')->paginate(15)->appends($request->all());

            return view('client.shifts.manage', compact('shifts'));
        } catch (Exception $e) {
            Log::error('Error loading shifts: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong while loading shifts.');
        }
    }

    /**
     * Store a newly created shift.
     */
    public function store(Request $request)
    {
        $request->validate([
            'shift_name' => 'required|string|max:100',
            'login_time' => 'required',
            'logout_time' => 'required',
            'grace_time' => 'nullable|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        try {
            $clientId = session('ClientUser');

            Shift::create([
                'client_id' => $clientId,
                'shift_name' => $request->shift_name,
                'login_time' => Carbon::parse($request->login_time)->format('H:i:s'),
                'logout_time' => Carbon::parse($request->logout_time)->format('H:i:s'),
                'grace_time' => $request->grace_time ?? 0,
                'grace_time_for_logout' => $request->grace_time_for_logout ?? 0,
                'is_overnight' => $request->is_overnight ?? 0,
                'status' => $request->status,
                'trash' => 0,
                'created_by' => $clientId,
                'updated_by' => $clientId,
            ]);

            return redirect()->back()->with('success', 'Shift created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating shift: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to create shift. Please try again.');
        }
    }

    /**
     * Update the specified shift.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'shift_name' => 'required|string|max:100',
            'login_time' => 'required',
            'logout_time' => 'required',
            'grace_time' => 'nullable|integer|min:0',
            'status' => 'required|in:0,1',
        ]);

        try {
            $clientId = session('ClientUser');
            $shift = Shift::where('client_id', $clientId)->findOrFail($id);

            $shift->update([
                'shift_name' => $request->shift_name,
                'login_time' => Carbon::parse($request->login_time)->format('H:i:s'),
                'logout_time' => Carbon::parse($request->logout_time)->format('H:i:s'),
                'grace_time' => $request->grace_time ?? 0,
                'grace_time_for_logout' => $request->grace_time_for_logout ?? 0,
                'is_overnight' => $request->is_overnight ?? 0,
                'status' => $request->status,
                'updated_by' => $clientId,
            ]);

            return redirect()->back()->with('success', 'Shift updated successfully.');
        } catch (Exception $e) {
            Log::error('Error updating shift ID ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to update shift.');
        }
    }

    /**
     * Move the specified shift to trash.
     */
    public function destroy($id)
    {
        try {
            $clientId = session('ClientUser');
            $shift = Shift::where('client_id', $clientId)->findOrFail($id);

            $shift->update([
                'trash' => 1,
                'updated_by' => $clientId,
            ]);

            return redirect()->back()->with('success', 'Shift has been trashed.');
        } catch (Exception $e) {
            Log::error('Error trashing shift ID ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete shift.');
        }
    }
}