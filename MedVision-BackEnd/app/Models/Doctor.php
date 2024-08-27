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

    public function messages()
    {
        return $this->hasMany(Message::class);
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

