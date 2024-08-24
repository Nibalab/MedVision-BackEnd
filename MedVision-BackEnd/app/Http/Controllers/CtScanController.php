<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CtScanController extends Controller
{
    public function index()
    {
        $ctScans = CtScan::all();
        return response()->json($ctScans);
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'patient_id' => 'required|exists:users,id',
            'file' => 'required|file|mimes:jpg,jpeg,png,bmp,tiff|max:10240',
        ]);

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('ct_scans', 'public');

            $ctScan = CtScan::create([
                'doctor_id' => $request->input('doctor_id'),
                'patient_id' => $request->input('patient_id'),
                'file_path' => $filePath,
            ]);

            return response()->json($ctScan, 201);
        }

        return response()->json(['error' => 'File upload failed'], 500);
    }

    public function show($id)
    {
        $ctScan = CtScan::findOrFail($id);
        return response()->json($ctScan);
    }

    public function destroy($id)
    {
        $ctScan = CtScan::findOrFail($id);
        $ctScan->delete();
        return response()->json(['message' => 'CT Scan deleted successfully']);
    }
}
