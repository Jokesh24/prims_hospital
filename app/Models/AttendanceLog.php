<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $table = 'rl_attendance_logs_tbl';

    protected $fillable = [

        'client_id',

        'emp_id',

        'attendance_id',

        'punch_time',
    ];

    protected $casts = [

        'punch_time' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(
            Employee::class,
            'emp_id'
        );
    }
}