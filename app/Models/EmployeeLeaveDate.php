<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLeaveDate extends Model
{
    use HasFactory;

    protected $table = 'rl_employee_leave_dates_tbl';

    protected $fillable = [
        'leave_id',
        'date',
        'is_paid'
    ];

    protected $casts = [
        'date' => 'date',
        'is_paid' => 'integer',
    ];

    public function leave()
    {
        return $this->belongsTo(EmployeeLeave::class, 'leave_id');
    }

    public function logs()
    {
        return $this->hasMany(LeaveStatusLog::class, 'leave_date_id');
    }
}