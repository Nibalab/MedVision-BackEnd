<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Annotation extends Model
{
    use HasFactory;
    
    protected $fillable = ['model_id', 'doctor_id', 'content', 'position'];

    public function threeDModel()
    {
        return $this->belongsTo(ThreeDModel::class, 'model_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
