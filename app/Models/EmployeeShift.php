<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeShift extends Model
{
    use HasFactory;

    protected $table = 'rl_employee_shifts_tbl';

    protected $fillable = [
        'client_id',
        'employee_id',
        'designation_shift_id',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the employee associated with this shift assignment.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Get the shift details associated with this assignment.
     */
    public function shift()
    {
        return $this->belongsTo(DesignationShift::class, 'designation_shift_id', 'id');
    }
}