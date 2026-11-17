<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    // Map to your specific table
    protected $table = 'rl_departments_tbl';

    // Allow mass assignment for these columns
    protected $fillable = [
        'department_name',
        'department_code',
        'flexible_timing',
        'no_deductions',
        'priority',
        'description',
        'status',
        'trash',
        'client_id',
        'created_by',
        'updated_by'
    ];

    public function employees()
    {
        // Assuming 'department_id' is the foreign key in rl_employees_tbl
        return $this->hasMany(Employee::class, 'department_id', 'id');
    }

    public function holidays()
    {
        return $this->belongsToMany(
            Holiday::class,
            'rl_holiday_departments_tbl',
            'department_id',
            'holiday_id'
        )->withTimestamps();
    }

    // Add this inside the Department class:
    public function leavePolicy()
    {
        return $this->hasOne(PaidLeave::class, 'department_id')->where('trash', 0);
    }

    public function subDepartments()
    {
        return $this->hasMany(SubDepartment::class, 'department_id', 'id')->where('trash', 0);
    }


}