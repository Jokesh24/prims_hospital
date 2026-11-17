<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Holiday;
use App\Models\Department;
use App\Models\PaidLeave;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class HolidayController extends Controller
{
    /* Display a listing of the holidays for the logged-in client */
    public function manage(Request $request)
    {
        try {
            $clientId = Auth::guard('client')->id() ?: session('ClientUser');
            $query = Holiday::with('departments')
                ->where('client_id', $clientId)
                ->where('trash', 0);

            if ($request->filled('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('type') && $request->type !== 'All Types') {
                $query->where('type', $request->type);
            }

            if ($request->filled('month') && $request->month !== 'All Months') {
                $monthNumber = Carbon::parse($request->month)->month;
                $query->whereMonth('date', $monthNumber);
            }

            /* Fetch paid leave settings for the view */
            $leaveSettings = PaidLeave::where('client_id', $clientId)
                ->where('trash', 0)
                ->first();

            /* Fetch departments for this client */
            $departments = Department::where('client_id', $clientId)
                ->where('trash', 0)
                ->where('status', 1)
                ->orderBy('department_name', 'asc')
                ->get();

            $holidays = $query->orderBy('date', 'asc')->paginate(10)->appends($request->all());

            return view('client.holidays', compact('holidays', 'leaveSettings', 'departments'));
        } catch (Exception $e) {
            Log::error('Error loading holidays: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong while loading holidays.');
        }
    }

    /* Store a newly created holiday */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'day' => 'required|string|max:20',
            'type' => 'required|integer|in:1,2,3,4',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'integer|exists:rl_departments_tbl,id'
        ]);

        try {
            $clientId = Auth::guard('client')->id() ?: session('ClientUser');

            $holiday = Holiday::create([
                'client_id' => $clientId,
                'title' => $request->title,
                'date' => $request->date,
                'day' => $request->day,
                'type' => $request->type,
                'description' => $request->description,
                'status' => $request->status,
                'trash' => 0
            ]);

            if ($request->has('department_ids')) {
                $holiday->departments()->sync($request->department_ids);
            }

            return redirect()->back()->with('success', 'Holiday added successfully.');
        } catch (Exception $e) {
            Log::error('Error adding holiday: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to add holiday.');
        }
    }

    /* Update the specified holiday */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'day' => 'required|string|max:20',
            'type' => 'required|integer|in:1,2,3,4',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'integer|exists:rl_departments_tbl,id'
        ]);

        try {
            $clientId = Auth::guard('client')->id() ?: session('ClientUser');
            $holiday = Holiday::where('client_id', $clientId)->findOrFail($id);

            $holiday->update([
                'title' => $request->title,
                'date' => $request->date,
                'day' => $request->day,
                'type' => $request->type,
                'description' => $request->description,
                'status' => $request->status
            ]);

            $holiday->departments()->sync($request->department_ids ?? []);

            return redirect()->back()->with('success', 'Holiday updated successfully.');
        } catch (Exception $e) {
            Log::error('Error updating holiday: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update holiday.');
        }
    }

    /* Soft delete the specified holiday */
    public function destroy($id)
    {
        try {
            $clientId = Auth::guard('client')->id() ?: session('ClientUser');
            $holiday = Holiday::where('client_id', $clientId)->findOrFail($id);
            $holiday->update(['trash' => 1]);

            return redirect()->back()->with('success', 'Holiday deleted successfully.');
        } catch (Exception $e) {
            Log::error('Error deleting holiday: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete holiday.');
        }
    }
}