<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CtScanController extends Controller
{
    public function store(Request $request)
    {
        
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'patient_id' => 'required|exists:users,id',
            'file_path' => 'required|string',
        ]);

        
        $ctScan = CtScan::create([
            'doctor_id' => $request->input('doctor_id'),
            'patient_id' => $request->input('patient_id'),
            'file_path' => $request->input('file_path'), 
        ]);

        
        return response()->json($ctScan, 201);
    }
}
