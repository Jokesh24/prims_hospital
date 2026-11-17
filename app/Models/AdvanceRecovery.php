<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvanceRecovery extends Model
{
    use HasFactory;

    protected $table = 'rl_advance_recoveries_tbl';

    protected $fillable = [

        'client_id',

        'advance_id',

        'employee_id',

        'payroll_id',

        'recovery_date',

        'recovered_amount',

        'balance_before',

        'balance_after',

        'recovery_type',

        'payment_mode',

        'reference_number',

        'remarks',

        'created_by',

        'updated_by',

    ];



    /*  Relationships    */

    public function advance()
    {
        return $this->belongsTo(
            EmployeeAdvance::class,
            'advance_id'
        );
    }

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
}