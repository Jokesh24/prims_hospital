<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'rl_payrolls_tbl';

    protected $fillable = [

        'client_id',
        'employee_id',

        'month',
        'year',

        'total_days',
        'working_days',

        'present_days',
        'absent_days',
        'half_days',

        'paid_leave_days',
        'lop_days',

        'gross_salary',

        'total_earnings',
        'total_deductions',

        'lop_amount',
        'week_offs',
        'holidays',
        'weekoff_absence_offset',

        'net_salary',

        'payroll_status',

        'created_by',
        'updated_by'
    ];

    public function employee()
    {
        return $this->belongsTo(
            Employee::class,
            'employee_id'
        );
    }

    public function items()
    {
        return $this->hasMany(
            PayrollItem::class,
            'payroll_id'
        );
    }

    public function earnings()
    {
        return $this->hasMany(
            PayrollItem::class,
            'payroll_id'
        )->where(
                'item_type',
                'earning'
            );
    }

    public function deductions()
    {
        return $this->hasMany(
            PayrollItem::class,
            'payroll_id'
        )->where(
                'item_type',
                'deduction'
            );
    }
}
