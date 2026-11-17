<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\Deductions;
use App\Models\Earnings;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees with Search, Filters & Department Grouping.
     */
    public function manage(Request $request)
    {
        try {
            $clientId = session('ClientUser');

            // Start query, join departments for priority sorting, and eager load relations
            $query = Employee::with(['department', 'designationInfo'])
                ->leftJoin('rl_departments_tbl', 'rl_employees_tbl.department_id', '=', 'rl_departments_tbl.id')
                ->select('rl_employees_tbl.*')
                ->where('rl_employees_tbl.trash', 0)
                ->where('rl_employees_tbl.client_id', $clientId);

            // 1. Text Search Filter (Name, Email, or Employee ID)
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('rl_employees_tbl.employee_name', 'like', "%{$search}%")
                        ->orWhere('rl_employees_tbl.employee_id', 'like', "%{$search}%")
                        ->orWhere('rl_employees_tbl.email', 'like', "%{$search}%");
                });
            }

            // 2. Department Filter
            if ($request->filled('department_id')) {
                $query->where('rl_employees_tbl.department_id', $request->department_id);
            }

            // 3. Status Filter (0 or 1)
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('rl_employees_tbl.status', (int) $request->status);
            }

            // Order by Department priority first, then Employee Name
            $query->orderByRaw('COALESCE(rl_departments_tbl.priority, 99999) ASC')
                ->orderBy('rl_employees_tbl.employee_name', 'asc');

            // Execute query with pagination and retain GET parameters in links
            $employees = $query->paginate(50)->appends($request->all());

            // Group the current page's employees by Department while retaining order
            $groupedEmployees = $employees->getCollection()->groupBy(function ($emp) {
                return $emp->department ? $emp->department->department_name : 'No Department';
            });

            // Fetch active departments for the filter dropdown
            $departments = Department::where('client_id', $clientId)
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('priority', 'asc')
                ->get();

            return view('client.employees.employees', compact('employees', 'groupedEmployees', 'departments'));
        } catch (Exception $e) {
            Log::error('Error loading employees manage page: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong while loading the directory.');
        }
    }

    /**
     * Export employees to a professional, clean Excel spreadsheet.
     */
    public function exportEmployeesExcel(Request $request): StreamedResponse
    {
        try {
            $clientId = session('ClientUser');

            // Added subDepartment to eager loading for export
            $query = Employee::with(['department', 'designationInfo', 'subDepartment'])
                ->leftJoin('rl_departments_tbl', 'rl_employees_tbl.department_id', '=', 'rl_departments_tbl.id')
                ->select('rl_employees_tbl.*')
                ->where('rl_employees_tbl.trash', 0)
                ->where('rl_employees_tbl.client_id', $clientId);

            // 1. Search Filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('rl_employees_tbl.employee_name', 'like', "%{$search}%")
                        ->orWhere('rl_employees_tbl.employee_id', 'like', "%{$search}%")
                        ->orWhere('rl_employees_tbl.email', 'like', "%{$search}%");
                });
            }

            // 2. Department Filter
            if ($request->filled('department_id')) {
                $query->where('rl_employees_tbl.department_id', $request->department_id);
            }

            // 3. Status Filter
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('rl_employees_tbl.status', (int) $request->status);
            }

            // Order by Department priority first, then Employee Name
            $employees = $query->orderByRaw('COALESCE(rl_departments_tbl.priority, 99999) ASC')
                ->orderBy('rl_employees_tbl.employee_name', 'asc')
                ->get();

            $client = \App\Models\Client::find($clientId);
            $companyName = $client->client_name ?? 'PRIMS HRMS';
            $filename = 'Employee_Directory_' . date('d_M_Y_His') . '.xls';

            return response()->streamDownload(function () use ($employees, $companyName) {
                echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
                echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
                echo '<style>
                    body { font-family: "Segoe UI", Arial, sans-serif; font-size: 11px; color: #1e293b; }
                    .title-header { font-size: 15px; font-weight: bold; color: #0f172a; text-align: left; }
                    .meta-info { font-size: 11px; color: #475569; margin-bottom: 10px; }
                    .dept-header { background-color: #0f172a; color: #ffffff; font-weight: bold; font-size: 12px; padding: 8px 10px; text-transform: uppercase; }
                    th { background-color: #f1f5f9; color: #334155; font-weight: bold; border: 1px solid #cbd5e1; padding: 7px 10px; text-align: center; }
                    td { border: 1px solid #cbd5e1; padding: 6px 10px; vertical-align: middle; }
                    .text-left { text-align: left; }
                    .text-center { text-align: center; }
                    .str-format { mso-number-format:"\@"; }
                </style></head><body>';

                echo '<table>';

                // Title Block (Updated colspan to 30 for all columns)
                echo '<tr><td colspan="30" class="title-header">' . htmlspecialchars($companyName) . ' — Employee Directory Detailed Report</td></tr>';
                echo '<tr><td colspan="30" class="meta-info">Total Records: ' . $employees->count() . ' &nbsp;|&nbsp; Generated on: ' . date('d M Y, h:i A') . '</td></tr>';
                echo '<tr><td colspan="30" style="height: 6px; border:none;"></td></tr>';

                // Group by Department
                $grouped = $employees->groupBy(function ($item) {
                    return $item->department ? $item->department->department_name : 'No Department';
                });

                foreach ($grouped as $deptName => $deptEmployees) {
                    echo '<tr><td colspan="30" class="dept-header">DEPARTMENT: ' . strtoupper(htmlspecialchars($deptName)) . ' (' . $deptEmployees->count() . ' Staff)</td></tr>';

                    // All fields exported
                    echo '<tr>
                        <th style="width: 45px;">Sl.No</th>
                        <th style="width: 120px;">EMP ID</th>
                        <th style="width: 220px; text-align: left;">Employee Name</th>
                        <th style="width: 180px; text-align: left;">Department</th>
                        <th style="width: 180px; text-align: left;">Sub Department</th>
                        <th style="width: 180px; text-align: left;">Designation</th>
                        <th style="width: 200px;">Email Address</th>
                        <th style="width: 120px;">Mobile No</th>
                        <th style="width: 120px;">Alt. Mobile No</th>
                        <th style="width: 100px;">Gender</th>
                        <th style="width: 120px;">Date of Birth</th>
                        <th style="width: 100px;">Blood Group</th>
                        <th style="width: 120px;">Marital Status</th>
                        <th style="width: 180px;">Father/Spouse Name</th>
                        <th style="width: 250px;">Present Address</th>
                        <th style="width: 250px;">Permanent Address</th>
                        <th style="width: 120px;">Gross Salary</th>
                        <th style="width: 120px;">Payment Mode</th>
                        <th style="width: 180px;">Bank Name</th>
                        <th style="width: 180px;">Bank Branch</th>
                        <th style="width: 160px;">Account Number</th>
                        <th style="width: 120px;">IFSC Code</th>
                        <th style="width: 120px;">Account Type</th>
                        <th style="width: 150px;">Aadhaar Number</th>
                        <th style="width: 150px;">PAN Number</th>
                        <th style="width: 150px;">UAN Number</th>
                        <th style="width: 160px;">ESI Number</th>
                        <th style="width: 180px;">Highest Qualification</th>
                        <th style="width: 120px;">Joining Date</th>
                        <th style="width: 100px;">Status</th>
                    </tr>';

                    $idx = 1;
                    $paymentModeMap = [1 => 'Bank Transfer', 2 => 'Cash', 3 => 'Cheque', 4 => 'UPI'];

                    foreach ($deptEmployees as $emp) {
                        $designationName = $emp->designationInfo->designation_name ?? '--';
                        $subDeptName = $emp->subDepartment->sub_department_name ?? '--';

                        $gender = $emp->gender == 1 ? 'Male' : ($emp->gender == 2 ? 'Female' : '--');
                        $maritalStatus = $emp->marital_status == 1 ? 'Married' : ($emp->marital_status == 2 ? 'Unmarried' : '--');
                        $paymentMode = $paymentModeMap[$emp->payment_mode] ?? '--';
                        $status = $emp->status == 1 ? 'Active' : 'Inactive';

                        echo '<tr>';
                        echo '<td class="text-center">' . $idx++ . '</td>';
                        echo '<td class="text-center str-format" style="font-weight:600;">' . htmlspecialchars($emp->employee_id ?? '') . '</td>';
                        echo '<td class="text-left" style="font-weight: 600; color:#0f172a;">' . htmlspecialchars($emp->employee_name ?? '') . '</td>';
                        echo '<td class="text-left">' . htmlspecialchars($deptName) . '</td>';
                        echo '<td class="text-left">' . htmlspecialchars($subDeptName) . '</td>';
                        echo '<td class="text-left">' . htmlspecialchars($designationName) . '</td>';
                        echo '<td class="text-center">' . htmlspecialchars($emp->email ?? '--') . '</td>';
                        echo '<td class="text-center str-format">' . htmlspecialchars($emp->mobile_number ?? '--') . '</td>';
                        echo '<td class="text-center str-format">' . htmlspecialchars($emp->alternative_mobile_number ?? '--') . '</td>';
                        echo '<td class="text-center">' . htmlspecialchars($gender) . '</td>';
                        echo '<td class="text-center">' . htmlspecialchars($emp->dob ? \Carbon\Carbon::parse($emp->dob)->format('d-m-Y') : '--') . '</td>';
                        echo '<td class="text-center">' . htmlspecialchars($emp->blood_group ?? '--') . '</td>';
                        echo '<td class="text-center">' . htmlspecialchars($maritalStatus) . '</td>';
                        echo '<td class="text-left">' . htmlspecialchars($emp->father_spouse_name ?? '--') . '</td>';
                        echo '<td class="text-left">' . htmlspecialchars($emp->present_address ?? '--') . '</td>';
                        echo '<td class="text-left">' . htmlspecialchars($emp->permanent_address ?? '--') . '</td>';
                        echo '<td class="text-center str-format">' . htmlspecialchars($emp->gross_salary ?? '--') . '</td>';
                        echo '<td class="text-center">' . htmlspecialchars($paymentMode) . '</td>';
                        echo '<td class="text-left">' . htmlspecialchars($emp->bank_name ?? '--') . '</td>';
                        echo '<td class="text-left">' . htmlspecialchars($emp->bank_branch ?? '--') . '</td>';
                        echo '<td class="text-center str-format">' . htmlspecialchars($emp->bank_account_number ?? '--') . '</td>';
                        echo '<td class="text-center str-format">' . htmlspecialchars($emp->ifsc_code ?? '--') . '</td>';
                        echo '<td class="text-center">' . htmlspecialchars($emp->account_type ?? '--') . '</td>';
                        echo '<td class="text-center str-format">' . htmlspecialchars($emp->aadhaar_number ?? '--') . '</td>';
                        echo '<td class="text-center str-format">' . htmlspecialchars($emp->pan_number ?? '--') . '</td>';
                        echo '<td class="text-center str-format">' . htmlspecialchars($emp->uan_number ?? '--') . '</td>';
                        echo '<td class="text-center str-format">' . htmlspecialchars($emp->esi_number ?? '--') . '</td>';
                        echo '<td class="text-left">' . htmlspecialchars($emp->highest_qualification ?? '--') . '</td>';
                        echo '<td class="text-center">' . htmlspecialchars($emp->joining_date ? \Carbon\Carbon::parse($emp->joining_date)->format('d-m-Y') : '--') . '</td>';
                        echo '<td class="text-center">' . htmlspecialchars($status) . '</td>';
                        echo '</tr>';
                    }

                    echo '<tr><td colspan="30" style="border:none; height: 10px;"></td></tr>';
                }

                if ($employees->isEmpty()) {
                    echo '<tr><td colspan="30" class="text-center" style="padding: 25px; color: #64748b;">No employee records match the selected filter criteria.</td></tr>';
                }

                echo '</table></body></html>';
            }, $filename, [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (Exception $e) {
            Log::error('Error exporting employees to Excel: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate employee export.');
        }
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        try {
            $clientId = session('ClientUser');

            $departments = Department::where('status', 1)
                ->where('trash', 0)
                ->where('client_id', $clientId)
                ->get();

            // Fetch Sub Departments
            $subDepartments = \App\Models\SubDepartment::where('status', 1)
                ->where('trash', 0)
                ->where('client_id', $clientId)
                ->get();

            // Fetch Designations
            $designations = collect();

            $earnings = Earnings::where('created_by', $clientId)
                ->whereNull('employee_id')->where('status', 1)->where('trash', 0)->orderBy('id')->get();

            $deductions = Deductions::where('created_by', $clientId)
                ->whereNull('employee_id')->where('status', 1)->where('trash', 0)->orderBy('id')->get();

            // Pass designations to view
            return view('client.employees.add_employee', compact('departments', 'designations', 'earnings', 'deductions', 'subDepartments'));
        } catch (Exception $e) {
            Log::error('Error loading add employee page: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to load the form. Please try again.');
        }
    }

    /**
     * Fetch designations for a specific department (Used for AJAX dependent dropdowns).
     */
    public function getByDepartment($department_id)
    {
        try {
            $clientId = session('ClientUser');

            $designations = \App\Models\Designation::where('client_id', $clientId)
                ->where('department_id', $department_id)
                ->where('status', 1)
                ->where('trash', 0)
                ->orderBy('designation_name', 'asc')
                ->get(['id', 'designation_name']);

            return response()->json($designations);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load designations'], 500);
        }
    }

    /**
     * Store a newly created employee with strict validations.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string|max:50',
            'employee_name' => 'required|string|max:255',
            'department_id' => 'required|exists:rl_departments_tbl,id',
            'sub_department_id' => 'nullable|integer',
            'designation_id' => 'required|integer|exists:rl_designations_tbl,id',
            'email' => 'nullable|email|max:255|unique:rl_employees_tbl,email',
            'mobile_number' => 'required|numeric|digits:10',
            'gross_salary' => 'required|numeric|min:0',
            'joining_date' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'gender' => 'required|in:1,2',
            'dob' => 'nullable|date',
            'father_spouse_name' => 'nullable|string|max:255',
            'blood_group' => 'nullable|string|max:10',
            'alternative_mobile_number' => 'nullable|numeric|digits:10',
            'relation_with_alt_mobile' => 'required_with:alternative_mobile_number|nullable|string|max:50',
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'marital_status' => 'nullable|in:1,2',
            'marriage_date' => 'nullable|date',
            'inactive_comments' => 'nullable|string',
            'aadhaar_number' => 'nullable|numeric|digits:12',
            'aadhaar_proof' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'pan_number' => 'nullable|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i',
            'pan_proof' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'uan_number' => 'nullable|numeric|digits:12',
            'esi_number' => 'nullable|numeric|digits:17',
            'highest_qualification' => 'nullable|string|max:100',
            'qualification_proof' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_branch' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:50',
            'account_type' => 'nullable|string|max:50',
            'pf_applicable' => 'nullable|in:0,1',
            'payment_mode' => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->except([
                'photo',
                'aadhaar_proof',
                'pan_proof',
                'qualification_proof',
                'earning_id',
                'earning_title',
                'earning_type',
                'earning_value',
                'deduction_id',
                'deduction_title',
                'deduction_type',
                'deduction_value',
                'is_individual_salary'
            ]);

            if ($request->hasFile('photo'))
                $data['photo'] = $this->uploadFile($request->file('photo'), 'uploads/employees/photos');
            if ($request->hasFile('aadhaar_proof'))
                $data['aadhaar_proof'] = $this->uploadFile($request->file('aadhaar_proof'), 'uploads/employees/documents');
            if ($request->hasFile('pan_proof'))
                $data['pan_proof'] = $this->uploadFile($request->file('pan_proof'), 'uploads/employees/documents');
            if ($request->hasFile('qualification_proof'))
                $data['qualification_proof'] = $this->uploadFile($request->file('qualification_proof'), 'uploads/employees/documents');

            $data['status'] = 1;
            $data['trash'] = 0;
            $data['client_id'] = session('ClientUser');
            $data['created_by'] = session('ClientUser');
            $data['password'] = Hash::make('123123');
            $data['biometric_id'] = $request->employee_id;

            $employee = Employee::create($data);

            $isIndividual = $request->has('is_individual_salary');

            // --- AUTO PROFESSIONAL TAX LOGIC ---
            $ptax = 0;
            $grossSalary = (float) $request->gross_salary;
            if ($grossSalary >= 15001 && $grossSalary <= 20000) {
                $ptax = 150;
            } elseif ($grossSalary > 20000) {
                $ptax = 200;
            }

            if ($ptax > 0) {
                Deductions::create([
                    'employee_id' => $employee->id,
                    'deduction_title' => 'Professional Tax',
                    'value_type' => 2,
                    'deduction_percent' => 0,
                    'deduction_money' => $ptax,
                    'status' => 1,
                    'trash' => 0,
                    'created_by' => session('ClientUser'),
                ]);
            }
            // --- END PROFESSIONAL TAX LOGIC ---

            /* Earnings & Deductions ONLY saved if Individual is checked */
            if ($isIndividual) {
                if ($request->filled('earning_title')) {
                    foreach ($request->earning_title as $key => $title) {
                        if (empty($title))
                            continue;
                        Earnings::create([
                            'employee_id' => $employee->id,
                            'earnings_title' => $title,
                            'value_type' => $request->earning_type[$key],
                            'earnings_percent' => $request->earning_type[$key] == 1 ? $request->earning_value[$key] : 0,
                            'earnings_money' => $request->earning_type[$key] == 2 ? $request->earning_value[$key] : 0,
                            'status' => 1,
                            'trash' => 0,
                            'created_by' => session('ClientUser'),
                        ]);
                    }
                }

                if ($request->filled('deduction_title')) {
                    foreach ($request->deduction_title as $key => $title) {
                        if (empty($title))
                            continue;

                        // Skip manual PT entry to prevent duplication
                        if (in_array(strtoupper(trim($title)), ['PROFESSIONAL TAX', 'PTAX', 'PT', 'PROFESSIONAL TAX (PT)', 'PROF TAX'])) {
                            continue;
                        }

                        Deductions::create([
                            'employee_id' => $employee->id,
                            'deduction_title' => $title,
                            'value_type' => $request->deduction_type[$key],
                            'deduction_percent' => $request->deduction_type[$key] == 1 ? $request->deduction_value[$key] : 0,
                            'deduction_money' => $request->deduction_type[$key] == 2 ? $request->deduction_value[$key] : 0,
                            'status' => 1,
                            'trash' => 0,
                            'created_by' => session('ClientUser'),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('client.employees')->with('success', 'Employee successfully registered and added to the directory.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating employee: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Failed to save employee data. System Error: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to handle direct public uploads.
     */
    private function uploadFile($file, $destinationPath)
    {
        if ($file) {
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move(public_path($destinationPath), $fileName);

            return $destinationPath . '/' . $fileName;
        }

        return null;
    }

    /**
     * Display the specified employee profile.
     */
    public function show($id)
    {
        try {
            $clientId = session('ClientUser');

            // Find the employee, ensuring they belong to the logged-in client
            $employee = Employee::with('department')
                ->where('trash', 0)
                ->where('client_id', $clientId)
                ->findOrFail($id);
            return view('client.employees.view_employee', compact('employee'));
        } catch (Exception $e) {
            Log::error('Error viewing employee ID ' . $id . ': ' . $e->getMessage());
            return redirect()->route('client.employees')->with('error', 'Employee not found.');
        }
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit($id)
    {
        try {
            $clientId = session('ClientUser');

            $employee = Employee::where('trash', 0)->where('client_id', $clientId)->findOrFail($id);

            $departments = Department::where('status', 1)->where('trash', 0)->where('client_id', $clientId)->get();

            // Fetch Sub Departments
            $subDepartments = \App\Models\SubDepartment::where('status', 1)
                ->where('trash', 0)
                ->where('client_id', $clientId)
                ->get();

            $designations = \App\Models\Designation::where('status', 1)
                ->where('trash', 0)
                ->where('client_id', $clientId)
                ->where('department_id', $employee->department_id)
                ->get();

            // Get employee-specific earnings
            $earnings = Earnings::where('employee_id', $employee->id)
                ->where('created_by', $clientId)
                ->where('trash', 0)
                ->where('status', 1)
                ->orderBy('id')
                ->get();

            // If employee has no earnings, show client's global earnings
            if ($earnings->isEmpty()) {
                $earnings = Earnings::whereNull('employee_id')
                    ->where('created_by', $clientId)
                    ->where('trash', 0)
                    ->where('status', 1)
                    ->orderBy('id')
                    ->get();
            }

            // Get employee-specific deductions
            $deductions = Deductions::where('employee_id', $employee->id)
                ->where('created_by', $clientId)
                ->where('trash', 0)
                ->where('status', 1)
                ->orderBy('id')
                ->get();

            // If employee has no deductions, show client's global deductions
            if ($deductions->isEmpty()) {
                $deductions = Deductions::whereNull('employee_id')
                    ->where('created_by', $clientId)
                    ->where('trash', 0)
                    ->where('status', 1)
                    ->orderBy('id')
                    ->get();
            }

            // Pass designations to view
            return view('client.employees.edit_employee', compact('employee', 'departments', 'designations', 'earnings', 'deductions', 'subDepartments'));
        } catch (Exception $e) {
            Log::error('Error loading edit employee page: ' . $e->getMessage());
            return redirect()->route('client.employees')->with('error', 'Failed to load employee data.');
        }
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|string|max:50',
            'employee_name' => 'required|string|max:255',
            'department_id' => 'required|exists:rl_departments_tbl,id',
            'sub_department_id' => 'nullable|integer',
            'designation_id' => 'required|integer|exists:rl_designations_tbl,id',
            'email' => 'nullable|email|max:255|unique:rl_employees_tbl,email,' . $id,
            'mobile_number' => 'required|numeric|digits:10',
            'gross_salary' => 'required|numeric|min:0',
            'joining_date' => 'nullable|date',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'dob' => 'nullable|date',
            'marital_status' => 'nullable|in:1,2',
            'marriage_date' => 'nullable|date',
            'inactive_comments' => 'nullable|string',
            'gender' => 'nullable|in:1,2',
            'father_spouse_name' => 'nullable|string|max:255',
            'blood_group' => 'nullable|string|max:10',
            'alternative_mobile_number' => 'nullable|numeric|digits:10',
            'relation_with_alt_mobile' => 'required_with:alternative_mobile_number|nullable|string|max:50',
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'aadhaar_number' => 'nullable|numeric|digits:12',
            'aadhaar_proof' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'pan_number' => 'nullable|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i',
            'pan_proof' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'uan_number' => 'nullable|numeric|digits:12',
            'esi_number' => 'nullable|numeric|digits:17',
            'highest_qualification' => 'nullable|string|max:100',
            'qualification_proof' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_branch' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:50',
            'account_type' => 'nullable|string|max:50',
            'status' => 'required|in:0,1',
            'password' => 'nullable|string|min:6',
            'pf_applicable' => 'nullable|in:0,1',
            'payment_mode' => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($id);

            $data = $request->except([
                'photo',
                'aadhaar_proof',
                'pan_proof',
                'qualification_proof',
                'earning_id',
                'earning_title',
                'earning_type',
                'earning_value',
                'deduction_id',
                'deduction_title',
                'deduction_type',
                'deduction_value',
                'is_individual_salary'
            ]);
            $data['biometric_id'] = $request->employee_id;

            if ($request->hasFile('photo'))
                $data['photo'] = $this->uploadFile($request->file('photo'), 'uploads/employees/photos');
            if ($request->hasFile('aadhaar_proof'))
                $data['aadhaar_proof'] = $this->uploadFile($request->file('aadhaar_proof'), 'uploads/employees/documents');
            if ($request->hasFile('pan_proof'))
                $data['pan_proof'] = $this->uploadFile($request->file('pan_proof'), 'uploads/employees/documents');
            if ($request->hasFile('qualification_proof'))
                $data['qualification_proof'] = $this->uploadFile($request->file('qualification_proof'), 'uploads/employees/documents');

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            } else {
                unset($data['password']);
            }

            $data['updated_by'] = session('ClientUser');
            $employee->update($data);

            $isIndividual = $request->has('is_individual_salary');

            $earningIds = [];
            $deductionIds = [];

            // --- AUTO PROFESSIONAL TAX LOGIC ---
            $ptax = 0;
            $grossSalary = (float) $request->gross_salary;
            if ($grossSalary >= 15001 && $grossSalary <= 20000) {
                $ptax = 150;
            } elseif ($grossSalary > 20000) {
                $ptax = 200;
            }

            $ptDeduction = Deductions::where('employee_id', $employee->id)
                ->whereIn('deduction_title', ['Professional Tax', 'PTAX', 'PT', 'Professional Tax (PT)', 'PROF TAX'])
                ->first();

            if ($ptax > 0) {
                if ($ptDeduction) {
                    $ptDeduction->update([
                        'deduction_money' => $ptax,
                        'value_type' => 2,
                        'deduction_title' => 'Professional Tax',
                        'updated_by' => session('ClientUser'),
                    ]);
                    $deductionIds[] = $ptDeduction->id;
                } else {
                    $newPt = Deductions::create([
                        'employee_id' => $employee->id,
                        'deduction_title' => 'Professional Tax',
                        'value_type' => 2,
                        'deduction_percent' => 0,
                        'deduction_money' => $ptax,
                        'status' => 1,
                        'trash' => 0,
                        'created_by' => session('ClientUser'),
                        'updated_by' => session('ClientUser'),
                    ]);
                    $deductionIds[] = $newPt->id;
                }
            }
            // --- END PROFESSIONAL TAX LOGIC ---

            if ($isIndividual) {
                /* Update/Create Earnings strictly for this employee */
                if ($request->filled('earning_title')) {
                    foreach ($request->earning_title as $key => $title) {
                        if (empty($title))
                            continue;

                        $earningData = [
                            'employee_id' => $employee->id,
                            'earnings_title' => $title,
                            'value_type' => $request->earning_type[$key],
                            'earnings_percent' => $request->earning_type[$key] == 1 ? $request->earning_value[$key] : 0,
                            'earnings_money' => $request->earning_type[$key] == 2 ? $request->earning_value[$key] : 0,
                            'status' => 1,
                            'trash' => 0,
                            'updated_by' => session('ClientUser'),
                        ];

                        if (!empty($request->earning_id[$key])) {
                            $earning = Earnings::where('id', $request->earning_id[$key])->where('employee_id', $employee->id)->first();
                            if ($earning) {
                                $earning->update($earningData);
                                $earningIds[] = $earning->id;
                            } else {
                                $earningData['created_by'] = session('ClientUser');
                                $newEarning = Earnings::create($earningData);
                                $earningIds[] = $newEarning->id;
                            }
                        } else {
                            $earningData['created_by'] = session('ClientUser');
                            $newEarning = Earnings::create($earningData);
                            $earningIds[] = $newEarning->id;
                        }
                    }
                }

                /* Update/Create Deductions strictly for this employee */
                if ($request->filled('deduction_title')) {
                    foreach ($request->deduction_title as $key => $title) {
                        if (empty($title))
                            continue;

                        // Skip manual PT entry to prevent duplication
                        if (in_array(strtoupper(trim($title)), ['PROFESSIONAL TAX', 'PTAX', 'PT', 'PROFESSIONAL TAX (PT)', 'PROF TAX'])) {
                            continue;
                        }

                        $deductionData = [
                            'employee_id' => $employee->id,
                            'deduction_title' => $title,
                            'value_type' => $request->deduction_type[$key],
                            'deduction_percent' => $request->deduction_type[$key] == 1 ? $request->deduction_value[$key] : 0,
                            'deduction_money' => $request->deduction_type[$key] == 2 ? $request->deduction_value[$key] : 0,
                            'status' => 1,
                            'trash' => 0,
                            'updated_by' => session('ClientUser'),
                        ];

                        if (!empty($request->deduction_id[$key])) {
                            $deduction = Deductions::where('id', $request->deduction_id[$key])->where('employee_id', $employee->id)->first();
                            if ($deduction) {
                                $deduction->update($deductionData);
                                $deductionIds[] = $deduction->id;
                            } else {
                                $deductionData['created_by'] = session('ClientUser');
                                $newDeduction = Deductions::create($deductionData);
                                $deductionIds[] = $newDeduction->id;
                            }
                        } else {
                            $deductionData['created_by'] = session('ClientUser');
                            $newDeduction = Deductions::create($deductionData);
                            $deductionIds[] = $newDeduction->id;
                        }
                    }
                }
            }

            // Sync: Any records tied to this employee that weren't just updated/created get deleted
            Earnings::where('employee_id', $employee->id)->whereNotIn('id', $earningIds)->delete();
            Deductions::where('employee_id', $employee->id)->whereNotIn('id', $deductionIds)->delete();

            DB::commit();
            return redirect()->route('client.employees')->with('success', 'Employee updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating employee : ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update employee. System Error: ' . $e->getMessage());
        }
    }


    public function resetAllEmployeePasswords()
    {
        try {
            $clientId = session('ClientUser');

            $updated = Employee::where('client_id', $clientId)
                ->update([
                    'password' => Hash::make('123123'),
                ]);

            if ($updated == 0) {
                return back()->with('error', 'No employees found for this client.');
            }

            return back()->with('success', "{$updated} employee passwords reset successfully.");
        } catch (\Exception $e) {
            \Log::error($e->getMessage());

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified employee (Soft Delete -> trash = 1).
     */
    public function destroy($id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $employee->update(['trash' => 1]);

            return redirect()->route('client.employees')->with('success', 'Employee has been successfully moved to trash.');
        } catch (Exception $e) {
            Log::error('Error trashing employee ID ' . $id . ': ' . $e->getMessage());
            return redirect()->route('client.employees')->with('error', 'Failed to delete employee.');
        }
    }

    /**
     * Download CSV Template for Bulk Employee Import
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="employee_import_template.csv"',
        ];

        return response()->stream(function () {
            $out = fopen('php://output', 'w');

            // Added "Date of Birth" right after Gender. Total columns: 17
            fputcsv($out, [
                'Employee ID*',
                'Full Name*',
                'Department*',
                'Designation*',
                'Mobile Number',
                'Email',
                'Gross Salary* (Per Month)',
                'Gender*',
                'Date of Birth (YYYY-MM-DD)',
                'PAN Number',
                'UAN Number',
                'ESI Number',
                'Joining Date* (YYYY-MM-DD)',
                'Bank Name*',
                'Branch Name*',
                'Account Number*',
                'IFSC Code*'
            ]);

            fclose($out);
        }, 200, $headers);
    }

    /**
     * Import Employees via CSV
     */
    public function importEmployeesjok(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        DB::beginTransaction();

        try {
            $handle = fopen($request->file('file')->getRealPath(), 'r');

            // Skip header row
            fgetcsv($handle);

            $imported = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];

            $clientId = session('ClientUser');

            while (($row = fgetcsv($handle)) !== false) {

                // Expanded array_pad to 17 to accommodate DOB
                [
                    $employeeId,
                    $name,
                    $departmentName,
                    $designation,
                    $mobile,
                    $email,
                    $salary,
                    $gender,
                    $dob, // <-- Added DOB
                    $pan,
                    $uan,
                    $esi,
                    $joiningDate,
                    $bankName,
                    $branchName,
                    $accountNumber,
                    $ifscCode
                ] = array_pad($row, 17, null);

                // Trim all values
                $employeeId = trim($employeeId ?? '');
                $name = trim($name ?? '');
                $departmentName = trim($departmentName ?? '');
                $designation = trim($designation ?? '');
                $mobile = trim($mobile ?? '');
                $email = trim($email ?? '');
                $salary = trim($salary ?? '');
                $gender = trim($gender ?? '');
                $dob = trim($dob ?? ''); // <-- Trim DOB
                $pan = trim($pan ?? '');
                $uan = trim($uan ?? '');
                $esi = trim($esi ?? '');
                $joiningDate = trim($joiningDate ?? '');
                $bankName = trim($bankName ?? '');
                $branchName = trim($branchName ?? '');
                $accountNumber = trim($accountNumber ?? '');
                $ifscCode = strtoupper(trim($ifscCode ?? '')); // Ensure IFSC is uppercase

                // Skip completely empty rows
                if (
                    empty($employeeId) &&
                    empty($name) &&
                    empty($departmentName) &&
                    empty($designation)
                ) {
                    continue;
                }

                // Strict validation including the mandatory fields
                if (
                    empty($employeeId) ||
                    empty($name) ||
                    empty($departmentName) ||
                    empty($designation) ||
                    empty($salary) ||
                    empty($gender) ||
                    empty($joiningDate) ||
                    empty($bankName) ||
                    empty($branchName) ||
                    empty($accountNumber) ||
                    empty($ifscCode)
                ) {
                    $skipped++;
                    $errors[] = "Employee {$employeeId} skipped due to missing required fields.";
                    continue;
                }

                /*
                 * ================= STRICT IFSC CODE VALIDATION =================
                 * Standard pattern: 4 Letters, 1 Zero, 6 Alphanumeric characters
                 */
                if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifscCode)) {
                    $skipped++;
                    $errors[] = "Employee {$employeeId} skipped due to invalid IFSC Code format (Must be 11 characters: 4 letters, a zero, 6 alphanumeric).";
                    continue;
                }

                /*
                 * ================= SAFELY PARSE DATES (DOB & Joining) =================
                 */
                try {
                    $parsedJoiningDate = \Carbon\Carbon::parse($joiningDate)->format('Y-m-d');
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Employee {$employeeId} skipped due to invalid Joining Date format. Use YYYY-MM-DD.";
                    continue;
                }

                $parsedDob = null;
                if (!empty($dob)) {
                    try {
                        $parsedDob = \Carbon\Carbon::parse($dob)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $skipped++;
                        $errors[] = "Employee {$employeeId} skipped due to invalid Date of Birth format. Use YYYY-MM-DD.";
                        continue;
                    }
                }

                /*
                 * ================= SALARY CLEANUP =================
                 */
                $salary = preg_replace('/[^\d.]/', '', $salary);

                if ($salary === '' || !is_numeric($salary)) {
                    $skipped++;
                    $errors[] = "Employee {$employeeId} skipped because salary is invalid.";
                    continue;
                }

                $salary = (float) $salary;

                /*
                 * ================= FIND DEPARTMENT =================
                 */
                $department = Department::where('client_id', $clientId)
                    ->where('trash', 0)
                    ->whereRaw(
                        'LOWER(department_name) = ?',
                        [strtolower($departmentName)]
                    )
                    ->first();

                /*
                 * ================= CREATE DEPARTMENT =================
                 */
                if (!$department) {
                    $departmentCode = strtoupper(
                        substr(
                            preg_replace('/[^A-Za-z]/', '', $departmentName),
                            0,
                            3
                        )
                    );

                    if (empty($departmentCode)) {
                        $departmentCode = 'DEP';
                    }

                    $department = Department::create([
                        'department_name' => $departmentName,
                        'department_code' => $departmentCode,
                        'description' => null,
                        'status' => 1,
                        'trash' => 0,
                        'client_id' => $clientId,
                        'created_by' => $clientId,
                    ]);
                }

                /*
                 * ================= FIND EXISTING EMPLOYEE =================
                 */
                $employee = Employee::where('employee_id', $employeeId)
                    ->where('client_id', $clientId)
                    ->first();

                /*
                 * ================= EMPLOYEE DATA =================
                 */
                $employeeData = [
                    'biometric_id' => $employeeId,
                    'employee_name' => $name,
                    'department_id' => $department->id,
                    'designation' => $designation,
                    'mobile_number' => $mobile ?: null,
                    'email' => $email ?: null,
                    'gross_salary' => $salary,
                    'gender' => strtolower($gender) === 'male' ? 1 : 2,
                    'dob' => $parsedDob, // <-- Mapped DOB
                    'pan_number' => $pan ?: null,
                    'uan_number' => $uan ?: null,
                    'esi_number' => $esi ?: null,
                    'joining_date' => $parsedJoiningDate,
                    'bank_name' => $bankName,
                    'bank_branch' => $branchName,
                    'bank_account_number' => $accountNumber,
                    'ifsc_code' => $ifscCode,
                    'status' => 1,
                    'trash' => 0,
                    'client_id' => $clientId,
                ];

                /*
                 * ================= UPDATE EMPLOYEE =================
                 */
                $currentEmployeeId = null;

                if ($employee) {
                    $employee->update(array_merge(
                        $employeeData,
                        [
                            'updated_by' => $clientId,
                        ]
                    ));

                    $updated++;
                    $currentEmployeeId = $employee->id;
                } else {
                    /*
                     * ================= CREATE EMPLOYEE =================
                     */
                    $newEmployee = Employee::create(array_merge(
                        $employeeData,
                        [
                            'employee_id' => $employeeId,
                            'created_by' => $clientId,
                            'password' => Hash::make('123123'),
                        ]
                    ));

                    $imported++;
                    $currentEmployeeId = $newEmployee->id;
                }

                /*
                 * ================= AUTOMATED PT DEDUCTION =================
                 */
                if ($currentEmployeeId) {
                    $ptax = 0;
                    if ($salary >= 15001 && $salary <= 20000) {
                        $ptax = 150;
                    } elseif ($salary > 20000) {
                        $ptax = 200;
                    }

                    $ptDeduction = Deductions::where('employee_id', $currentEmployeeId)
                        ->whereIn('deduction_title', ['Professional Tax', 'PTAX', 'PT', 'Professional Tax (PT)', 'PROF TAX'])
                        ->first();

                    if ($ptax > 0) {
                        if ($ptDeduction) {
                            $ptDeduction->update([
                                'deduction_money' => $ptax,
                                'value_type' => 2,
                                'deduction_title' => 'Professional Tax',
                                'updated_by' => $clientId,
                            ]);
                        } else {
                            Deductions::create([
                                'employee_id' => $currentEmployeeId,
                                'deduction_title' => 'Professional Tax',
                                'value_type' => 2,
                                'deduction_percent' => 0,
                                'deduction_money' => $ptax,
                                'status' => 1,
                                'trash' => 0,
                                'created_by' => $clientId,
                                'updated_by' => $clientId,
                            ]);
                        }
                    } else {
                        // Delete PT if it existed but employee's salary dropped below bounds
                        if ($ptDeduction) {
                            $ptDeduction->delete();
                        }
                    }
                }
            }

            fclose($handle);
            DB::commit();

            return back()->with([
                'success' => "Employees Imported: {$imported}, Updated: {$updated}, Skipped: {$skipped}",
                'import_errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }

            Log::error('Employee Import Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()->with(
                'error',
                'Employee import failed. ' . $e->getMessage()
            );
        }
    }

    public function importEmployees(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        DB::beginTransaction();

        try {
            $handle = fopen($request->file('file')->getRealPath(), 'r');

            // Skip header row
            fgetcsv($handle);

            $imported = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];

            $clientId = session('ClientUser');
            $rowNum = 1;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;

                // Expanded array_pad to 17 to accommodate DOB
                [
                    $employeeId,
                    $name,
                    $departmentName,
                    $designation,
                    $mobile,
                    $email,
                    $salary,
                    $gender,
                    $dob,
                    $pan,
                    $uan,
                    $esi,
                    $joiningDate,
                    $bankName,
                    $branchName,
                    $accountNumber,
                    $ifscCode
                ] = array_pad($row, 17, null);

                // Trim all values
                $employeeId = trim($employeeId ?? '');
                $name = trim($name ?? '');
                $departmentName = trim($departmentName ?? '');
                $designation = trim($designation ?? '');
                $mobile = trim($mobile ?? '');
                $email = trim($email ?? '');
                $salary = trim($salary ?? '');
                $gender = trim($gender ?? '');
                $dob = trim($dob ?? '');
                $pan = trim($pan ?? '');
                $uan = trim($uan ?? '');
                $esi = trim($esi ?? '');
                $joiningDate = trim($joiningDate ?? '');
                $bankName = trim($bankName ?? '');
                $branchName = trim($branchName ?? '');
                $accountNumber = trim($accountNumber ?? '');
                $ifscCode = strtoupper(trim($ifscCode ?? ''));

                // Skip completely empty rows seamlessly
                if ($employeeId === '' && $name === '' && $departmentName === '' && $designation === '') {
                    continue;
                }

                $identifier = $employeeId !== '' ? "ID: {$employeeId}" : "Row {$rowNum}";

                // Strict validation using === '' to prevent rejecting valid '0' entries
                if (
                    $employeeId === '' || $name === '' || $departmentName === '' ||
                    $designation === '' || $salary === '' || $gender === '' ||
                    $joiningDate === '' || $bankName === '' || $branchName === '' ||
                    $accountNumber === '' || $ifscCode === ''
                ) {
                    $skipped++;
                    $errors[] = "Employee {$identifier} skipped: Missing one or more required fields.";
                    continue;
                }

                // Strict IFSC validation
                if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifscCode)) {
                    $skipped++;
                    $errors[] = "Employee {$identifier} skipped: Invalid IFSC Code '{$ifscCode}'. Must be exactly 11 characters (4 letters, 1 zero, 6 alphanumeric).";
                    continue;
                }

                // Identify if Excel corrupted the number into scientific notation
                if (stripos($accountNumber, 'e') !== false) {
                    $skipped++;
                    $errors[] = "Employee {$identifier} skipped: Account Number '{$accountNumber}' is in scientific notation. Please format the 'Account Number' column as 'Text' in Excel before saving the CSV.";
                    continue;
                }

                // Ensure the account number contains exclusively digits
                if (!preg_match('/^\d+$/', $accountNumber)) {
                    $skipped++;
                    $errors[] = "Employee {$identifier} skipped: Account Number must contain only numbers. No spaces or special characters allowed.";
                    continue;
                }

                // Safely parse Dates
                try {
                    $parsedJoiningDate = \Carbon\Carbon::parse($joiningDate)->format('Y-m-d');
                } catch (\Exception $e) {
                    $skipped++;
                    $errors[] = "Employee {$identifier} skipped: Invalid Joining Date format. Use YYYY-MM-DD.";
                    continue;
                }

                $parsedDob = null;
                if ($dob !== '') {
                    try {
                        $parsedDob = \Carbon\Carbon::parse($dob)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $skipped++;
                        $errors[] = "Employee {$identifier} skipped: Invalid Date of Birth format. Use YYYY-MM-DD.";
                        continue;
                    }
                }

                // Salary Cleanup
                $salaryClean = preg_replace('/[^\d.]/', '', $salary);
                if ($salaryClean === '' || !is_numeric($salaryClean)) {
                    $skipped++;
                    $errors[] = "Employee {$identifier} skipped: Invalid numeric salary.";
                    continue;
                }
                $salaryClean = (float) $salaryClean;

                // Find or Create Department
                $department = Department::where('client_id', $clientId)
                    ->where('trash', 0)
                    ->whereRaw('LOWER(department_name) = ?', [strtolower($departmentName)])
                    ->first();

                if (!$department) {
                    $departmentCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $departmentName), 0, 3));
                    if (empty($departmentCode))
                        $departmentCode = 'DEP';

                    $department = Department::create([
                        'department_name' => $departmentName,
                        'department_code' => $departmentCode,
                        'description' => null,
                        'status' => 1,
                        'trash' => 0,
                        'client_id' => $clientId,
                        'created_by' => $clientId,
                    ]);
                }

                $employee = Employee::where('employee_id', $employeeId)->where('client_id', $clientId)->first();

                $employeeData = [
                    'biometric_id' => $employeeId,
                    'employee_name' => $name,
                    'department_id' => $department->id,
                    'designation' => $designation,
                    'mobile_number' => $mobile ?: null,
                    'email' => $email ?: null,
                    'gross_salary' => $salaryClean,
                    'gender' => strtolower($gender) === 'male' ? 1 : 2,
                    'dob' => $parsedDob,
                    'pan_number' => $pan ?: null,
                    'uan_number' => $uan ?: null,
                    'esi_number' => $esi ?: null,
                    'joining_date' => $parsedJoiningDate,
                    'bank_name' => $bankName,
                    'bank_branch' => $branchName,
                    'bank_account_number' => $accountNumber,
                    'ifsc_code' => $ifscCode,
                    'status' => 1,
                    'trash' => 0,
                    'client_id' => $clientId,
                ];

                $currentEmployeeId = null;

                if ($employee) {
                    $employee->update(array_merge($employeeData, ['updated_by' => $clientId]));
                    $updated++;
                    $currentEmployeeId = $employee->id;
                } else {
                    $newEmployee = Employee::create(array_merge($employeeData, [
                        'employee_id' => $employeeId,
                        'created_by' => $clientId,
                        'password' => \Illuminate\Support\Facades\Hash::make('123123'),
                    ]));
                    $imported++;
                    $currentEmployeeId = $newEmployee->id;
                }

                // Professional Tax calculation
                if ($currentEmployeeId) {
                    $ptax = 0;
                    if ($salaryClean >= 15001 && $salaryClean <= 20000) {
                        $ptax = 150;
                    } elseif ($salaryClean > 20000) {
                        $ptax = 200;
                    }

                    $ptDeduction = Deductions::where('employee_id', $currentEmployeeId)
                        ->whereIn('deduction_title', ['Professional Tax', 'PTAX', 'PT', 'Professional Tax (PT)', 'PROF TAX'])
                        ->first();

                    if ($ptax > 0) {
                        if ($ptDeduction) {
                            $ptDeduction->update([
                                'deduction_money' => $ptax,
                                'value_type' => 2,
                                'deduction_title' => 'Professional Tax',
                                'updated_by' => $clientId,
                            ]);
                        } else {
                            Deductions::create([
                                'employee_id' => $currentEmployeeId,
                                'deduction_title' => 'Professional Tax',
                                'value_type' => 2,
                                'deduction_percent' => 0,
                                'deduction_money' => $ptax,
                                'status' => 1,
                                'trash' => 0,
                                'created_by' => $clientId,
                                'updated_by' => $clientId,
                            ]);
                        }
                    } else {
                        if ($ptDeduction)
                            $ptDeduction->delete();
                    }
                }
            }

            fclose($handle);
            DB::commit();

            return back()->with([
                'success' => "Import Complete. Imported: {$imported} | Updated: {$updated} | Skipped: {$skipped}",
                'import_errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($handle) && is_resource($handle))
                fclose($handle);
            \Illuminate\Support\Facades\Log::error('Employee Import Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            return back()->with('error', 'Employee import failed. ' . $e->getMessage());
        }
    }
}