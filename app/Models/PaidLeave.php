<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class PaidLeave extends Model
{
    use HasFactory;

    protected $table = 'rl_paid_leaves_tbl';

    protected $fillable = [
        'client_id',
        'department_id',
        'employee_id',      // ADDED: employee-wise policy support
        'week_off_type',
        'week_offs',
        'monthly',
        'yearly',
        'status',
        'trash',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'week_offs' => 'integer',
        'monthly' => 'integer',
        'yearly' => 'integer',
        'status' => 'integer',
        'trash' => 'integer',
        'employee_id' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // ADDED: employee relation for the new employee-wise policy
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * ADDED: Resolve the effective PaidLeave policy for ONE employee.
     * Priority: employee-specific policy -> department-wide fallback policy -> null.
     * Use this for single-employee lookups (e.g. AJAX balance check, single payroll calc).
     */
    public static function resolveFor(Employee $employee): ?self
    {
        $employeePolicy = self::where('client_id', $employee->client_id)
            ->where('employee_id', $employee->id)
            ->where('status', 1)
            ->where('trash', 0)
            ->latest('id')
            ->first();

        if ($employeePolicy) {
            return $employeePolicy;
        }

        if ($employee->department_id) {
            return self::where('client_id', $employee->client_id)
                ->whereNull('employee_id')
                ->where('department_id', $employee->department_id)
                ->where('status', 1)
                ->where('trash', 0)
                ->latest('id')
                ->first();
        }

        return null;
    }

    /**
     * ADDED: Bulk-resolve the effective PaidLeave policy for a collection of employees,
     * in exactly 2 queries total (avoids N+1 when looping over many employees, e.g. the
     * monthly attendance grid or payroll export).
     *
     * @param int $clientId
     * @param \Illuminate\Support\Collection $employees
     * @return array  [employee_id => PaidLeave|null]
     */
    public static function resolveMapForEmployees($clientId, $employees): array
    {
        $employeeIds = $employees->pluck('id')->all();
        $departmentIds = $employees->pluck('department_id')->filter()->unique()->values()->all();

        $employeePolicies = self::where('client_id', $clientId)
            ->whereIn('employee_id', $employeeIds)
            ->where('status', 1)
            ->where('trash', 0)
            ->orderBy('id')
            ->get()
            ->keyBy('employee_id');

        $departmentPolicies = self::where('client_id', $clientId)
            ->whereNull('employee_id')
            ->whereIn('department_id', $departmentIds)
            ->where('status', 1)
            ->where('trash', 0)
            ->orderBy('id')
            ->get()
            ->keyBy('department_id');

        $map = [];
        foreach ($employees as $emp) {
            $map[$emp->id] = $employeePolicies->get($emp->id)
                ?? ($emp->department_id ? $departmentPolicies->get($emp->department_id) : null);
        }

        return $map;
    }
}