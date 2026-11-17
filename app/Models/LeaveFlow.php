<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveFlow extends Model
{
    use HasFactory;

    protected $table = 'rl_leave_flows_tbl';

    protected $fillable = [
        'client_id',
        'department_id',
        'flow_name',
        'status',
        'trash',
        'created_by',
        'updated_by'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function steps()
    {
        return $this->hasMany(LeaveFlowStep::class, 'flow_id')->orderBy('step_order', 'asc');
    }
}