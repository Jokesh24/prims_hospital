<?php
namespace App\Http\Controllers\employee;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeavesController extends Controller
{
    public function apply_leave()
    {
        return view('employee.leaves.apply_leave');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'leave_date_from' => 'required|date',
            'to_leave_date' => 'required|date|after_or_equal:leave_date_from',
            'reason' => 'required',
            'documents' => 'nullable|file|mimes:jpg,png,pdf|max:2048'
        ]);

        $leave = new Leave();
        $leave->employee_id = Auth::id();
        $leave->client_id = Auth::user()->client_id; // Adjust based on your Auth structure
        $leave->subject = $request->subject;
        $leave->leave_date_from = $request->leave_date_from;
        $leave->to_leave_date = $request->to_leave_date;
        $leave->reason = $request->reason;

        // if ($request->hasFile('documents')) {
        //     $leave->documents = $request->file('documents')->store('leaves', 'public');
        // }

        // Use your custom upload method
        if ($request->hasFile('documents')) {
            $leave->documents = $this->uploadFile($request->file('documents'), 'uploads/leave_documents');
        }


        $leave->leave_status = Leave::STATUS_APPLIED;
        $leave->save();

        return redirect()->route('employee.leaves.manage')->with('success', 'Leave request submitted successfully.');
    }

    // Your existing helper method (placed at the bottom of the Controller)
    private function uploadFile($file, $destinationPath)
    {
        if ($file) {
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Ensure the directory exists
            if (!file_exists(public_path($destinationPath))) {
                mkdir(public_path($destinationPath), 0755, true);
            }

            $file->move(public_path($destinationPath), $fileName);
            return $destinationPath . '/' . $fileName;
        }
        return null;
    }

    public function manage()
    {
        $leaves = Leave::where('employee_id', Auth::id())->latest()->paginate(10);
        return view('employee.leaves.manage', compact('leaves'));
    }

    public function cancel(Request $request)
    {
        // Ensure the ID is being pulled from the hidden input field in the modal
        $request->validate(['id' => 'required', 'cancel_reason' => 'required|string|max:500']);

        $leave = Leave::where('id', $request->id)
            ->where('employee_id', Auth::id())
            ->firstOrFail();

        $leave->update([
            'leave_status' => Leave::STATUS_CANCELLED,
            'cancel_reason' => $request->cancel_reason
        ]);

        return back()->with('success', 'Leave request has been cancelled.');
    }

    // Show Edit Form
    public function edit($id)
    {
        $leave = Leave::where('id', $id)->where('employee_id', Auth::id())->firstOrFail();
        if ($leave->leave_status != Leave::STATUS_APPLIED) {
            return back()->with('error', 'Only pending leaves can be edited.');
        }
        return view('employee.leaves.edit_leave', compact('leave'));
    }

    // Update Leave
    public function update(Request $request, $id)
    {
        $leave = Leave::where('id', $id)->where('employee_id', Auth::id())->firstOrFail();
        $leave->update($request->only(['subject', 'leave_date_from', 'to_leave_date', 'reason']));
        return redirect()->route('employee.leaves.manage')->with('success', 'Leave updated successfully.');
    }
}