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
            $totalDoctors = User::doctors()->count();
            $newDoctors = User::doctors()->where('created_at', '>=', now()->subWeek())->count();
            $oldDoctors = $totalDoctors - $newDoctors;
            $totalPatients = User::patients()->count();
            $newPatients = User::patients()->where('created_at', '>=', now()->subWeek())->count();
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
        'email' => 'required|string|email|max:255|unique:users,email,' . $id, 
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

public function updatePatient(Request $request, $id)
{
    // Validate the incoming request
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $id, // Ensure the email is unique except for the current patient
        'gender' => 'required|string|in:male,female,other', // Validate gender as one of the allowed values
    ]);

    try {
        // Find the user (patient) by ID
        $user = User::findOrFail($id);

        // Update the patient's information
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->gender = $validatedData['gender'];

        if ($user->isDirty()) {
            $user->save(); // Save the changes if there are any modifications
        }

        return response()->json([
            'message' => 'Patient updated successfully',
            'updatedPatient' => $user,
        ]);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error updating patient', 'error' => $e->getMessage()], 500);
    }
}

// Delete Doctor Function in AdminLogController.php

public function deleteDoctor($doctorId)
{
    try {
        // Find the doctor and associated user
        $doctor = Doctor::find($doctorId);
        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        // Delete the user associated with the doctor
        $user = User::find($doctor->user_id);
        if ($user) {
            $user->delete(); // This will delete the user and the associated doctor due to the foreign key
        }

        return response()->json(['message' => 'Doctor deleted successfully'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error deleting doctor', 'error' => $e->getMessage()], 500);
    }
}

// Delete Patient Function in AdminLogController.php

public function deletePatient($patientId)
{
    try {
        // Find the patient (assuming role is defined)
        $user = User::where('role', 'patient')->find($patientId);

        if (!$user) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        // Delete the user (and thereby the patient record)
        $user->delete();

        return response()->json(['message' => 'Patient deleted successfully'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Error deleting patient', 'error' => $e->getMessage()], 500);
    }
}


}
