<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', '/login');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [AuthWebController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthWebController::class, 'register'])->name('register.submit');

    Route::get('/verify-email', [AuthWebController::class, 'showVerifyOtp'])->name('verify.form');
    Route::post('/verify-email', [AuthWebController::class, 'verifyOtp'])->name('verify.submit');
    Route::post('/verify-email/resend', [AuthWebController::class, 'resendOtp'])->name('verify.resend');

    Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthWebController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/home', [AuthWebController::class, 'home'])->name('home');
    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
        Route::get('/audit-logs', [AdminDashboardController::class, 'auditLogs'])->name('audit-logs.index');
        Route::get('/hospitals', [AdminDashboardController::class, 'hospitalsIndex'])->name('hospitals.index');
        Route::get('/hospitals/create', [AdminDashboardController::class, 'createHospital'])->name('hospitals.create');
        Route::post('/hospitals/create', [AdminDashboardController::class, 'createHospitalSubmit'])->name('hospitals.create.submit');
        Route::get('/hospitals/{hospital}', [AdminDashboardController::class, 'hospitalInfo'])->name('hospitals.info');
        Route::get('/hospitals/{hospital}/update', [AdminDashboardController::class, 'updateHospital'])->name('hospitals.update');
        Route::post('/hospitals/{hospital}/update', [AdminDashboardController::class, 'updateHospitalSubmit'])->name('hospitals.update.submit');
        Route::get('/hospitals/{hospital}/users', [AdminDashboardController::class, 'hospitalUsersIndex'])->name('hospitals.users.index');
        Route::get('/hospitals/{hospital}/users/{user}', [AdminDashboardController::class, 'hospitalUserInfo'])->name('hospitals.users.info');
        Route::get('/hospitals/{hospital}/users/{user}/update', [AdminDashboardController::class, 'hospitalUserUpdate'])->name('hospitals.users.update');
        Route::post('/hospitals/{hospital}/users/{user}/update', [AdminDashboardController::class, 'hospitalUserUpdateSubmit'])->name('hospitals.users.update.submit');
        Route::get('/hospitals/{hospital}/doctors', [AdminDashboardController::class, 'hospitalDoctorsIndex'])->name('hospitals.doctors.index');
        Route::get('/hospitals/{hospital}/doctors/{doctor}', [AdminDashboardController::class, 'hospitalDoctorInfo'])->name('hospitals.doctors.info');
        Route::get('/hospitals/{hospital}/doctors/{doctor}/update', [AdminDashboardController::class, 'hospitalDoctorUpdate'])->name('hospitals.doctors.update');
        Route::post('/hospitals/{hospital}/doctors/{doctor}/update', [AdminDashboardController::class, 'hospitalDoctorUpdateSubmit'])->name('hospitals.doctors.update.submit');
        Route::get('/doctors', [AdminDashboardController::class, 'doctorsIndex'])->name('doctors.index');
        Route::get('/doctors/create', [AdminDashboardController::class, 'createDoctor'])->name('doctors.create');
        Route::post('/doctors/create', [AdminDashboardController::class, 'createDoctorSubmit'])->name('doctors.create.submit');
        Route::get('/doctors/{doctor}', [AdminDashboardController::class, 'doctorInfo'])->name('doctors.info');
        Route::get('/doctors/{doctor}/update', [AdminDashboardController::class, 'updateDoctor'])->name('doctors.update');
        Route::post('/doctors/{doctor}/update', [AdminDashboardController::class, 'updateDoctorSubmit'])->name('doctors.update.submit');
        Route::get('/patients', [AdminDashboardController::class, 'patientsIndex'])->name('patients.index');
        Route::get('/patients/{patient}', [AdminDashboardController::class, 'patientInfo'])->name('patients.info');
        Route::get('/patients/{patient}/update', [AdminDashboardController::class, 'updatePatient'])->name('patients.update');
        Route::post('/patients/{patient}/update', [AdminDashboardController::class, 'updatePatientSubmit'])->name('patients.update.submit');
        Route::get('/settings/profile', [AdminDashboardController::class, 'settingsProfile'])->name('settings.profile');
        Route::post('/settings/profile', [AdminDashboardController::class, 'settingsProfileUpdate'])->name('settings.profile.update');
        Route::post('/settings/profile/password', [AdminDashboardController::class, 'settingsPasswordUpdate'])->name('settings.profile.password.update');
    });
});
