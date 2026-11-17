<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeOT extends Model
{
    use HasFactory;

    protected $table = 'rl_employee_ot_tbl';

    protected $fillable = [

        'client_id',

        'employee_id',

        'payroll_id',

        'ot_month',

        'ot_year',

        'ot_days',

        'ot_hours',

        'ot_rate',

        'ot_amount',

        'ot_source',

        'remarks',

        'status',

        'trash',

        'created_by',

        'updated_by',

    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function employee()
    {
        return $this->belongsTo(
            Employee::class,
            'employee_id'
        );
    }

    public function payroll()
    {
        return $this->belongsTo(
            Payroll::class,
            'payroll_id'
        );
    }

    /**
     * Individual OT dates captured from the date-wise "Assign OT" screen.
     * A 'manual' OT entry has no rows here.
     */
    public function dates()
    {
        return $this->hasMany(
            EmployeeOTDate::class,
            'ot_id'
        )
            ->where('trash', 0)
            ->orderBy('ot_date');
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater()
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }
}