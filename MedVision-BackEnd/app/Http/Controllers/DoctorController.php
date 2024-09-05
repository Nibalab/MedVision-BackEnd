<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use App\Models\CtScan;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Get doctor dashboard stats including CT scans, patients, and today's appointments.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDashboardStats()
    {
        try {
            // Get the current authenticated doctor's ID
            $doctorId = Auth::user()->doctor->id;

            // Count of total CT Scans for this doctor
            $totalCtScans = CtScan::where('doctor_id', $doctorId)->count();

            // Count of total patients (all patients in the system)
            $totalPatients = User::where('role', 'patient')->count();

            // Count of new patients (registered within the last week)
            $newPatients = User::where('role', 'patient')
                ->where('created_at', '>=', now()->subWeek())
                ->count();

            // Count of old patients (registered more than a week ago)
            $oldPatients = User::where('role', 'patient')
                ->where('created_at', '<', now()->subWeek())
                ->count();

            // Fetch today's appointments for the current doctor
            $appointmentsToday = Appointment::with('patient')
                ->where('doctor_id', $doctorId)
                ->whereDate('appointment_date', now()->format('Y-m-d'))
                ->get();

            // Count of today's appointments
            $totalAppointmentsToday = $appointmentsToday->count();

            // Return all stats including today's appointments
            return response()->json([
                'totalCtScans' => $totalCtScans,
                'totalPatients' => $totalPatients,
                'newPatients' => $newPatients,
                'oldPatients' => $oldPatients,
                'totalAppointmentsToday' => $totalAppointmentsToday,
                'appointmentsToday' => $appointmentsToday, // List of today's appointments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching the dashboard stats',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending appointment requests for the logged-in doctor.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPendingAppointments()
    {
        try {
            // Get the authenticated doctor's ID
            $doctorId = Auth::user()->doctor->id;

            // Fetch pending appointments for this doctor
            $pendingAppointments = Appointment::with('patient')
                ->where('doctor_id', $doctorId)
                ->where('status', 'pending')
                ->get();

            return response()->json($pendingAppointments);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching pending appointments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}