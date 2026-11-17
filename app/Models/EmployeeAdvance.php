<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAdvance extends Model
{
    use HasFactory;

    protected $table = 'rl_employee_advances_tbl';

    protected $fillable = [

        'client_id',

        'employee_id',

        'advance_date',

        'advance_amount',

        'recovered_amount',

        'balance_amount',

        'recovery_type',

        'monthly_recovery_amount',

        'payment_mode',

        'reference_number',

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
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function recoveries()
    {
        return $this->hasMany(
            AdvanceRecovery::class,
            'advance_id'
        )->orderByDesc('recovery_date');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}