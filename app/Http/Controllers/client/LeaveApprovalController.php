<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeLeave;
use App\Models\LeaveFlowStep;
use App\Models\LeaveApprovalLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class LeaveApprovalController extends Controller
{
    // Utility to get logged in Employee's ID from session/guard depending on your auth setup
    private function getLoggedInEmployeeId()
    {
        // Adjust this depending on how you store employee session when they log in
        return session('LoggedEmployeeId');
    }

    public function pendingLeaves()
    {
        $empId = $this->getLoggedInEmployeeId();

        $leaves = EmployeeLeave::with(['employee', 'leaveType', 'dates'])
            ->where('current_approver_emp_id', $empId)
            ->whereIn('approval_status', [0, 1]) // Pending or In-Progress
            ->where('trash', 0)
            ->latest()
            ->paginate(15);

        return view('client.leaves.approvals', compact('leaves'));
    }

    public function actionLeave(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:forward,approve,reject',
            'remarks' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $empId = $this->getLoggedInEmployeeId();
            $leave = EmployeeLeave::where('current_approver_emp_id', $empId)
                ->whereIn('approval_status', [0, 1])
                ->findOrFail($id);

            $currentStep = LeaveFlowStep::where('flow_id', $leave->flow_id)
                ->where('step_order', $leave->current_step)
                ->first();

            // Log the action
            LeaveApprovalLog::create([
                'leave_id' => $leave->id,
                'approver_emp_id' => $empId,
                'action' => $request->action,
                'remarks' => $request->remarks
            ]);

            if ($request->action === 'reject') {
                $leave->update([
                    'approval_status' => 3, // Rejected
                    'current_approver_emp_id' => null
                ]);
            } elseif ($request->action === 'forward' || $request->action === 'approve') {
                if ($currentStep->is_final == 1) {
                    // It's the final step, it must be approved
                    $leave->update([
                        'approval_status' => 2, // Approved
                        'current_approver_emp_id' => null,
                        'status' => 1 // Mark as active/finalized
                    ]);
                } else {
                    // Forward to next step
                    $nextStep = LeaveFlowStep::where('flow_id', $leave->flow_id)
                        ->where('step_order', '>', $leave->current_step)
                        ->orderBy('step_order', 'asc')
                        ->first();

                    if ($nextStep) {
                        $leave->update([
                            'approval_status' => 1, // In-Progress
                            'current_step' => $nextStep->step_order,
                            'current_approver_emp_id' => $nextStep->approver_emp_id
                        ]);
                    } else {
                        // Failsafe if next step missing but not marked final
                        $leave->update([
                            'approval_status' => 2,
                            'current_approver_emp_id' => null
                        ]);
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Leave successfully processed.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Leave Action Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to process leave action.');
        }
    }
}