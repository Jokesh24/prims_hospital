<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $table = 'rl_leaves_tbl';
    protected $guarded = ['id'];

    // Status Constants
    const STATUS_APPLIED = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_ACCEPTED = 3;
    const STATUS_CANCELLED = 4;
    const STATUS_REJECTED = 5;

    // Status Mapping for easy display
    public static function getStatusMap()
    {
        return [
            self::STATUS_APPLIED => 'Applied',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    // Helper to get text status
    public function getStatusLabelAttribute()
    {
        return self::getStatusMap()[$this->leave_status] ?? 'Unknown';
    }

    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'employee_id');
    }

    public function scopeAppliedOrPending($query)
    {
        // STATUS_APPLIED = 1, STATUS_IN_PROGRESS = 2
        return $query->whereIn('leave_status', [self::STATUS_APPLIED, self::STATUS_IN_PROGRESS]);
    }

    public function scopeCancelledOrRejected($query)
    {
        // STATUS_CANCELLED = 4, STATUS_REJECTED = 5
        return $query->whereIn('leave_status', [self::STATUS_CANCELLED, self::STATUS_REJECTED]);
    }
}