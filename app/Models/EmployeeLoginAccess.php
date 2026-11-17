<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLoginAccess extends Model
{
    use HasFactory;

    protected $table = 'rl_employee_login_access_tbl';

    protected $fillable = [
        'client_id',
        'employee_id',
        'accessible_sections',
        'status',
        'trash',
        'created_by',
        'updated_by'
    ];

    // Automatically cast the JSON text field to a PHP array
    protected $casts = [
        'accessible_sections' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}