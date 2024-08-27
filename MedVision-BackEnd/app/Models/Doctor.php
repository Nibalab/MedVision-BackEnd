<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'specialization', 'bio', 'contact_number', 'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sentMessages()
    {
        return $this->morphMany(Message::class, 'sender');
    }

    public function receivedMessages()
    {
        return $this->morphMany(Message::class, 'receiver');
    }

    public function ctScans()
    {
        return $this->hasMany(CtScan::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
