<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{

    public function manage(Request $request)
    {
        try {
            $clientId = session('ClientUser');

            $query = Department::where('trash', 0)
                ->where('client_id', $clientId)
                ->withCount('employees');

            if ($request->filled('sort_staff')) {
                $query->orderBy('employees_count', $request->sort_staff);
            } else {
                // Order by Priority ascending by default
                $query->orderBy('priority', 'asc')->latest();
            }

            $departments = $query->paginate(10)->appends($request->all());

            // Fetch the next available priority for the Add form
            $maxPriority = Department::where('client_id', $clientId)->where('trash', 0)->max('priority');
            $nextPriority = $maxPriority ? $maxPriority + 1 : 1;

            return view('client.departments.departments', compact('departments', 'nextPriority'));
        } catch (Exception $e) {
            Log::error('Error loading departments: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong.');
        }
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_name' => 'required|string|max:255',
            'priority' => 'required|integer|min:1', // <-- Validated
            'department_code' => 'nullable|string|max:50',
            'status' => 'required|in:0,1',
            'description' => 'nullable|string'
        ]);

        try {
            $department = Department::create([
                'department_name' => $request->department_name,
                'priority' => $request->priority,
                'department_code' => $request->department_code,
                'status' => $request->status,
                'description' => $request->description,
                'trash' => 0,
                'client_id' => session('ClientUser'),
                'created_by' => session('ClientUser'),
                'updated_by' => session('ClientUser'),
            ]);

            // ADDED: Check for AJAX request
            if ($request->ajax()) {
                return response()->json(['success' => true, 'data' => $department]);
            }

            return redirect()->back()->with('success', 'Department created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating department: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to create department.']);
            }
            return redirect()->back()->with('error', 'Failed to create department. Please try again.');
        }
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'department_name' => 'required|string|max:255',
            'priority' => 'required|integer|min:1', // <-- Validated
            'department_code' => 'nullable|string|max:50',
            'status' => 'required|in:0,1',
            'description' => 'nullable|string'
        ]);

        try {
            $clientId = session('ClientUser');
            $department = Department::where('client_id', $clientId)->findOrFail($id);

            $department->update([
                'department_name' => $request->department_name,
                'priority' => $request->priority, // <-- Updated
                'department_code' => $request->department_code,
                'status' => $request->status,
                'description' => $request->description,
                'updated_by' => session('ClientUser'),
            ]);

            return redirect()->back()->with('success', 'Department updated successfully.');
        } catch (Exception $e) {
            Log::error('Error updating department ID ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update department. Please try again.');
        }
    }

    /**
     * Move the specified department to trash (Soft Delete implementation).
     */
    public function destroy($id)
    {
        try {
            $clientId = session('ClientUser');

            // Find the department ONLY if it belongs to the logged-in client
            $department = Department::where('client_id', $clientId)->findOrFail($id); // <-- ADDED: Security filter

            // Setting trash = 1 as per your schema logic instead of physical delete
            $department->update([
                'trash' => 1,
                'updated_by' => session('ClientUser'),
            ]);

            return redirect()->back()->with('success', 'Department has been trashed.');
        } catch (Exception $e) {
            Log::error('Error trashing department ID ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete department. Please try again.');
        }
    }
}