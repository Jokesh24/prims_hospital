<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveDate;
use App\Models\LeaveType;
use App\Models\PaidLeave;
use App\Models\Employee;
use App\Models\LeaveStatusLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class LeaveController extends Controller
{
    private function getClientId()
    {
        return Auth::guard('client')->id() ?: session('ClientUser');
    }

    public function index(Request $request)
    {
        try {
            $clientId = $this->getClientId();

            $query = EmployeeLeave::with(['employee', 'leaveType', 'dates', 'latestLog'])
                ->where('client_id', $clientId)
                ->where('trash', 0);

            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->filled('leave_type_id')) {
                $query->where('leave_type_id', $request->leave_type_id);
            }

            $leaves = $query->orderBy('id', 'desc')->paginate(10)->appends($request->all());

            $employees = Employee::where('client_id', $clientId)
                ->where('trash', 0)
                ->where('status', 1)
                ->orderBy('employee_name', 'asc')
                ->get();

            $leaveTypes = LeaveType::where('client_id', $clientId)
                ->where('trash', 0)
                ->where('status', 1)
                ->orderBy('name', 'asc')
                ->get();

            $leaveSettings = PaidLeave::where('client_id', $clientId)
                ->where('trash', 0)
                ->first();

            return view('client.leaves', compact('leaves', 'employees', 'leaveTypes', 'leaveSettings'));
        } catch (Exception $e) {
            Log::error('Error loading leaves: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong while loading leaves.');
        }
    }

    public function getPaidLeavesLeftjok(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'date' => 'nullable|string',
            'ignore_leave_id' => 'nullable|integer'
        ]);

        $clientId = $this->getClientId();

        try {
            $date = $request->filled('date')
                ? Carbon::createFromFormat('d-m-Y', $request->date)
                : Carbon::now();
        } catch (Exception $e) {
            $date = Carbon::now();
        }

        $month = $date->month;
        $year = $date->year;

        // Fetch employee with their department leave policy
        $employee = Employee::where('client_id', $clientId)->findOrFail($request->employee_id);

        $leaveSettings = PaidLeave::where('client_id', $clientId)
            ->where('department_id', $employee->department_id)
            ->where('trash', 0)
            ->where('status', 1)
            ->first();

        $monthlyAllowance = $leaveSettings ? (int) $leaveSettings->monthly : 0;

        $consumedQuery = EmployeeLeaveDate::whereHas('leave', function ($q) use ($clientId, $request) {
            $q->where('client_id', $clientId)
                ->where('employee_id', $request->employee_id)
                ->where('trash', 0);

            if ($request->filled('ignore_leave_id')) {
                $q->where('id', '!=', $request->ignore_leave_id);
            }
        })
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('is_paid', 1);

        $consumedPaidLeaves = $consumedQuery->count();
        $leavesLeft = max(0, $monthlyAllowance - $consumedPaidLeaves);

        return response()->json([
            'monthly_quota' => $monthlyAllowance,
            'consumed' => $consumedPaidLeaves,
            'leaves_left' => $leavesLeft
        ]);
    }

    public function getPaidLeavesLeft(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'date' => 'nullable|string',
            'ignore_leave_id' => 'nullable|integer'
        ]);

        $clientId = $this->getClientId();

        try {
            $date = $request->filled('date')
                ? Carbon::createFromFormat('d-m-Y', $request->date)
                : Carbon::now();
        } catch (Exception $e) {
            $date = Carbon::now();
        }

        $month = $date->month;
        $year = $date->year;

        // Fetch employee
        $employee = Employee::where('client_id', $clientId)->findOrFail($request->employee_id);

        // CHANGED: resolve employee-specific policy first, fall back to department policy
        $leaveSettings = PaidLeave::resolveFor($employee);

        $monthlyAllowance = $leaveSettings ? (int) $leaveSettings->monthly : 0;

        $consumedQuery = EmployeeLeaveDate::whereHas('leave', function ($q) use ($clientId, $request) {
            $q->where('client_id', $clientId)
                ->where('employee_id', $request->employee_id)
                ->where('trash', 0);

            if ($request->filled('ignore_leave_id')) {
                $q->where('id', '!=', $request->ignore_leave_id);
            }
        })
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('is_paid', 1);

        $consumedPaidLeaves = $consumedQuery->count();
        $leavesLeft = max(0, $monthlyAllowance - $consumedPaidLeaves);

        return response()->json([
            'monthly_quota' => $monthlyAllowance,
            'consumed' => $consumedPaidLeaves,
            'leaves_left' => $leavesLeft
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:rl_employees_tbl,id',
            'leave_type_id' => 'required|integer|exists:rl_leave_types_tbl,id',
            'remarks' => 'nullable|string|max:500',
            'dates' => 'required|array|min:1',
            'dates.*' => 'required|date_format:d-m-Y|distinct',
            'is_paid' => 'required|array',
            'is_paid.*' => 'required|in:0,1',
            'override_reason' => 'nullable|string|max:500'
        ], [
            'dates.*.date_format' => 'Each date must be in DD-MM-YYYY format.',
            'dates.*.distinct' => 'Duplicate leave dates selected.',
        ]);

        // Validate that no dates are in the past
        $today = Carbon::today();
        foreach ($request->dates as $dateStr) {
            $parsedDate = Carbon::createFromFormat('d-m-Y', $dateStr)->startOfDay();
            if ($parsedDate->lt($today)) {
                return redirect()->back()->withErrors(['dates' => 'Leave dates cannot be in the past.'])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            $clientId = $this->getClientId();

            $leave = EmployeeLeave::create([
                'client_id' => $clientId,
                'employee_id' => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
                'remarks' => $request->remarks,
                'status' => 1,
                'trash' => 0,
                'created_by' => $clientId,
                'updated_by' => $clientId,
            ]);

            foreach ($request->dates as $index => $dateVal) {
                $isPaid = (int) ($request->is_paid[$index] ?? 0);
                $formattedSqlDate = Carbon::createFromFormat('d-m-Y', $dateVal)->format('Y-m-d');

                $leaveDate = EmployeeLeaveDate::create([
                    'leave_id' => $leave->id,
                    'date' => $formattedSqlDate,
                    'is_paid' => $isPaid
                ]);

                if ($isPaid === 1 && $request->filled('override_reason')) {
                    LeaveStatusLog::create([
                        'leave_date_id' => $leaveDate->id,
                        'leave_id' => $leave->id,
                        'previous_status' => 'unpaid', // ✅ Fixed
                        'new_status' => 'paid',
                        'changed_by' => $clientId,
                        'changed_by_guard' => 'client',
                        'reason' => 'Quota Exceeded: ' . $request->override_reason, // Optionally append context to the reason
                        'created_at' => Carbon::now()
                    ]);
                }
            }

            // START: LEAVE APPROVAL FLOW ASSIGNMENT
            $applicant = Employee::find($request->employee_id);

            // Look for specific dept flow, fallback to global (department_id IS NULL)
            $flow = \App\Models\LeaveFlow::where('client_id', $clientId)
                ->where(function ($q) use ($applicant) {
                    $q->where('department_id', $applicant->department_id)
                        ->orWhereNull('department_id');
                })
                ->where('status', 1)
                ->where('trash', 0)
                ->orderByRaw('department_id IS NULL ASC') // Prioritize specific dept
                ->first();

            if ($flow) {
                $firstStep = \App\Models\LeaveFlowStep::where('flow_id', $flow->id)->orderBy('step_order', 'asc')->first();
                if ($firstStep) {
                    $leave->update([
                        'flow_id' => $flow->id,
                        'current_step' => $firstStep->step_order,
                        'current_approver_emp_id' => $firstStep->approver_emp_id,
                        'approval_status' => 0 // Pending
                    ]);
                } else {
                    // Flow exists but has no steps - auto approve
                    $leave->update(['approval_status' => 2]);
                }
            } else {
                // No workflow defined in system - fallback to auto-approved or manual admin override
                $leave->update(['approval_status' => 2]);
            }
            // END: LEAVE APPROVAL FLOW ASSIGNMENT

            DB::commit();
            return redirect()->back()->with('success', 'Leave application submitted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error storing leave: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to submit leave application.');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'leave_type_id' => 'required|integer|exists:rl_leave_types_tbl,id',
            'remarks' => 'nullable|string|max:500',
            'dates' => 'required|array|min:1',
            'dates.*' => 'required|date_format:d-m-Y|distinct',
            'is_paid' => 'required|array',
            'is_paid.*' => 'required|in:0,1',
            'override_reason' => 'nullable|string|max:500'
        ], [
            'dates.*.date_format' => 'Each date must be in DD-MM-YYYY format.',
            'dates.*.distinct' => 'Duplicate leave dates selected.',
        ]);

        $today = Carbon::today();
        foreach ($request->dates as $dateStr) {
            $parsedDate = Carbon::createFromFormat('d-m-Y', $dateStr)->startOfDay();
            if ($parsedDate->lt($today)) {
                return redirect()->back()->withErrors(['dates' => 'Leave dates cannot be in the past.'])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            $clientId = $this->getClientId();
            $leave = EmployeeLeave::where('client_id', $clientId)->findOrFail($id);

            $leave->update([
                'leave_type_id' => $request->leave_type_id,
                'remarks' => $request->remarks,
                'updated_by' => $clientId,
            ]);

            $leave->dates()->delete();

            foreach ($request->dates as $index => $dateVal) {
                $isPaid = (int) ($request->is_paid[$index] ?? 0);
                $formattedSqlDate = Carbon::createFromFormat('d-m-Y', $dateVal)->format('Y-m-d');

                $leaveDate = EmployeeLeaveDate::create([
                    'leave_id' => $leave->id,
                    'date' => $formattedSqlDate,
                    'is_paid' => $isPaid
                ]);

                if ($isPaid === 1 && $request->filled('override_reason')) {
                    LeaveStatusLog::create([
                        'leave_date_id' => $leaveDate->id,
                        'leave_id' => $leave->id,
                        'previous_status' => 'unpaid', // ✅ Fixed
                        'new_status' => 'paid',
                        'changed_by' => $clientId,
                        'changed_by_guard' => 'client',
                        'reason' => 'Quota Exceeded: ' . $request->override_reason, // Optionally append context
                        'created_at' => Carbon::now()
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Leave application updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating leave: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update leave application.');
        }
    }

    public function destroy($id)
    {
        try {
            $clientId = $this->getClientId();
            $leave = EmployeeLeave::where('client_id', $clientId)->findOrFail($id);
            $leave->update(['trash' => 1]);

            return redirect()->back()->with('success', 'Leave record deleted successfully.');
        } catch (Exception $e) {
            Log::error('Error deleting leave: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete leave record.');
        }
    }

    public function updateDateStatus(Request $request)
    {
        $request->validate([
            'date_id' => 'required|integer|exists:rl_employee_leave_dates_tbl,id',
            'is_paid' => 'required|in:0,1',
            'reason' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            $clientId = $this->getClientId();
            $leaveDate = EmployeeLeaveDate::whereHas('leave', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            })->findOrFail($request->date_id);

            $oldStatus = $leaveDate->is_paid ? 'paid' : 'unpaid';
            $newStatus = $request->is_paid == 1 ? 'paid' : 'unpaid';

            if ($oldStatus !== $newStatus) {
                $leaveDate->is_paid = $request->is_paid;
                $leaveDate->save();

                LeaveStatusLog::create([
                    'leave_date_id' => $leaveDate->id,
                    'leave_id' => $leaveDate->leave_id,
                    'previous_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'changed_by' => $clientId,
                    'changed_by_guard' => 'client',
                    'reason' => $request->reason ?: 'Status updated manually via HRMS dashboard',
                    'created_at' => Carbon::now()
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Status updated & logged successfully.']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error toggling leave date status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update status.'], 500);
        }
    }

    public function storeLeaveType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'status' => 'required|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $clientId = $this->getClientId();

            $exists = LeaveType::where('client_id', $clientId)
                ->where('name', trim($request->name))
                ->where('trash', 0)
                ->first();

            if ($exists) {
                return response()->json(['success' => false, 'message' => 'This Leave Type already exists.'], 422);
            }

            $type = LeaveType::create([
                'client_id' => $clientId,
                'name' => trim($request->name),
                'status' => $request->status,
                'trash' => 0
            ]);

            return response()->json([
                'success' => true,
                'data' => $type,
                'message' => 'Leave type created successfully.'
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateLeaveType(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'status' => 'required|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            $clientId = $this->getClientId();
            $type = LeaveType::where('client_id', $clientId)->findOrFail($id);
            $type->update([
                'name' => trim($request->name),
                'status' => $request->status
            ]);

            return response()->json(['success' => true, 'message' => 'Leave type updated.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroyLeaveType($id)
    {
        try {
            $clientId = $this->getClientId();
            $type = LeaveType::where('client_id', $clientId)->findOrFail($id);
            $type->update(['trash' => 1]);

            return response()->json(['success' => true, 'message' => 'Leave type deleted.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}