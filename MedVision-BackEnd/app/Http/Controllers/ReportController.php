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
        $request->validate([
            'ct_scan_id' => 'required|exists:ct_scans,id',
            'doctor_id' => 'required|exists:users,id',
            'patient_id' => 'required|exists:users,id',
            'report_document' => 'required|file|mimes:pdf,doc,docx|max:2048', // Validate document file
        ]);

        // Handle file upload
        $filePath = $request->file('report_document')->store('public/reports');

        // Create the report record with the file path
        $report = Report::create([
            'ct_scan_id' => $request->ct_scan_id,
            'doctor_id' => $request->doctor_id,
            'patient_id' => $request->patient_id,
            'file_path' => $filePath,
        ]);

        return response()->json($report, 201);
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
    public function downloadReport($id)
    {
        $report = Report::findOrFail($id);

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
}
