<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Display the doctor dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        return response()->json(['message' => 'Welcome to the Doctor Dashboard']);
    }

    /**
     * Display a listing of doctors.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $doctors = Doctor::with('user')->get();
        return response()->json($doctors);
    }

    /**
     * Store a newly created doctor in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'specialization' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'contact_number' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
        ]);

        $doctor = Doctor::create($request->all());

        return response()->json($doctor, 201);
    }

    /**
     * Display the specified doctor.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $doctor = Doctor::with(['user', 'messages', 'ctScans', 'reports'])->findOrFail($id);
        return response()->json($doctor);
    }

    /**
     * Update the specified doctor in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'specialization' => 'sometimes|required|string|max:255',
            'bio' => 'nullable|string',
            'contact_number' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
        ]);

        $doctor = Doctor::findOrFail($id);
        $doctor->update($request->all());

        return response()->json($doctor);
    }

    /**
     * Remove the specified doctor from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->delete();

        return response()->json(['message' => 'Doctor deleted successfully']);
    }

    /**
     * Get all messages related to a specific doctor.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function messages($id)
    {
        $doctor = Doctor::findOrFail($id);
        $messages = $doctor->messages()->with(['sender', 'receiver'])->get();

        return response()->json($messages);
    }

    /**
     * Get all CT scans related to a specific doctor.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function ctScans($id)
    {
        $doctor = Doctor::findOrFail($id);
        $ctScans = $doctor->ctScans()->get();

        return response()->json($ctScans);
    }

    /**
     * Get all reports related to a specific doctor.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reports($id)
    {
        $doctor = Doctor::findOrFail($id);
        $reports = $doctor->reports()->get();

        return response()->json($reports);
    }
}
