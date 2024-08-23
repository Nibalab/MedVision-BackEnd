<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CtScan extends Model
{
    protected $fillable = ['doctor_id', 'patient_id', 'file_path'];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function threeDModel()
    {
        return $this->hasOne(ThreeDModel::class);
    }

    public function report()
    {
        return $this->hasOne(Report::class);
    }
}
