<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveStatusLog extends Model
{
    use HasFactory;

    public $timestamps = false; // Only uses created_at
    protected $table = 'rl_leave_status_logs_tbl';

    protected $fillable = [
        'leave_date_id',
        'leave_id',
        'previous_status',
        'new_status',
        'changed_by',
        'changed_by_guard',
        'reason',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];
}