<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Message;
use App\Models\Appointment;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,doctor,patient',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'role' => $validatedData['role'],
        ]);

        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->update($request->all());

        return response()->json($user);
    }
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function search(Request $request)
{
    $query = $request->input('query');

    if (empty($query)) {
        return response()->json([
            'doctors' => [],
            'patients' => [],
        ]);
    }
    $request->validate([
        'query' => 'required|string|max:255',
    ]);

    $searchQuery = $request->input('query');

    try {
        // Search in users (patients) table
        $patients = User::where('name', 'like', '%' . $searchQuery . '%')
                    ->where('role', 'patient')  // Assuming there's a role field to distinguish between patients and doctors
                    ->get();

        // Search in doctors table by joining with users table via user_id
        $doctors = Doctor::join('users', 'doctors.user_id', '=', 'users.id')
                        ->where('users.name', 'like', '%' . $searchQuery . '%')
                        ->select('doctors.*', 'users.name as doctor_name')
                        ->get();

        return response()->json([
            'patients' => $patients,
            'doctors' => $doctors,
        ]);
    } catch (\Exception $e) {
        \Log::error('Error during search: ' . $e->getMessage());
        return response()->json(['error' => 'An error occurred during search'], 500);
    }
}


public function getAllPatients(Request $request)
{
    try {
        // Add search functionality if the 'name' parameter is provided
        $query = User::where('role', 'patient');

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Fetch the patients with pagination
        $patients = $query->select('id', 'name', 'gender')->paginate(10);

        return response()->json([
            'success' => true,
            'patients' => $patients,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching patients: ' . $e->getMessage(),
        ], 500);
    }
}
public function searchPatients(Request $request)
{
    try {
        // Ensure that a search query (name) is provided
        $searchTerm = $request->input('name');

        if (!$searchTerm) {
            return response()->json([
                'success' => false,
                'message' => 'Search term is required.'
            ], 400);
        }

        // Search for patients whose names match the search term
        $patients = User::where('role', 'patient') // Assuming 'role' defines user type
                        ->where('name', 'like', '%' . $searchTerm . '%')
                        ->select('id', 'name', 'gender') // Specify the fields needed
                        ->paginate(10); // Optional: Paginate the results

        return response()->json([
            'success' => true,
            'patients' => $patients,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error searching patients: ' . $e->getMessage(),
        ], 500);
    }
}

public function getNewMessages()
{
    $userId = Auth::id();

    // Fetch unread messages where the receiver is the current user
    $messages = Message::where('receiver_id', $userId)
                ->where('receiver_type', 'App\Models\User') // Assuming the receiver is always a User model
                ->where('is_read', false)
                ->with('sender') // Load the polymorphic sender
                ->get();

    // Filter messages where the sender is a doctor
    $doctorMessages = $messages->filter(function ($message) {
        return $message->sender_type === 'App\Models\Doctor'; // Check if the sender is a Doctor
    });

    return response()->json([
        'messages' => $doctorMessages
    ]);
}


public function getConfirmedAppointments()
{
    $userId = Auth::id();

    // Fetching confirmed appointments for the logged-in patient
    $confirmedAppointments = Appointment::where('patient_id', $userId)
                              ->where('status', 'confirmed')
                              ->with('doctor.user') // Load the doctor and their user details
                              ->get();

    return response()->json([
        'confirmedAppointments' => $confirmedAppointments
    ]);
}


     public function getNewReports()
    {
        $userId = Auth::id();

        // Fetch new reports for the logged-in patient
        $newReports = Report::where('patient_id', $userId)
                    ->latest()  // Get the latest reports first
                    ->with('doctor') // Assuming you have a relationship with doctor
                    ->get();

        return response()->json([
            'newReports' => $newReports
        ]);
    }

    


}
