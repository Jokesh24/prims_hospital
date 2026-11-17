<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\SubDepartment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SubDepartmentController extends Controller
{
    /**
     * Display a listing of sub-departments.
     */
    public function manage(Request $request)
    {
        try {
            $clientId = session('ClientUser');

            $query = SubDepartment::with('department')
                ->where('client_id', $clientId)
                ->where('trash', 0);

            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->filled('search')) {
                $query->where('sub_department_name', 'like', '%' . $request->search . '%');
            }

            $subDepartments = $query->latest()->paginate(15)->appends($request->all());

            $departments = Department::where('client_id', $clientId)
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('department_name', 'asc')
                ->get();

            return view('client.sub_departments.manage', compact('subDepartments', 'departments'));
        } catch (Exception $e) {
            Log::error('Error loading sub-departments: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong.');
        }
    }

    /**
     * Store newly created sub-departments (Handles multiple).
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|integer|exists:rl_departments_tbl,id',
            'sub_departments' => 'required|array|min:1',
            'sub_departments.*.name' => 'required|string|max:255',
            'sub_departments.*.status' => 'required|in:0,1',
        ]);

        try {
            DB::beginTransaction();
            $clientId = session('ClientUser');
            $lastCreated = null; // Track the created sub-dept for AJAX

            foreach ($request->sub_departments as $subDept) {
                if (empty($subDept['name']))
                    continue;

                $lastCreated = SubDepartment::create([
                    'client_id' => $clientId,
                    'department_id' => $request->department_id,
                    'sub_department_name' => $subDept['name'],
                    'status' => $subDept['status'],
                    'trash' => 0,
                    'created_by' => $clientId,
                    'updated_by' => $clientId,
                ]);
            }

            DB::commit();

            // ADDED: Check for AJAX request
            if ($request->ajax()) {
                return response()->json(['success' => true, 'data' => $lastCreated]);
            }

            return redirect()->back()->with('success', 'Sub-Departments created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating sub-departments: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to create sub-department.']);
            }
            return redirect()->back()->withInput()->with('error', 'Failed to create sub-departments.');
        }
    }

    /**
     * Update the specified sub-department.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'department_id' => 'required|integer|exists:rl_departments_tbl,id',
            'sub_department_name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        try {
            $clientId = session('ClientUser');
            $subDepartment = SubDepartment::where('client_id', $clientId)->findOrFail($id);

            $subDepartment->update([
                'department_id' => $request->department_id,
                'sub_department_name' => $request->sub_department_name,
                'status' => $request->status,
                'updated_by' => $clientId,
            ]);

            return redirect()->back()->with('success', 'Sub-Department updated successfully.');
        } catch (Exception $e) {
            Log::error('Error updating sub-department ID ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update sub-department.');
        }
    }

    /**
     * Move the specified sub-department to trash.
     */
    public function destroy($id)
    {
        try {
            $clientId = session('ClientUser');
            $subDepartment = SubDepartment::where('client_id', $clientId)->findOrFail($id);

            $subDepartment->update([
                'trash' => 1,
                'updated_by' => $clientId,
            ]);

            return redirect()->back()->with('success', 'Sub-Department has been trashed.');
        } catch (Exception $e) {
            Log::error('Error trashing sub-department ID ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete sub-department.');
        }
    }
}