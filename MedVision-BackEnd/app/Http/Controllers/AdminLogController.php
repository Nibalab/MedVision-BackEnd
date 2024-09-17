<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;

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


    public function getAdminDashboardStats()
    {
        try {
            // Count of total doctors
            $totalDoctors = User::doctors()->count();
    
            // Count of new doctors (registered within the last week)
            $newDoctors = User::doctors()->where('created_at', '>=', now()->subWeek())->count();
    
            // Count of old doctors (registered more than a week ago)
            $oldDoctors = $totalDoctors - $newDoctors;
    
            // Count of total patients
            $totalPatients = User::patients()->count();
    
            // Count of new patients (registered within the last week)
            $newPatients = User::patients()->where('created_at', '>=', now()->subWeek())->count();
    
            // Count of old patients (registered more than a week ago)
            $oldPatients = $totalPatients - $newPatients;
    
            return response()->json([
                'totalDoctors' => $totalDoctors,
                'newDoctors' => $newDoctors,
                'oldDoctors' => $oldDoctors,
                'totalPatients' => $totalPatients,
                'newPatients' => $newPatients,
                'oldPatients' => $oldPatients,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch admin dashboard stats'], 500);
        }
    }
    
    public function searchDoctorsAdmin(Request $request)
{
    $searchTerm = $request->input('name'); 

    
    if (!$searchTerm) {
        return response()->json(['message' => 'Search term is required'], 400);
    }

    
    $doctors = Doctor::join('users', 'doctors.user_id', '=', 'users.id') 
                    ->where('users.role', 'doctor') 
                    ->where('users.name', 'LIKE', '%' . $searchTerm . '%') 
                    ->select('doctors.*', 'users.name', 'users.email', 'users.profile_picture') 
                    ->get();

    
    if ($doctors->isEmpty()) {
        return response()->json(['message' => 'No doctors found for the given search criteria'], 404);
    }

    return response()->json($doctors);
}

public function searchPatientsAdmin(Request $request)
{
    $searchTerm = $request->input('name'); // Get the search term from the request

    // Check if search term exists
    if (!$searchTerm) {
        return response()->json(['message' => 'Search term is required'], 400);
    }

    // Query to search for patients by name
    $patients = User::where('role', 'patient') // Ensure we're searching for patients
                    ->where('name', 'LIKE', '%' . $searchTerm . '%') // Search by patient name
                    ->select('id', 'name', 'email', 'profile_picture') // Select required patient fields
                    ->get();

    // Check if any patients are found
    if ($patients->isEmpty()) {
        return response()->json(['message' => 'No patients found for the given search criteria'], 404);
    }

    return response()->json($patients);
}

public function getAllDoctors()
{
    // Query to get all doctors and their associated user details
    $doctors = Doctor::join('users', 'doctors.user_id', '=', 'users.id') // Join doctors with users table
                    ->where('users.role', 'doctor') // Ensure we're only getting doctors
                    ->select('doctors.*', 'users.name', 'users.email', 'users.profile_picture') // Select required doctor and user fields
                    ->get();

    // Check if any doctors are found
    if ($doctors->isEmpty()) {
        return response()->json(['message' => 'No doctors found'], 404);
    }

    return response()->json($doctors);
}

public function getAllPatients()
{
    // Query to get all users with the role 'patient'
    $patients = User::where('role', 'patient') // Ensure we're only getting patients
                    ->select('id', 'name', 'email', 'profile_picture', 'created_at') // Select required fields
                    ->get();

    // Check if any patients are found
    if ($patients->isEmpty()) {
        return response()->json(['message' => 'No patients found'], 404);
    }

    return response()->json($patients);
}



}
