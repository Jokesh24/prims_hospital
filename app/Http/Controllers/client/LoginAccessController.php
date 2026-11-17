<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeLoginAccess;
use App\Models\Employee;
use App\Models\Department;
use App\Models\SubDepartment;
use App\Models\Designation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class LoginAccessController extends Controller
{
    private function authorizeAccess()
    {
        if (!session()->has('ClientUser')) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function manage(Request $request)
    {
        try {
            $this->authorizeAccess();
            $clientId = session('ClientUser');

            $query = EmployeeLoginAccess::with(['employee.department', 'employee.designationInfo'])
                ->where('client_id', $clientId)
                ->where('trash', 0);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('employee_name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            }

            if ($request->filled('department_id')) {
                $deptId = $request->department_id;
                $query->whereHas('employee', function ($q) use ($deptId) {
                    $q->where('department_id', $deptId);
                });
            }

            $accessLists = $query->latest()->paginate(20)->appends($request->all());

            $departments = Department::where('client_id', $clientId)
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('department_name', 'asc')
                ->get();

            return view('client.login_access.manage', compact('accessLists', 'departments'));
        } catch (Exception $e) {
            Log::error('Manage Login Access Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong while loading login access data.');
        }
    }

    public function create()
    {
        try {
            $this->authorizeAccess();
            $clientId = session('ClientUser');

            $departments = Department::where('client_id', $clientId)->where('status', 1)->where('trash', 0)->get();

            return view('client.login_access.create', compact('departments'));
        } catch (Exception $e) {
            Log::error('Create Login Access Error: ' . $e->getMessage());
            return back()->with('error', 'Unable to load create form.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:rl_employees_tbl,id',
            'sections' => 'required|array|min:1',
            'status' => 'required|in:0,1'
        ], [
            'employee_id.required' => 'Please select an employee.',
            'sections.required' => 'Please grant access to at least one section.'
        ]);

        try {
            $this->authorizeAccess();
            $clientId = session('ClientUser');

            // Check for duplicates
            $exists = EmployeeLoginAccess::where('client_id', $clientId)
                ->where('employee_id', $request->employee_id)
                ->where('trash', 0)
                ->exists();

            if ($exists) {
                return back()->withInput()->with('error', 'Access configuration for this employee already exists. Please edit the existing record.');
            }

            EmployeeLoginAccess::create([
                'client_id' => $clientId,
                'employee_id' => $request->employee_id,
                'accessible_sections' => $request->sections, // Casts to JSON automatically via Model
                'status' => $request->status,
                'trash' => 0,
                'created_by' => $clientId,
            ]);

            return redirect()->route('client.login_access')->with('success', 'Employee access granted successfully.');
        } catch (Exception $e) {
            Log::error('Store Login Access Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to save access configuration.');
        }
    }

    public function edit($id)
    {
        try {
            $this->authorizeAccess();
            $clientId = session('ClientUser');

            $access = EmployeeLoginAccess::with('employee')->where('client_id', $clientId)->findOrFail($id);
            $departments = Department::where('client_id', $clientId)->where('status', 1)->where('trash', 0)->get();

            // Pre-load relationships for edit view
            $subDepartments = SubDepartment::where('client_id', $clientId)->where('department_id', $access->employee->department_id)->get();
            $designations = Designation::where('client_id', $clientId)->where('department_id', $access->employee->department_id)->get();
            $employees = Employee::where('client_id', $clientId)->where('designation_id', $access->employee->designation_id)->get();

            return view('client.login_access.edit', compact('access', 'departments', 'subDepartments', 'designations', 'employees'));
        } catch (Exception $e) {
            Log::error('Edit Login Access Error: ' . $e->getMessage());
            return back()->with('error', 'Unable to load access configuration.');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:rl_employees_tbl,id',
            'sections' => 'required|array|min:1',
            'status' => 'required|in:0,1'
        ], [
            'sections.required' => 'Please grant access to at least one section.'
        ]);

        try {
            $this->authorizeAccess();
            $clientId = session('ClientUser');

            $access = EmployeeLoginAccess::where('client_id', $clientId)->findOrFail($id);

            // Check if changing to an employee that already has access (excluding current record)
            if ($access->employee_id != $request->employee_id) {
                $exists = EmployeeLoginAccess::where('client_id', $clientId)
                    ->where('employee_id', $request->employee_id)
                    ->where('id', '!=', $id)
                    ->where('trash', 0)
                    ->exists();

                if ($exists) {
                    return back()->withInput()->with('error', 'Access configuration for this employee already exists elsewhere.');
                }
            }

            $access->update([
                'employee_id' => $request->employee_id,
                'accessible_sections' => $request->sections,
                'status' => $request->status,
                'updated_by' => $clientId,
            ]);

            return redirect()->route('client.login_access')->with('success', 'Employee access updated successfully.');
        } catch (Exception $e) {
            Log::error('Update Login Access Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update access configuration.');
        }
    }

    public function destroy($id)
    {
        try {
            $this->authorizeAccess();
            $clientId = session('ClientUser');

            $access = EmployeeLoginAccess::where('client_id', $clientId)->findOrFail($id);
            $access->update([
                'trash' => 1,
                'updated_by' => $clientId
            ]);

            return back()->with('success', 'Login access revoked and trashed successfully.');
        } catch (Exception $e) {
            Log::error('Destroy Login Access Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to remove access.');
        }
    }

    // AJAX Endpoint for cascading dropdowns
    public function fetchEmployees(Request $request)
    {
        try {
            $clientId = session('ClientUser');
            $type = $request->type;

            if ($type === 'sub_departments') {
                $data = SubDepartment::where('client_id', $clientId)->where('department_id', $request->department_id)->where('status', 1)->where('trash', 0)->get(['id', 'sub_department_name as name']);
                return response()->json(['status' => true, 'data' => $data]);
            }

            if ($type === 'designations') {
                $query = Designation::where('client_id', $clientId)->where('department_id', $request->department_id)->where('status', 1)->where('trash', 0);
                if ($request->filled('sub_department_id')) {
                    $query->where('sub_department_id', $request->sub_department_id);
                }
                $data = $query->get(['id', 'designation_name as name']);
                return response()->json(['status' => true, 'data' => $data]);
            }

            if ($type === 'employees') {
                $query = Employee::where('client_id', $clientId)->where('status', 1)->where('trash', 0);
                if ($request->filled('department_id'))
                    $query->where('department_id', $request->department_id);
                if ($request->filled('sub_department_id'))
                    $query->where('sub_department_id', $request->sub_department_id);
                if ($request->filled('designation_id'))
                    $query->where('designation_id', $request->designation_id);

                $data = $query->get(['id', 'employee_name', 'employee_id']);

                $formatted = $data->map(function ($emp) {
                    return [
                        'id' => $emp->id,
                        'name' => $emp->employee_name . ' (' . $emp->employee_id . ')'
                    ];
                });

                return response()->json(['status' => true, 'data' => $formatted]);
            }

            return response()->json(['status' => false]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }
}