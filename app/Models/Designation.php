<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    protected $table = 'rl_designations_tbl';

    protected $fillable = [
        'client_id',
        'department_id',
        'sub_department_id',
        'designation_name',
        'shift_type',
        'status',
        'trash',
        'created_by',
        'updated_by',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function shifts()
    {
        return $this->hasMany(DesignationShift::class, 'designation_id', 'id');
    }
    
    // 2. Add the relationship
    public function subDepartment()
    {
        return $this->belongsTo(SubDepartment::class, 'sub_department_id', 'id');
    }
}