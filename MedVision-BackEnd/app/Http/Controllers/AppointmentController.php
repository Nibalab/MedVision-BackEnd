<?php

namespace App\Http\Controllers;
use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['patient', 'doctor'])->get();
        return response()->json($appointments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:users,id', 
            'doctor_id' => 'required|exists:users,id',  
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i', 
        ]);
        $appointment = Appointment::create([
            'patient_id' => $request->input('patient_id'),
            'doctor_id' => $request->input('doctor_id'),
            'appointment_date' => $request->input('appointment_date'),
            'appointment_time' => $request->input('appointment_time'),
            'status' => 'pending', 
        ]);

        return response()->json($appointment, 201);
    }

    public function show($id)
    {
        $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($id);
        return response()->json($appointment);
    }

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

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return response()->json(['message' => 'Appointment deleted successfully'], 200);
    }

    public function getTodayAppointments()
    {
        $doctorId = Auth::user()->doctor->id; 
        $today = Carbon::now()->format('Y-m-d');

        $appointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('status', 'confirmed') 
            ->get();

        return response()->json($appointments);
    }

    /**
     * Fetch this week's appointments for the authenticated doctor.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWeekAppointments()
    {
        $doctorId = Auth::user()->doctor->id; 
        $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
        $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d');

        $appointments = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('appointment_date', [$startOfWeek, $endOfWeek])
            ->where('status', 'confirmed') // Optional: if you only want confirmed appointments
            ->get();

        return response()->json($appointments);
    }
}
