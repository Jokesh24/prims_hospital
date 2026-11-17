<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeave extends Model
{
    use HasFactory;

    protected $table = 'rl_employee_leaves_tbl';

    protected $fillable = [
        'client_id',
        'employee_id',
        'leave_type_id',
        'remarks',
        'status',
        'trash',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => 'integer',
        'trash' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function dates()
    {
        return $this->hasMany(EmployeeLeaveDate::class, 'leave_id');
    }

    // Add this inside App\Models\EmployeeLeave class:

    public function logs()
    {
        return $this->hasMany(LeaveStatusLog::class, 'leave_id');
    }

    public function latestLog()
    {
        return $this->hasOne(LeaveStatusLog::class, 'leave_id')->latestOfMany();
    }

    public function flow()
    {
        return $this->belongsTo(LeaveFlow::class, 'flow_id');
    }

    public function currentApprover()
    {
        return $this->belongsTo(Employee::class, 'current_approver_emp_id');
    }

    public function approvalLogs()
    {
        return $this->hasMany(LeaveApprovalLog::class, 'leave_id')->orderBy('created_at', 'desc');
    }
}