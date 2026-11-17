<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;

// 1. Add these two imports
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;

class Employee extends Model implements Authenticatable
{
    use HasFactory, AuthenticatableTrait;

    protected $table = 'rl_employees_tbl';

    // Mass assignable attributes
    protected $fillable = [
        'client_id',
        'employee_id',
        'employee_name',
        'department_id',
        'sub_department_id',
        'designation_id',
        'email',
        'mobile_number',
        'gross_salary',
        'password',
        'joining_date',
        'photo',
        'dob',
        'gender',
        'marital_status',
        'marriage_date',
        'inactive_comments',
        'father_spouse_name',
        'mother_name',
        'blood_group',
        'alternative_mobile_number',
        'relation_with_alt_mobile',
        'present_address',
        'permanent_address',
        'aadhaar_number',
        'aadhaar_proof',
        'pan_number',
        'pan_proof',
        'uan_number',
        'esi_number',
        'highest_qualification',
        'qualification_proof',
        'bank_name',
        'bank_branch',
        'bank_account_number',
        'ifsc_code',
        'account_type',
        'status',
        'trash',
        'created_by',
        'updated_by',
        'tds_type',
        'tds_percentage',
        'tds_amount',
        'tax_regime',
        'payment_mode',
        'biometric_id',
        'pf_applicable'
    ];

    // Cast dates for easy Carbon manipulation
    protected $casts = [
        'joining_date' => 'date',
        'dob' => 'date',
        'marriage_date' => 'date',
        'gross_salary' => 'decimal:2',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Relationship: An employee belongs to a department.
     */
    public function department()
    {
        // Assumes your Department model is at App\Models\Department
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }


    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'emp_id');
    }

    // Add this new relationship method at the bottom of the class:
    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id');
    }

    public function skills()
    {
        // Only load active, non-trashed skills
        return $this->hasMany(EmployeeSkill::class, 'employee_id')
            ->where('status', 1)
            ->where('trash', 0);
    }

    public function designationInfo()
    {
        return $this->belongsTo(\App\Models\Designation::class, 'designation_id', 'id');
    }

    public function subDepartment()
    {
        return $this->belongsTo(\App\Models\SubDepartment::class, 'sub_department_id', 'id');
    }

    /**
     * Relationship: Login access permissions for the employee.
     */
    public function loginAccess()
    {
        return $this->hasOne(\App\Models\EmployeeLoginAccess::class, 'employee_id', 'id');
    }

}