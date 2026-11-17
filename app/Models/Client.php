<?php

namespace App\Models;

// 1. You must import the Authenticatable class
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// 2. Change "extends Model" to "extends Authenticatable"
class Client extends Authenticatable
{
    use Notifiable;

    protected $table = 'rl_clients_tbl';

    protected $fillable = [
        'parent_id',
        'client_name',
        'client_email',
        'client_mobile',
        'client_gst',
        'client_pan',
        'logo',
        'city',
        'address',
        'client_password',
        'created_by',
        'updated_by',
        'trash',
        'status'
    ];

    // Hide the password from array/JSON conversions
    protected $hidden = [
        'client_password',
    ];

    /**
     * Override this method to tell Laravel the EXACT name of the password column in your database.
     * This is required for Laravel 10/11/12 to correctly map to 'client_password'.
     */
    public function getAuthPasswordName()
    {
        return 'client_password';
    }

    /**
     * Fallback for older Laravel auth checks or custom guards that fetch the value directly.
     */
    public function getAuthPassword()
    {
        return $this->client_password;
    }






















































    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }


    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'client_id');
    }
}
