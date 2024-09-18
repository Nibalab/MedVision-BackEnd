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
        $patients = User::where('name', 'like', '%' . $searchQuery . '%')
                    ->where('role', 'patient')  
                    ->get();

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
        $query = User::where('role', 'patient');

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

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
        $searchTerm = $request->input('name');

        if (!$searchTerm) {
            return response()->json([
                'success' => false,
                'message' => 'Search term is required.'
            ], 400);
        }

        $patients = User::where('role', 'patient') 
                        ->where('name', 'like', '%' . $searchTerm . '%')
                        ->select('id', 'name', 'gender') 
                        ->paginate(10); 

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

   
    $messages = Message::where('receiver_id', $userId)
                ->where('receiver_type', 'App\Models\User') 
                ->where('is_read', false)
                ->with('sender') 
                ->get();

   
    $doctorMessages = $messages->filter(function ($message) {
        return $message->sender_type === 'App\Models\Doctor'; 
    });

    return response()->json([
        'messages' => $doctorMessages
    ]);
}


public function getConfirmedAppointments()
{
    $userId = Auth::id();
    $confirmedAppointments = Appointment::where('patient_id', $userId)
                              ->where('status', 'confirmed')
                              ->with('doctor.user') 
                              ->get();

    return response()->json([
        'confirmedAppointments' => $confirmedAppointments
    ]);
}


     public function getNewReports()
    {
        $userId = Auth::id();

        $newReports = Report::where('patient_id', $userId)
                    ->latest()  
                    ->with('doctor') 
                    ->get();

        return response()->json([
            'newReports' => $newReports
        ]);
    }

    


}
