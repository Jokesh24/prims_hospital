<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $table = 'rl_holidays_tbl';

    protected $fillable = [
        'client_id',
        'date',
        'day',
        'title',
        'description',
        'type',
        'status',
        'trash',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date' => 'date',

        /* * Type Mapping:
         * 1 = General Holiday
         * 2 = Public Holiday
         * 3 = Company Holiday
         * 4 = Optional Holiday 
         */
        'type' => 'integer',

        /* * Status Mapping:
         * 1 = Active
         * 0 = Inactive 
         */
        'status' => 'integer',

        /* * Trash Mapping:
         * 1 = Trashed (Soft Deleted)
         * 0 = Not Trashed (Active Record) 
         */
        'trash' => 'integer',
    ];

    public function departments()
    {
        return $this->belongsToMany(
            Department::class,
            'rl_holiday_departments_tbl',
            'holiday_id',
            'department_id'
        )->withTimestamps();
    }
}