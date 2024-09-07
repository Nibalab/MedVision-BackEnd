<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CtScanController;
use App\Http\Controllers\ThreeDModelController;
use App\Http\Controllers\AnnotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AdminLogController;
use App\Http\Controllers\DoctorController;
use App\Http\Middleware\EnsureUserIsDoctor;


Route::post('/register/doctor', [AuthController::class, 'registerDoctor']);
Route::post('/register/patient', [AuthController::class, 'registerPatient']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api');

// Admin routes
Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    
    Route::get('admin-logs', [AdminLogController::class, 'index']);
    Route::get('admin-logs/{id}', [AdminLogController::class, 'show']);
    Route::post('admin-logs', [AdminLogController::class, 'store']);
    Route::delete('admin-logs/{id}', [AdminLogController::class, 'destroy']);
});

// Doctor routes
Route::middleware(['auth:api', EnsureUserIsDoctor::class])->group(function () {
    Route::get('/doctor-dashboard', [DoctorController::class, 'dashboard']);
    Route::get('/doctor-dashboard/stats', [DoctorController::class, 'getDashboardStats']);
    
    // Appointment management by doctor
    Route::get('/doctor-dashboard/pending-appointments', [DoctorController::class, 'getPendingAppointments']);
    Route::put('appointments/{id}/accept', [AppointmentController::class, 'acceptAppointment']);  // Doctor accepts the appointment
    Route::put('appointments/{id}/decline', [AppointmentController::class, 'declineAppointment']); // Doctor declines the appointment
    Route::get('/appointments/today', [AppointmentController::class, 'getTodayAppointments']);
    Route::get('/appointments/week', [AppointmentController::class, 'getWeekAppointments']);
    
    // CT Scan and 3D Model Management
    Route::post('ct-scans', [CtScanController::class, 'store']);
    Route::delete('ct-scans/{id}', [CtScanController::class, 'destroy']);
    
    Route::get('3d-models', [ThreeDModelController::class, 'index']);
    Route::get('3d-models/{id}', [ThreeDModelController::class, 'show']);
    Route::post('3d-models', [ThreeDModelController::class, 'store']);
    Route::delete('3d-models/{id}', [ThreeDModelController::class, 'destroy']);
    
    Route::post('annotations', [AnnotationController::class, 'store']);
    Route::delete('annotations/{id}', [AnnotationController::class, 'destroy']);
    
    Route::post('reports', [ReportController::class, 'store']);
    Route::put('reports/{id}', [ReportController::class, 'update']);
});

// Patient routes (common user routes)
Route::middleware(['auth:api', 'patient'])->group(function () {
    // Appointment management by patient
    Route::post('appointments', [AppointmentController::class, 'store']); // Patient requests an appointment
    Route::put('appointments/{id}', [AppointmentController::class, 'update']); // Patient can update an appointment request
    Route::get('appointments', [AppointmentController::class, 'index']); // View appointments
    Route::get('appointments/{id}', [AppointmentController::class, 'show']); // View specific appointment
    
    Route::get('ct-scans', [CtScanController::class, 'index']);
    Route::get('ct-scans/{id}', [CtScanController::class, 'show']);
    
    Route::get('annotations/{modelId}', [AnnotationController::class, 'index']);
    Route::get('annotations/{id}', [AnnotationController::class, 'show']);
    
    Route::get('reports', [ReportController::class, 'index']);
    Route::get('reports/{id}', [ReportController::class, 'show']);
});

// Common routes for authenticated users (e.g. both patients and doctors)
Route::middleware(['auth:api'])->group(function () {
    Route::get('messages', [MessageController::class, 'index']);
    Route::get('messages/{id}', [MessageController::class, 'show']);
    Route::post('messages', [MessageController::class, 'store']);
    Route::put('messages/{id}/read', [MessageController::class, 'markAsRead']);
    Route::delete('messages/{id}', [MessageController::class, 'destroy']);
});
