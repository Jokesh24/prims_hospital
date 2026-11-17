<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeTiming extends Model
{
    use HasFactory;

    protected $table = 'rl_office_timings_tbl';

    protected $fillable = [
        'client_id',
        'dept_id',  
        'start_time',
        'end_time',
        'grace_time',
        'half_day_limit',
        'created_by',
        'updated_by'
    ];

    protected $casts = [

        'grace_time' => 'integer',
        'half_day_limit' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(
            Client::class,
            'client_id'
        );
    }
    public function department()
    {
        return $this->belongsTo(
            Department::class,
            'dept_id'
        );
    }
}
