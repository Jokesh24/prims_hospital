<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientLoginLog extends Model
{
    use HasFactory;

    // Explicitly define the table name
    protected $table = 'rl_client_loginlogs_tbl';

    // Mass assignable attributes
    protected $fillable = [
        'client_id',
        'ip',
        'login_datetime',
        'status',
        'log_comment'
    ];

    // Ensure proper casting
    protected $casts = [
        'login_datetime' => 'datetime',
        'status' => 'integer',
    ];
}