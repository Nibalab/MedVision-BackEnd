<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'gender', 'profile_picture'];

    /**
     * Scope a query to only include patients.
     */
    public function scopePatients($query)
    {
        return $query->where('role', 'patient');
    }

    /**
     * Scope a query to only include admins.
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Get the patient's messages.
     */
    public function sentMessages()
    {
        return $this->morphMany(Message::class, 'sender');
    }

    /**
     * Get the messages sent to the patient.
     */
    public function receivedMessages()
    {
        return $this->morphMany(Message::class, 'receiver');
    }

    /**
     * Get the admin logs for the user if they are an admin.
     */
    public function adminLogs()
    {
        return $this->hasMany(AdminLog::class, 'admin_id');
    }

    /**
     * JWT Methods
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
