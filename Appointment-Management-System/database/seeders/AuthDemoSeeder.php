<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\RoleType;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\MedicalProfile;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuthDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultHospital = Hospital::firstOrCreate(
            ['name' => 'City Care Hospital'],
            ['address' => 'Main Street', 'phone' => '+123456789']
        );

        User::updateOrCreate(
            ['email' => 'admin@app.test'],
            [
                'first_name' => 'System',
                'last_name' => 'Admin',
                'password' => 'password',
                'role' => RoleType::Admin,
                'status' => AccountStatus::Active,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'hospital@app.test'],
            [
                'first_name' => 'Pending',
                'last_name' => 'HospitalAdmin',
                'password' => 'password',
                'hospital_id' => $defaultHospital->id,
                'role' => RoleType::HospitalAdmin,
                'status' => AccountStatus::Pending,
                'email_verified_at' => now(),
            ]
        );

        $doctorUser = User::updateOrCreate(
            ['email' => 'doctor@app.test'],
            [
                'first_name' => 'Pending',
                'last_name' => 'Doctor',
                'password' => 'password',
                'role' => RoleType::Doctor,
                'status' => AccountStatus::Pending,
                'email_verified_at' => now(),
            ]
        );

        Doctor::updateOrCreate(
            ['user_id' => $doctorUser->id],
            [
                'specialization_id' => Specialization::query()->value('id'),
                'license_number' => 'LIC-001-PENDING',
                'bio' => 'Doctor account waiting for admin approval.',
            ]
        );

        $patientUser = User::updateOrCreate(
            ['email' => 'patient@app.test'],
            [
                'first_name' => 'Active',
                'last_name' => 'Patient',
                'password' => 'password',
                'role' => RoleType::Patient,
                'status' => AccountStatus::Active,
                'email_verified_at' => now(),
            ]
        );

        MedicalProfile::updateOrCreate(
            ['user_id' => $patientUser->id],
            [
                'blood_type' => null,
                'allergies' => null,
                'chronic_conditions' => null,
                'medications' => null,
                'emergency_contact' => null,
            ]
        );
    }
}
