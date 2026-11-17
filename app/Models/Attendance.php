<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
Use App\Models\Employee;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'rl_attendance_tbl';

    protected $fillable = [
        'emp_id',
        'punch_in_time',
        'punch_out_time',
        'client_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'punch_in_time'  => 'datetime',
        'punch_out_time' => 'datetime',
    ];


    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}