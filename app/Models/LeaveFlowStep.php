<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveFlowStep extends Model
{
    protected $table = 'rl_leave_flow_steps_tbl';

    protected $fillable = [
        'flow_id',
        'approver_emp_id',
        'step_order',
        'is_final'
    ];

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_emp_id');
    }
}