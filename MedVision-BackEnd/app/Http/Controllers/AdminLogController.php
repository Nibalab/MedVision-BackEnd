<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminLogController extends Controller
{
    
    public function index()
    {
        $adminLogs = AdminLog::with('admin')->orderBy('created_at', 'desc')->get();
        return response()->json($adminLogs);
    }

    
    public function show($id)
    {
        $adminLog = AdminLog::with('admin')->findOrFail($id);
        return response()->json($adminLog);
    }

   
    public function store(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id',
            'action' => 'required|string',
        ]);

        $adminLog = AdminLog::create($request->all());

        return response()->json($adminLog, 201);
    }

    
    public function destroy($id)
    {
        $adminLog = AdminLog::findOrFail($id);
        $adminLog->delete();

        return response()->json(['message' => 'Admin log deleted successfully']);
    }
}
