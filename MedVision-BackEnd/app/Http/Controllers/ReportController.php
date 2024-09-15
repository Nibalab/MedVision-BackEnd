<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    // Fetch all reports (Optional: You might want to paginate this in the future)
    public function index()
    {
        $reports = Report::all();
        return response()->json($reports);
    }

    // Store a new report (with document upload)
    public function store(Request $request)
{
    // Validate the request inputs
    $request->validate([
        'ct_scan_id' => 'nullable|exists:ct_scans,id',
        'doctor_id' => 'required|exists:users,id',
        'patient_id' => 'required|exists:users,id',
        'report_document' => 'required|file|mimes:pdf,doc,docx|max:2048', // Validate the document type
    ]);

    // Handle the file upload
    if ($request->hasFile('report_document')) {
        // Store the file and retrieve the file path
        $filePath = $request->file('report_document')->store('public/reports');
    
        // Debug to check if the file path is correct
        dd($filePath); // This will stop the execution and output the file path
    
        // Save the report information including the file path
        $report = Report::create([
            'ct_scan_id' => $request->ct_scan_id, // Optional field
            'doctor_id' => $request->doctor_id,
            'patient_id' => $request->patient_id,
            'file_path' => $filePath,  // Save the file path to the database
        ]);
    
        return response()->json($report, 201);
    } else {
        // Return error response if file upload fails
        return response()->json(['error' => 'File upload failed'], 400);
    }
}


    // Show a specific report (without exposing the file path directly)
    public function show($id)
    {
        $report = Report::findOrFail($id);
        return response()->json($report);
    }

    // Update report details (if needed for other metadata, not the file)
    public function update(Request $request, $id)
    {
        $request->validate([
            'ct_scan_id' => 'sometimes|required|exists:ct_scans,id',
            'doctor_id' => 'sometimes|required|exists:users,id',
            'patient_id' => 'sometimes|required|exists:users,id',
            'report_document' => 'sometimes|required|file|mimes:pdf,doc,docx|max:2048', // Optional file update
        ]);

        $report = Report::findOrFail($id);

        // Handle file update if a new report document is provided
        if ($request->hasFile('report_document')) {
            // Delete the old file if it exists
            if ($report->file_path && Storage::exists($report->file_path)) {
                Storage::delete($report->file_path);
            }

            // Upload the new file
            $filePath = $request->file('report_document')->store('public/reports');
            $report->file_path = $filePath;
        }

        $report->update($request->except('report_document')); // Update other fields

        return response()->json($report);
    }

    // Delete a report (also remove the associated document)
    public function destroy($id)
    {
        $report = Report::findOrFail($id);

        // Delete the file associated with the report
        if ($report->file_path && Storage::exists($report->file_path)) {
            Storage::delete($report->file_path);
        }

        $report->delete();
        return response()->json(['message' => 'Report deleted successfully']);
    }

    // Allow the patient to download their report
    public function downloadReport($patientId)
{
    // Fetch the report based on patient_id (assuming one report per patient)
    $report = Report::where('patient_id', $patientId)->latest()->first();

    // Check if a report exists for the patient
    if (!$report) {
        return response()->json(['error' => 'Report not found for this patient'], 404);
    }

    // Ensure only the patient or doctor can download the report
    $user = auth()->user();
    if ($user->id !== $report->patient_id && $user->id !== $report->doctor_id) {
        return response()->json(['error' => 'Unauthorized access'], 403);
    }

    // Return the file for download
    if ($report->file_path && Storage::exists($report->file_path)) {
        return response()->download(storage_path('app/' . $report->file_path));
    }

    return response()->json(['error' => 'File not found'], 404);
}


    public function storeReport(Request $request, $patientId)
    {
        // Validate the request
        $request->validate([
            'report_document' => 'required|file|mimes:pdf,doc,docx|max:2048', // Validate document file
        ]);

        // Handle file upload
        if ($request->hasFile('report_document')) {
            $filePath = $request->file('report_document')->store('public/reports');

            // Store report info in the database
            $report = Report::create([
                'patient_id' => $patientId,
                'file_path' => $filePath,
                'doctor_id' => auth()->id(), // Assuming doctor is logged in
            ]);

            return response()->json(['message' => 'Report uploaded successfully', 'report' => $report], 201);
        }

        return response()->json(['error' => 'File upload failed'], 400);
    }

    public function getReports(Request $request) {
        $user = auth()->user();
        
        // Assuming 'reports' table has patient_id and is related to doctors.
        $reports = Report::where('patient_id', $user->id)
            ->with('doctor')
            ->get();
        
        return response()->json(['reports' => $reports]);
    }
    

}
