<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\Doctor\AvailabilityController as DoctorAvailabilityController;
use App\Http\Controllers\Web\Doctor\AppointmentController as DoctorAppointmentController;
use App\Http\Controllers\Web\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Web\Doctor\SettingsController as DoctorSettingsController;
use App\Http\Controllers\Web\HospitalDoctor\AppointmentController as HospitalDoctorAppointmentController;
use App\Http\Controllers\Web\HospitalDoctor\DashboardController as HospitalDoctorDashboardController;
use App\Http\Controllers\Web\HospitalDoctor\SettingsController as HospitalDoctorSettingsController;
use App\Http\Controllers\Web\HospitalAdmin\AppointmentController as HospitalAdminAppointmentController;
use App\Http\Controllers\Web\HospitalAdmin\DashboardController as HospitalAdminDashboardController;
use App\Http\Controllers\Web\HospitalAdmin\DoctorAvailabilityController as HospitalAdminDoctorAvailabilityController;
use App\Http\Controllers\Web\HospitalAdmin\DoctorController as HospitalAdminDoctorController;
use App\Http\Controllers\Web\HospitalAdmin\SettingsController as HospitalAdminSettingsController;
use App\Http\Controllers\Web\Admin\AuditLogController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\DoctorController;
use App\Http\Controllers\Web\Admin\HospitalController;
use App\Http\Controllers\Web\Admin\HospitalDoctorController;
use App\Http\Controllers\Web\Admin\HospitalUserController;
use App\Http\Controllers\Web\Admin\PatientController;
use App\Http\Controllers\Web\Admin\SettingsController;

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

    Route::prefix('hospital-admin')->name('hospital-admin.')->group(function (): void {
        Route::get('/', [HospitalAdminDashboardController::class, 'index'])->name('index');

        Route::get('/doctors', [HospitalAdminDoctorController::class, 'index'])->name('doctors.index');
        Route::get('/doctors/create', [HospitalAdminDoctorController::class, 'create'])->name('doctors.create');
        Route::post('/doctors/create', [HospitalAdminDoctorController::class, 'store'])->name('doctors.create.submit');
        Route::get('/doctors/{doctor}', [HospitalAdminDoctorController::class, 'show'])->name('doctors.info');
        Route::get('/doctors/{doctor}/update', [HospitalAdminDoctorController::class, 'edit'])->name('doctors.update');
        Route::post('/doctors/{doctor}/update', [HospitalAdminDoctorController::class, 'update'])->name('doctors.update.submit');
        Route::post('/doctors/{doctor}/status', [HospitalAdminDoctorController::class, 'updateStatus'])->name('doctors.status.update');

        Route::get('/doctors/{doctor}/availability', [HospitalAdminDoctorAvailabilityController::class, 'index'])->name('doctors.availability.index');
        Route::get('/doctors/{doctor}/availability/create', [HospitalAdminDoctorAvailabilityController::class, 'create'])->name('doctors.availability.create');
        Route::post('/doctors/{doctor}/availability/create', [HospitalAdminDoctorAvailabilityController::class, 'store'])->name('doctors.availability.create.submit');
        Route::get('/doctors/{doctor}/availability/{availability}/update', [HospitalAdminDoctorAvailabilityController::class, 'edit'])->name('doctors.availability.update');
        Route::post('/doctors/{doctor}/availability/{availability}/update', [HospitalAdminDoctorAvailabilityController::class, 'update'])->name('doctors.availability.update.submit');
        Route::post('/doctors/{doctor}/availability/{availability}/delete', [HospitalAdminDoctorAvailabilityController::class, 'destroy'])->name('doctors.availability.delete');

        Route::get('/appointments', [HospitalAdminAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/{appointment}', [HospitalAdminAppointmentController::class, 'show'])->name('appointments.info');
        Route::get('/appointments/{appointment}/update', [HospitalAdminAppointmentController::class, 'edit'])->name('appointments.update');
        Route::post('/appointments/{appointment}/update', [HospitalAdminAppointmentController::class, 'update'])->name('appointments.update.submit');

        Route::get('/settings/profile', [HospitalAdminSettingsController::class, 'profile'])->name('settings.profile');
        Route::post('/settings/profile', [HospitalAdminSettingsController::class, 'updateProfile'])->name('settings.profile.update');
        Route::post('/settings/profile/password', [HospitalAdminSettingsController::class, 'updatePassword'])->name('settings.profile.password.update');
        Route::get('/settings/hospital', [HospitalAdminSettingsController::class, 'hospitalProfile'])->name('settings.hospital');
        Route::post('/settings/hospital', [HospitalAdminSettingsController::class, 'updateHospitalProfile'])->name('settings.hospital.update');
    });

    Route::prefix('doctor')->name('doctor.')->group(function (): void {
        Route::get('/', [DoctorDashboardController::class, 'index'])->name('index');

        Route::get('/appointments', [DoctorAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/{appointment}', [DoctorAppointmentController::class, 'show'])->name('appointments.info');
        Route::post('/appointments/{appointment}', [DoctorAppointmentController::class, 'update'])->name('appointments.update');

        Route::get('/availability', [DoctorAvailabilityController::class, 'index'])->name('availability.index');
        Route::get('/availability/create', [DoctorAvailabilityController::class, 'create'])->name('availability.create');
        Route::post('/availability/create', [DoctorAvailabilityController::class, 'store'])->name('availability.create.submit');
        Route::get('/availability/{availability}/update', [DoctorAvailabilityController::class, 'edit'])->name('availability.update');
        Route::post('/availability/{availability}/update', [DoctorAvailabilityController::class, 'update'])->name('availability.update.submit');
        Route::post('/availability/{availability}/delete', [DoctorAvailabilityController::class, 'destroy'])->name('availability.delete');

        Route::get('/settings/profile', [DoctorSettingsController::class, 'profile'])->name('settings.profile');
        Route::post('/settings/profile', [DoctorSettingsController::class, 'updateProfile'])->name('settings.profile.update');
        Route::post('/settings/profile/professional', [DoctorSettingsController::class, 'updateProfessional'])->name('settings.profile.professional.update');
        Route::post('/settings/profile/password', [DoctorSettingsController::class, 'updatePassword'])->name('settings.profile.password.update');
    });

    Route::prefix('hospital-doctor')->name('hospital-doctor.')->group(function (): void {
        Route::get('/', [HospitalDoctorDashboardController::class, 'index'])->name('index');

        Route::get('/appointments', [HospitalDoctorAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/{appointment}', [HospitalDoctorAppointmentController::class, 'show'])->name('appointments.info');
        Route::post('/appointments/{appointment}', [HospitalDoctorAppointmentController::class, 'update'])->name('appointments.update');

        Route::get('/settings/profile', [HospitalDoctorSettingsController::class, 'profile'])->name('settings.profile');
        Route::post('/settings/profile', [HospitalDoctorSettingsController::class, 'updateProfile'])->name('settings.profile.update');
        Route::post('/settings/profile/password', [HospitalDoctorSettingsController::class, 'updatePassword'])->name('settings.profile.password.update');
    });

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::get('/hospitals', [HospitalController::class, 'index'])->name('hospitals.index');
        Route::get('/hospitals/create', [HospitalController::class, 'create'])->name('hospitals.create');
        Route::post('/hospitals/create', [HospitalController::class, 'store'])->name('hospitals.create.submit');
        Route::get('/hospitals/{hospital}', [HospitalController::class, 'show'])->name('hospitals.info');
        Route::get('/hospitals/{hospital}/update', [HospitalController::class, 'edit'])->name('hospitals.update');
        Route::post('/hospitals/{hospital}/update', [HospitalController::class, 'update'])->name('hospitals.update.submit');

        Route::get('/hospitals/{hospital}/users', [HospitalUserController::class, 'index'])->name('hospitals.users.index');
        Route::get('/hospitals/{hospital}/users/{user}', [HospitalUserController::class, 'show'])->name('hospitals.users.info');
        Route::get('/hospitals/{hospital}/users/{user}/update', [HospitalUserController::class, 'edit'])->name('hospitals.users.update');
        Route::post('/hospitals/{hospital}/users/{user}/update', [HospitalUserController::class, 'update'])->name('hospitals.users.update.submit');

        Route::get('/hospitals/{hospital}/doctors', [HospitalDoctorController::class, 'index'])->name('hospitals.doctors.index');
        Route::get('/hospitals/{hospital}/doctors/{doctor}', [HospitalDoctorController::class, 'show'])->name('hospitals.doctors.info');
        Route::get('/hospitals/{hospital}/doctors/{doctor}/update', [HospitalDoctorController::class, 'edit'])->name('hospitals.doctors.update');
        Route::post('/hospitals/{hospital}/doctors/{doctor}/update', [HospitalDoctorController::class, 'update'])->name('hospitals.doctors.update.submit');

        Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors.index');
        Route::get('/doctors/create', [DoctorController::class, 'create'])->name('doctors.create');
        Route::post('/doctors/create', [DoctorController::class, 'store'])->name('doctors.create.submit');
        Route::get('/doctors/{doctor}', [DoctorController::class, 'show'])->name('doctors.info');
        Route::get('/doctors/{doctor}/update', [DoctorController::class, 'edit'])->name('doctors.update');
        Route::post('/doctors/{doctor}/update', [DoctorController::class, 'update'])->name('doctors.update.submit');

        Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
        Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.info');
        Route::get('/patients/{patient}/update', [PatientController::class, 'edit'])->name('patients.update');
        Route::post('/patients/{patient}/update', [PatientController::class, 'update'])->name('patients.update.submit');

        Route::get('/settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
        Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
        Route::post('/settings/profile/password', [SettingsController::class, 'updatePassword'])->name('settings.profile.password.update');
    });
});
