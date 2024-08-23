<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThreeDModel extends Model
{
    protected $fillable = ['ct_scan_id', 'model_path'];

    public function ctScan()
    {
        return $this->belongsTo(CtScan::class);
    }

    public function annotations()
    {
        return $this->hasMany(Annotation::class, 'model_id');
    }
}
