<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Doctor;

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

   

}
