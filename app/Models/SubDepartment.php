<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubDepartment extends Model
{
    use HasFactory;

    protected $table = 'rl_sub_departments_tbl';

    protected $fillable = [
        'client_id',
        'department_id',
        'sub_department_name',
        'status',
        'trash',
        'created_by',
        'updated_by'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function designations()
    {
        return $this->hasMany(Designation::class, 'sub_department_id', 'id');
    }
}