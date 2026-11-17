<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LeavesController extends Controller
{
    public function manage(Request $request)
    {
        $clientSession = Session::get('ClientUser');

        // If it's an object, get the ID. If it's already an int, use it directly.
        $clientId = is_object($clientSession) ? $clientSession->id : $clientSession;

        // Safety check: if no ID found, redirect or abort
        if (!$clientId) {
            return redirect()->back()->with('error', 'Session expired or invalid user.');
        }

        $query = Leave::where('client_id', $clientId);

        // Search and Filter logic
        if ($request->search) {
            $query->where('subject', 'like', '%' . $request->search . '%');
        }
        if ($request->status) {
            $query->where('leave_status', $request->status);
        }

        // $leaves = $query->latest()->paginate(10);
        // In LeavesController.php manage method
        $leaves = Leave::with('employee') // Added eager loading here
            ->where('client_id', $clientId)
            ->latest()
            ->paginate(10);
        return view('client.leaves.manage', compact('leaves'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate(['id' => 'required', 'status' => 'required']);

        // FETCH THE SESSION VALUE ONCE
        $clientSession = Session::get('ClientUser');

        // DETERMINE ID: If it's an object, get ->id. If it's an int, use it directly.
        $clientId = is_object($clientSession) ? $clientSession->id : $clientSession;

        // USE THE DETERMINED $clientId
        $leave = Leave::where('id', $request->id)
            ->where('client_id', $clientId)
            ->firstOrFail();

        // If rejecting, require reason
        if ($request->status == Leave::STATUS_REJECTED) {
            $request->validate(['cancel_reason' => 'required']);
            $leave->cancel_reason = $request->cancel_reason;
        }

        $leave->leave_status = $request->status;
        $leave->save();

        return back()->with('success', 'Leave status updated successfully.');
    }

    // Method 1: Get Applied or In-Progress leaves
    public function getActiveLeaves(Request $request)
    {
        $clientId = is_object(Session::get('ClientUser')) ? Session::get('ClientUser')->id : Session::get('ClientUser');

        $query = Leave::where('client_id', $clientId);

        // Search and Filter logic
        if ($request->search) {
            $query->where('subject', 'like', '%' . $request->search . '%');
        }
        if ($request->status) {
            $query->where('leave_status', $request->status);
        }


        $leaves = Leave::with('employee')
            ->where('client_id', $clientId)
            ->appliedOrPending() // Using the scope
            ->latest()
            ->paginate(10);

        return view('client.leaves.pending_leaves', compact('leaves'));
    }

    // Method 2: Get Cancelled or Rejected leaves
    public function getHistoryLeaves(Request $request)
    {
        $clientId = is_object(Session::get('ClientUser')) ? Session::get('ClientUser')->id : Session::get('ClientUser');

        $query = Leave::where('client_id', $clientId);

        // Search and Filter logic
        if ($request->search) {
            $query->where('subject', 'like', '%' . $request->search . '%');
        }
        if ($request->status) {
            $query->where('leave_status', $request->status);
        }

        $leaves = Leave::with('employee')
            ->where('client_id', $clientId)
            ->cancelledOrRejected() // Using the scope
            ->latest()
            ->paginate(10);

        return view('client.leaves.cancelled_rejected_leaves', compact('leaves'));
    }
}