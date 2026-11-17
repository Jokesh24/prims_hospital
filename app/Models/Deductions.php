<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deductions extends Model
{
    protected $table = 'rl_deductions_tbl';

    protected $fillable = [
        'employee_id',
        'deduction_title',
        'value_type',
        'deduction_percent',
        'deduction_money',
        'status',
        'trash',
        'created_by',
        'updated_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getTitleAttribute()
    {
        return $this->deduction_title;
    }

    public function getPercentAttribute()
    {
        return $this->deduction_percent;
    }

    public function getAmountAttribute()
    {
        return $this->deduction_money;
    }


   
    public function calculateAmount($baseAmount)
    {
        if ($this->value_type == 1) {
            return ($baseAmount * $this->deduction_percent) / 100;
        }

        return $this->deduction_money;
    }
}
