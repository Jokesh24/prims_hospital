<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class DesignationController extends Controller
{
    /**
     * Display a listing of the designations.
     */
    public function manage(Request $request)
    {
        try {
            $clientId = session('ClientUser');

            $query = Designation::with(['department', 'subDepartment'])
                ->where('client_id', $clientId)
                ->where('trash', 0);

            if ($request->filled('search')) {
                $query->where('designation_name', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->filled('sub_department_id')) {
                $query->where('sub_department_id', $request->sub_department_id);
            }

            $designations = $query->latest()->paginate(15)->appends($request->all());

            $departments = Department::where('client_id', $clientId)
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('department_name', 'asc')
                ->get();

            $subDepartments = \App\Models\SubDepartment::where('client_id', $clientId)
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('sub_department_name', 'asc')
                ->get();

            return view('client.designations.designations', compact('designations', 'departments', 'subDepartments'));
        } catch (Exception $e) {
            Log::error('Error loading designations: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong while loading the designations.');
        }
    }

    /**
     * Store a newly created designation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|integer|exists:rl_departments_tbl,id',
            'sub_department_id' => 'nullable|integer|exists:rl_sub_departments_tbl,id',
            'designation_name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        try {
            DB::beginTransaction();
            $clientId = session('ClientUser');

            // 1. Create Designation
            Designation::create([
                'client_id' => $clientId,
                'department_id' => $request->department_id,
                'sub_department_id' => $request->sub_department_id,
                'designation_name' => $request->designation_name,
                'status' => $request->status,
                'trash' => 0,
                'created_by' => $clientId,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Designation created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating designation: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to create designation. Please try again.');
        }
    }

    /**
     * Update the specified designation.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'department_id' => 'required|integer|exists:rl_departments_tbl,id',
            'sub_department_id' => 'nullable|integer|exists:rl_sub_departments_tbl,id',
            'designation_name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        try {
            DB::beginTransaction();
            $clientId = session('ClientUser');

            $designation = Designation::where('client_id', $clientId)->findOrFail($id);

            // 1. Update Core Data
            $designation->update([
                'department_id' => $request->department_id,
                'sub_department_id' => $request->sub_department_id,
                'designation_name' => $request->designation_name,
                'status' => $request->status,
                'updated_by' => $clientId,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Designation updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating designation ID ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update designation. Please try again.');
        }
    }

    /**
     * Move the specified designation to trash.
     */
    public function destroy($id)
    {
        try {
            $clientId = session('ClientUser');
            $designation = Designation::where('client_id', $clientId)->findOrFail($id);

            $designation->update([
                'trash' => 1,
                'updated_by' => $clientId,
            ]);

            return redirect()->back()->with('success', 'Designation has been trashed.');
        } catch (Exception $e) {
            Log::error('Error trashing designation ID ' . $id . ': ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete designation.');
        }
    }
}