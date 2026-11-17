<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveFlow;
use App\Models\LeaveFlowStep;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLoginAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class LeaveFlowController extends Controller
{
    private function getClientId()
    {
        return session('ClientUser');
    }

    public function index()
    {
        $clientId = $this->getClientId();
        $flows = LeaveFlow::with(['department', 'steps.approver'])
            ->where('client_id', $clientId)
            ->where('trash', 0)
            ->latest()
            ->paginate(15);

        return view('client.leave_flows.manage', compact('flows'));
    }

    public function create()
    {
        $clientId = $this->getClientId();
        $departments = Department::where('client_id', $clientId)->where('status', 1)->where('trash', 0)->get();

        // Fetch Employees who have Login Access 
        $eligibleApprovers = Employee::where('client_id', $clientId)
            ->where('status', 1)
            ->where('trash', 0)
            ->whereHas('loginAccess', function ($q) use ($clientId) {
                $q->where('client_id', $clientId)->where('status', 1)->where('trash', 0);
            })->get();

        return view('client.leave_flows.create', compact('departments', 'eligibleApprovers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'flow_name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:rl_departments_tbl,id',
            'approvers' => 'required|array|min:1',
            'approvers.*' => 'required|exists:rl_employees_tbl,id',
            'status' => 'required|in:0,1'
        ]);

        DB::beginTransaction();
        try {
            $clientId = $this->getClientId();

            // Check if department already has a flow
            $exists = LeaveFlow::where('client_id', $clientId)
                ->where('department_id', $request->department_id)
                ->where('trash', 0)
                ->exists();

            if ($exists && $request->department_id != null) {
                return back()->withInput()->with('error', 'A leave flow for this department already exists.');
            }

            $flow = LeaveFlow::create([
                'client_id' => $clientId,
                'department_id' => $request->department_id,
                'flow_name' => $request->flow_name,
                'status' => $request->status,
                'trash' => 0,
                'created_by' => $clientId
            ]);

            $totalApprovers = count($request->approvers);
            foreach ($request->approvers as $index => $approverId) {
                LeaveFlowStep::create([
                    'flow_id' => $flow->id,
                    'approver_emp_id' => $approverId,
                    'step_order' => $index + 1,
                    'is_final' => ($index === $totalApprovers - 1) ? 1 : 0
                ]);
            }

            DB::commit();
            return redirect()->route('client.leave_flows.index')->with('success', 'Leave Flow configured successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Store Leave Flow Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to save Leave Flow.');
        }
    }

    public function destroy($id)
    {
        try {
            $clientId = $this->getClientId();
            LeaveFlow::where('client_id', $clientId)->where('id', $id)->update(['trash' => 1]);
            return back()->with('success', 'Leave flow removed successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to remove leave flow.');
        }
    }
}