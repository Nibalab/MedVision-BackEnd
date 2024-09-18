<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::all();
        return response()->json($reports);
    }

    public function store(Request $request)
{
    $request->validate([
        'ct_scan_id' => 'nullable|exists:ct_scans,id',
        'doctor_id' => 'required|exists:users,id',
        'patient_id' => 'required|exists:users,id',
        'report_document' => 'required|file|mimes:pdf,doc,docx|max:2048', 
    ]);
    if ($request->hasFile('report_document')) {
        $filePath = $request->file('report_document')->store('public/reports');
    
        
        dd($filePath); 
       
        $report = Report::create([
            'ct_scan_id' => $request->ct_scan_id, 
            'doctor_id' => $request->doctor_id,
            'patient_id' => $request->patient_id,
            'file_path' => $filePath, 
        ]);
    
        return response()->json($report, 201);
    } else {
        
        return response()->json(['error' => 'File upload failed'], 400);
    }
}

    public function show($id)
    {
        $report = Report::findOrFail($id);
        return response()->json($report);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'ct_scan_id' => 'sometimes|required|exists:ct_scans,id',
            'doctor_id' => 'sometimes|required|exists:users,id',
            'patient_id' => 'sometimes|required|exists:users,id',
            'report_document' => 'sometimes|required|file|mimes:pdf,doc,docx|max:2048', 
        ]);

        $report = Report::findOrFail($id);

        if ($request->hasFile('report_document')) {
            if ($report->file_path && Storage::exists($report->file_path)) {
                Storage::delete($report->file_path);
            }
            $filePath = $request->file('report_document')->store('public/reports');
            $report->file_path = $filePath;
        }

        $report->update($request->except('report_document'));

        return response()->json($report);
    }

    public function destroy($id)
    {
        $report = Report::findOrFail($id);

        if ($report->file_path && Storage::exists($report->file_path)) {
            Storage::delete($report->file_path);
        }

        $report->delete();
        return response()->json(['message' => 'Report deleted successfully']);
    }

    public function downloadReport($patientId)
{
    $report = Report::where('patient_id', $patientId)->latest()->first();

    if (!$report) {
        return response()->json(['error' => 'Report not found for this patient'], 404);
    }

    $user = auth()->user();
    if ($user->id !== $report->patient_id && $user->id !== $report->doctor_id) {
        return response()->json(['error' => 'Unauthorized access'], 403);
    }

    if ($report->file_path && Storage::exists($report->file_path)) {
        return response()->download(storage_path('app/' . $report->file_path));
    }

    return response()->json(['error' => 'File not found'], 404);
}


    public function storeReport(Request $request, $patientId)
    {
        $request->validate([
            'report_document' => 'required|file|mimes:pdf,doc,docx|max:2048', 
        ]);
        if ($request->hasFile('report_document')) {
            $filePath = $request->file('report_document')->store('public/reports');


            $report = Report::create([
                'patient_id' => $patientId,
                'file_path' => $filePath,
                'doctor_id' => auth()->id(), 
            ]);

            return response()->json(['message' => 'Report uploaded successfully', 'report' => $report], 201);
        }

        return response()->json(['error' => 'File upload failed'], 400);
    }

    public function getReports(Request $request) {
        $user = auth()->user();
        $reports = Report::where('patient_id', $user->id)
            ->with('doctor')
            ->get();
        
        return response()->json(['reports' => $reports]);
    }
    
    public function getLatestReport(Request $request) {
        $user = auth()->user();
        
        $latestReport = Report::where('patient_id', $user->id)
            ->with('doctor') 
            ->latest()       
            ->first();   
        if (!$latestReport) {
            return response()->json(['error' => 'No report found for this patient'], 404);
        }
        
        return response()->json(['latestReport' => $latestReport], 200);
    }
    

}
