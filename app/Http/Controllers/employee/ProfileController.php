<?php

namespace App\Http\Controllers\employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeSkill;
use App\Models\Attendance;
use App\Models\OfficeTiming;
use App\Models\Leave;
use App\Models\PaidLeave;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function showProfile()
    {
        $employee = Employee::with(['department', 'documents', 'skills'])->find(Auth::id());

        if (!$employee) {
            abort(404);
        }

        $newDocuments = $employee->documents->where('trash', 0)->keyBy('name');

        $currentYear = date('Y');
        $startOfYear = Carbon::now()->startOfYear();
        $today = Carbon::today();

        $leavesUsed = Leave::where('employee_id', $employee->id)
            ->where('leave_status', Leave::STATUS_ACCEPTED)
            ->whereYear('created_at', $currentYear)
            ->count();

        $paidLeaveSettings = PaidLeave::where('client_id', $employee->client_id)->first();
        $totalYearlyPaidLeaves = $paidLeaveSettings ? $paidLeaveSettings->yearly : 15;

        $workingDaysYTD = 0;
        $dateIterator = clone $startOfYear;
        while ($dateIterator->lte($today)) {
            if (!$dateIterator->isWeekend()) {
                $workingDaysYTD++;
            }
            $dateIterator->addDay();
        }

        $attendancesYTD = Attendance::where('emp_id', $employee->id)
            ->whereBetween('punch_in_time', [$startOfYear->startOfDay(), $today->copy()->endOfDay()])
            ->get();

        $officeTiming = OfficeTiming::where('client_id', $employee->client_id)->first();
        $halfDayLimit = $officeTiming ? (float) $officeTiming->half_day_limit : 4.0;

        $presentDaysYTD = 0;

        foreach ($attendancesYTD as $att) {
            $pIn = Carbon::parse($att->punch_in_time);
            if ($att->punch_out_time) {
                $pOut = Carbon::parse($att->punch_out_time);
                $hWorked = abs($pOut->diffInMinutes($pIn)) / 60;
                $presentDaysYTD += ($hWorked < $halfDayLimit) ? 0.5 : 1;
            } else {
                $presentDaysYTD += 1;
            }
        }

        $totalAbsentYTD = max(0, $workingDaysYTD - $presentDaysYTD);
        $attendanceRate = $workingDaysYTD > 0 ? round(($presentDaysYTD / $workingDaysYTD) * 100, 1) : 0;

        return view('employee.profile', compact(
            'employee',
            'leavesUsed',
            'totalYearlyPaidLeaves',
            'attendanceRate',
            'totalAbsentYTD',
            'newDocuments'
        ));
    }

    public function manageSkills(\Illuminate\Http\Request $request)
    {
        $employee = Auth::user();

        $request->validate([
            'skill_name.*' => 'required|string|max:255',
        ]);

        try {
            if ($request->has('skill_name')) {
                foreach ($request->skill_name as $key => $name) {
                    if (!empty($name)) {
                        EmployeeSkill::create([
                            'employee_id' => $employee->id,
                            'client_id' => $employee->client_id,
                            'name' => $name,
                            'description' => null, // Removed as per requirement
                            'status' => 1,
                            'trash' => 0,
                            'created_by' => $employee->id,
                            'updated_by' => $employee->id,
                        ]);
                    }
                }
            }

            return back()->with('success', 'Skills added successfully.')->with('active_tab', 'profile');
        } catch (\Exception $e) {
            Log::error('Employee Skills Update Error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while saving skills.')->with('active_tab', 'profile');
        }
    }

    public function updateSkill(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'skill_id' => 'required|exists:rl_employee_skills_tbl,id',
            'action' => 'required|in:update,delete'
        ]);

        try {
            $employee = Auth::user();

            $skill = EmployeeSkill::where('id', $request->skill_id)
                ->where('employee_id', $employee->id)
                ->firstOrFail();

            if ($request->action === 'delete') {
                $skill->update(['trash' => 1, 'updated_by' => $employee->id]);
                return back()->with('success', 'Skill removed successfully.')->with('active_tab', 'profile');
            }

            $request->validate([
                'edit_skill_name' => 'required|string|max:255',
            ]);

            $skill->update([
                'name' => $request->edit_skill_name,
                'description' => null, // Removed
                'updated_by' => $employee->id
            ]);

            return back()->with('success', 'Skill updated successfully.')->with('active_tab', 'profile');
        } catch (\Exception $e) {
            Log::error('Employee Skill Edit Error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while modifying the skill.')->with('active_tab', 'profile');
        }
    }

    public function uploadDocument(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'document_type' => 'required|string',
            'document_file' => 'required|file|mimes:pdf,jpeg,png,jpg|max:2048',
        ], [
            'document_file.max' => 'The document size must not exceed 2MB.',
            'document_file.mimes' => 'Only PDF, JPEG, PNG, and JPG files are allowed.',
        ]);

        try {
            $employee = Auth::user();

            if ($request->hasFile('document_file')) {
                $file = $request->file('document_file');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'uploads/employees/documents';

                $file->move(public_path($destinationPath), $fileName);
                $filePath = $destinationPath . '/' . $fileName;

                $type = $request->document_type;

                $legacyDocs = ['aadhaar_proof', 'pan_proof', 'qualification_proof'];

                if (in_array($type, $legacyDocs)) {
                    $employee->$type = $filePath;
                    $employee->save();
                } else {
                    EmployeeDocument::updateOrCreate(
                        ['employee_id' => $employee->id, 'name' => $type],
                        [
                            'client_id' => $employee->client_id,
                            'file' => $filePath,
                            'status' => 1,
                            'trash' => 0,
                            'created_by' => $employee->id,
                            'updated_by' => $employee->id,
                        ]
                    );
                }

                return back()->with('success', 'Document uploaded successfully.')->with('active_tab', 'documents');
            }

            return back()->with('error', 'No file was provided.')->with('active_tab', 'documents');
        } catch (\Exception $e) {
            Log::error('Employee Document Upload Error: ' . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred while uploading.')->with('active_tab', 'documents');
        }
    }

    public function updateProfile(\Illuminate\Http\Request $request)
    {
        $employee = Auth::user();

        $request->validate([
            'employee_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:rl_employees_tbl,email,' . $employee->id,
            'mobile_number' => 'required|numeric|digits:10',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'gender' => 'required|in:1,2',
            'dob' => 'required|date',
            'father_spouse_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'blood_group' => 'nullable|string|max:10',
            'alternative_mobile_number' => 'nullable|numeric|digits:10',
            'relation_with_alt_mobile' => 'required_with:alternative_mobile_number|nullable|string|max:50',
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'marital_status' => 'required|in:1,2',
            'marriage_date' => 'nullable|date|required_if:marital_status,1',
            'aadhaar_number' => 'nullable|numeric|digits:12',
            'pan_number' => 'nullable|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i',
            'uan_number' => 'nullable|numeric|digits:12',
            'esi_number' => 'nullable|numeric|digits:17',
            'highest_qualification' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'ifsc_code' => 'nullable|string|max:50',
            'account_type' => 'nullable|string|max:50',
        ]);

        try {
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = 'uploads/employees/photos';
                $file->move(public_path($destinationPath), $fileName);
                $employee->photo = $destinationPath . '/' . $fileName;
            }

            $employee->employee_name = $request->employee_name;
            $employee->email = $request->email;
            $employee->mobile_number = $request->mobile_number;
            $employee->gender = $request->gender;

            $employee->dob = $request->dob;
            $employee->father_spouse_name = $request->father_spouse_name;
            $employee->mother_name = $request->mother_name;
            $employee->blood_group = $request->blood_group;
            $employee->alternative_mobile_number = $request->alternative_mobile_number;
            $employee->relation_with_alt_mobile = $request->relation_with_alt_mobile;

            $employee->present_address = $request->present_address;
            $employee->permanent_address = $request->permanent_address;

            $employee->marital_status = $request->marital_status;
            $employee->marriage_date = $request->marital_status == 1 ? $request->marriage_date : null;

            $employee->aadhaar_number = $request->aadhaar_number;
            $employee->pan_number = strtoupper($request->pan_number);
            $employee->uan_number = $request->uan_number;
            $employee->esi_number = $request->esi_number;

            $employee->highest_qualification = $request->highest_qualification;

            $employee->bank_name = $request->bank_name;
            $employee->bank_account_number = $request->bank_account_number;
            $employee->ifsc_code = strtoupper($request->ifsc_code);
            $employee->account_type = $request->account_type;

            $employee->save();

            return back()->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            Log::error('Employee Profile Update Error: ' . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred while updating the profile.');
        }
    }
}