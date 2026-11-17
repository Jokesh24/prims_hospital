<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $table = 'rl_leave_types_tbl';

    protected $fillable = [
        'client_id',
        'name',
        'status',
        'trash'
    ];

    protected $casts = [
        'status' => 'integer',
        'trash'  => 'integer',
    ];

    public function leaves()
    {
        return $this->hasMany(EmployeeLeave::class, 'leave_type_id');
    }
}