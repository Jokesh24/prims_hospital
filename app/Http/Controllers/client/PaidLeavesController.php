<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaidLeave;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PaidLeavesController extends Controller
{
    private function getClientId()
    {
        return Auth::guard('client')->id() ?: session('ClientUser');
    }

    /* Display the Paid Leaves dashboard and employee-wise policies */
    public function manage(Request $request)
    {
        try {
            $clientId = $this->getClientId();

            // Kept for legacy/fallback reference only (department policies still exist
            // as a fallback when an employee has no individual policy configured).
            $departments = Department::where('client_id', $clientId)
                ->where('trash', 0)
                ->where('status', 1)
                ->orderBy('department_name', 'asc')
                ->get();

            // CHANGED: Employee list for the searchable dropdown
            $employees = Employee::with('department')
                ->where('client_id', $clientId)
                ->where('trash', 0)
                ->where('status', 1)
                ->orderBy('employee_name', 'asc')
                ->get();

            // CHANGED: Fetch all policies (employee-wise + any legacy department-wise), with employee + department eager loaded
            $policies = PaidLeave::with(['employee.department', 'department'])
                ->where('client_id', $clientId)
                ->where('trash', 0)
                ->orderByRaw('employee_id IS NULL, id DESC') // employee-wise policies first, then legacy department ones
                ->get();

            return view('client.paidleaves', compact('departments', 'employees', 'policies'));
        } catch (Exception $e) {
            Log::error('Error loading paid leaves: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong while loading the page.');
        }
    }

    /* Store or update the Employee Leave Policy */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:rl_employees_tbl,id',
            'week_off_type' => 'required|in:weekly,monthly',
            'week_offs' => 'required|integer|min:0',
            'monthly' => 'required|integer|min:0',
            'yearly' => 'required|integer|min:0',
        ]);

        try {
            $clientId = $this->getClientId();

            // Make sure the employee actually belongs to this client
            $employee = Employee::where('client_id', $clientId)
                ->where('id', $request->employee_id)
                ->firstOrFail();

            PaidLeave::updateOrCreate(
                [
                    'client_id' => $clientId,
                    'employee_id' => $employee->id, // CHANGED: match key is now employee_id, not department_id
                ],
                [
                    'department_id' => $employee->department_id, // kept in sync for reporting/legacy views
                    'week_off_type' => $request->week_off_type,
                    'week_offs' => $request->week_offs,
                    'monthly' => $request->monthly,
                    'yearly' => $request->yearly,
                    'status' => 1,
                    'created_by' => $clientId,
                    'updated_by' => $clientId,
                    'trash' => 0
                ]
            );

            return redirect()->back()->with('success', 'Employee Leave Policy configured successfully.');
        } catch (Exception $e) {
            Log::error('Error saving employee leave policy: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to save configuration. Please try again.');
        }
    }

    /**
     * ADDED: Bulk-apply the same Week Off + Paid Leave policy to every active employee
     * currently in the selected department. Writes one individual (employee-wise)
     * PaidLeave row per employee — identical in shape to what store() creates for a
     * single employee — so this is purely a convenience bulk-write, not a new policy type.
     */
    public function storeBulkByDepartment(Request $request)
    {
        $request->validate([
            'bulk_department_id' => 'required|integer|exists:rl_departments_tbl,id',
            'bulk_week_off_type' => 'required|in:weekly,monthly',
            'bulk_week_offs' => 'required|integer|min:0',
            'bulk_monthly' => 'required|integer|min:0',
            'bulk_yearly' => 'required|integer|min:0',
        ]);

        try {
            $clientId = $this->getClientId();

            $department = Department::where('client_id', $clientId)
                ->where('id', $request->bulk_department_id)
                ->firstOrFail();

            $employees = Employee::where('client_id', $clientId)
                ->where('department_id', $department->id)
                ->where('status', 1)
                ->where('trash', 0)
                ->get();

            if ($employees->isEmpty()) {
                return redirect()->back()->with('error', 'No active employees found in "' . $department->department_name . '". Nothing was saved.');
            }

            $now = now();
            $appliedCount = 0;

            DB::transaction(function () use ($employees, $department, $clientId, $request, $now, &$appliedCount) {
                foreach ($employees as $employee) {
                    PaidLeave::updateOrCreate(
                        [
                            'client_id' => $clientId,
                            'employee_id' => $employee->id,
                        ],
                        [
                            'department_id' => $employee->department_id,
                            'week_off_type' => $request->bulk_week_off_type,
                            'week_offs' => $request->bulk_week_offs,
                            'monthly' => $request->bulk_monthly,
                            'yearly' => $request->bulk_yearly,
                            'status' => 1,
                            'created_by' => $clientId,
                            'updated_by' => $clientId,
                            'trash' => 0,
                            'updated_at' => $now,
                        ]
                    );
                    $appliedCount++;
                }
            });

            return redirect()->back()->with(
                'success',
                'Policy applied to ' . $appliedCount . ' employee(s) in "' . $department->department_name . '" successfully.'
            );
        } catch (Exception $e) {
            Log::error('Error bulk-applying department leave policy: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to apply bulk configuration. Please try again.');
        }
    }

    /* Soft Delete an Employee Policy */
    public function destroy($id)
    {
        try {
            $clientId = $this->getClientId();
            $policy = PaidLeave::where('client_id', $clientId)->findOrFail($id);
            $policy->update(['trash' => 1]);

            return redirect()->back()->with('success', 'Employee policy deleted successfully.');
        } catch (Exception $e) {
            Log::error('Error deleting paid leaves: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete configuration.');
        }
    }
}