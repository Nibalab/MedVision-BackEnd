<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    // Fetch all appointments with patient and doctor relationships
    public function index()
    {
        $appointments = Appointment::with(['patient', 'doctor'])->get();
        return response()->json($appointments);
    }

    // Create a new appointment request
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:users,id', // Validating against the users table for patients
            'doctor_id' => 'required|exists:users,id',   // Validating against the users table for doctors
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i', // Validate time format
        ]);

        // Create a pending appointment request
        $appointment = Appointment::create([
            'patient_id' => $request->input('patient_id'),
            'doctor_id' => $request->input('doctor_id'),
            'appointment_date' => $request->input('appointment_date'),
            'appointment_time' => $request->input('appointment_time'),
            'status' => 'pending', // Set default status to pending for new requests
        ]);

        return response()->json($appointment, 201);
    }

    // Show a specific appointment with patient and doctor details
    public function show($id)
    {
        $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($id);
        return response()->json($appointment);
    }

    // Update an existing appointment
    public function update(Request $request, $id)
    {
        $request->validate([
            'appointment_date' => 'sometimes|required|date',
            'appointment_time' => 'sometimes|required|date_format:H:i', // Validate time format
            'status' => 'sometimes|required|in:pending,confirmed,completed,canceled',
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->update($request->all());

        return response()->json($appointment);
    }

    // Accept an appointment request
    public function acceptAppointment($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'confirmed']);
        
        return response()->json(['message' => 'Appointment accepted successfully', 'appointment' => $appointment]);
    }
    
    public function declineAppointment($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'canceled']);
        
        return response()->json(['message' => 'Appointment declined successfully', 'appointment' => $appointment]);
    }

    // Delete an appointment
    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return response()->json(['message' => 'Appointment deleted successfully'], 200);
    }
}
