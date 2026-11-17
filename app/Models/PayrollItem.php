<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory;

    protected $table = 'rl_payroll_items_tbl';

    protected $fillable = [

        'payroll_id',

        'type',

        'title',

        'value_type',

        'configured_value',

        'calculated_amount'
    ];

    public function payroll()
    {
        return $this->belongsTo(
            Payroll::class,
            'payroll_id'
        );
    }
}
