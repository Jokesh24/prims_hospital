<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeOTDate extends Model
{
    use HasFactory;

    protected $table = 'rl_employee_ot_dates_tbl';

    protected $fillable = [
        'client_id',
        'employee_id',
        'ot_id',
        'ot_date',
        'ot_amount',
        'remarks',
        'status',
        'trash',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'ot_date' => 'date',
        'ot_amount' => 'decimal:2',
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

    public function ot()
    {
        return $this->belongsTo(EmployeeOT::class, 'ot_id');
    }
}
