<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['patient', 'doctor'])->get(); // Eager loading patient and doctor relationships
        return response()->json($appointments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id', // Validating against the patients table
            'doctor_id' => 'required|exists:doctors,id',   // Validating against the doctors table
            'appointment_date' => 'required|date',
            'status' => 'required|in:pending,confirmed,completed',
        ]);

        $appointment = Appointment::create($request->all());

        return response()->json($appointment, 201);
    }

    public function show($id)
    {
        $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($id); // Eager loading patient and doctor relationships
        return response()->json($appointment);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'sometimes|required|in:pending,confirmed,completed',
        ]);

        $appointment = Appointment::findOrFail($id);
        $appointment->update($request->all());

        return response()->json($appointment);
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();
        return response()->json(['message' => 'Appointment deleted successfully']);
    }
}
