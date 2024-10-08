<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    protected $fillable = ['patient_id', 'doctor_id', 'appointment_date', 'appointment_time', 'status'];

    // Relationship to the patient
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    // Relationship to the doctor
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}