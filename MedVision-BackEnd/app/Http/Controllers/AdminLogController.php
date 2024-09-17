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
    $searchTerm = $request->input('name'); 

    
    if (!$searchTerm) {
        return response()->json(['message' => 'Search term is required'], 400);
    }

    
    $patients = User::where('role', 'patient') 
                    ->where('name', 'LIKE', '%' . $searchTerm . '%') 
                    ->select('id', 'name', 'email', 'profile_picture') 
                    ->get();

    
    if ($patients->isEmpty()) {
        return response()->json(['message' => 'No patients found for the given search criteria'], 404);
    }

    return response()->json($patients);
}

public function getAllDoctors()
{
    $doctors = Doctor::join('users', 'doctors.user_id', '=', 'users.id') 
                    ->where('users.role', 'doctor') 
                    ->select('doctors.*', 'users.name', 'users.email', 'users.profile_picture') 
                    ->get();

    if ($doctors->isEmpty()) {
        return response()->json(['message' => 'No doctors found'], 404);
    }

    return response()->json($doctors);
}

public function getAllPatients()
{
    
    $patients = User::where('role', 'patient') 
                    ->select('id', 'name', 'email', 'profile_picture', 'created_at') 
                    ->get();

    if ($patients->isEmpty()) {
        return response()->json(['message' => 'No patients found'], 404);
    }

    return response()->json($patients);
}

public function updateDoctor(Request $request, $id)
{
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $id, // Ensure the email is unique, excluding the current user
        'bio' => 'nullable|string|max:1000',
        'contact_number' => 'nullable|string|max:15',
        'address' => 'nullable|string|max:255',
        'specialization' => 'required|string|max:255',
    ]);

    try {
        // Update the user's information
        $user = User::findOrFail($id);
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];

        if ($user->isDirty()) {
            $user->save();
        }

        // Check if a doctor profile exists
        $doctor = Doctor::where('user_id', $id)->first();
        if (!$doctor) {
            return response()->json(['message' => 'Doctor record not found for user ID: ' . $id], 404);
        }

        // Update the doctor's profile with new fields
        $doctor->bio = $validatedData['bio'];
        $doctor->contact_number = $validatedData['contact_number'];
        $doctor->address = $validatedData['address'];
        $doctor->specialization = $validatedData['specialization'];
        $doctor->save();

        return response()->json([
            'message' => 'Doctor updated successfully',
            'updatedUser' => $user,
            'updatedDoctor' => $doctor,
        ]);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error updating doctor', 'error' => $e->getMessage()], 500);
    }
}



}
