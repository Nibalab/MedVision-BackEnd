<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

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
            'ct_scan_id' => 'required|exists:ct_scans,id',
            'doctor_id' => 'required|exists:users,id',
            'patient_id' => 'required|exists:users,id',
            'report_content' => 'required|string',
            'status' => 'required|in:draft,finalized',
        ]);

        $report = Report::create($request->all());

        return response()->json($report, 201);
    }

    public function show($id)
    {
        $report = Report::findOrFail($id);
        return response()->json($report);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'report_content' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:draft,finalized',
        ]);

        $report = Report::findOrFail($id);
        $report->update($request->all());

        return response()->json($report);
    }

    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();
        return response()->json(['message' => 'Report deleted successfully']);
    }
}
