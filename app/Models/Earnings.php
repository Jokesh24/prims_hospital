<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Earnings extends Model
{
    protected $table = 'rl_earnings_tbl';

    protected $fillable = [
        'employee_id',
        'earnings_title',
        'value_type',
        'earnings_percent',
        'earnings_money',
        'status',
        'trash',
        'created_by',
        'updated_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors (Clean Naming for Blade & Logic)
    |--------------------------------------------------------------------------
    */

    public function getTitleAttribute()
    {
        return $this->earnings_title;
    }

    public function getPercentAttribute()
    {
        return $this->earnings_percent;
    }

    public function getAmountAttribute()
    {
        return $this->earnings_money;
    }

   

  

    public function calculateAmount($baseAmount)
    {
        if ($this->value_type == 1) {
            return ($baseAmount * $this->earnings_percent) / 100;
        }

        return $this->earnings_money;
    }
}
