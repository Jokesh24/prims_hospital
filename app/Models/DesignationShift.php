<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesignationShift extends Model
{
    use HasFactory;

    protected $table = 'rl_designation_shifts_tbl';

    protected $fillable = [
        'designation_id',
        'shift_name',
        'login_time',
        'logout_time',
        'grace_time',
        'grace_time_for_logout',
        'is_overnight',
    ];

    public function designation()
    {
        return $this->belongsTo(Designation::class, 'designation_id', 'id');
    }
}