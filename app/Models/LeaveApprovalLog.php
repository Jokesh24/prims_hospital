<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApprovalLog extends Model
{
    protected $table = 'rl_leave_approval_logs_tbl';

    protected $fillable = [
        'leave_id',
        'approver_emp_id',
        'action',
        'remarks'
    ];

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_emp_id');
    }
}