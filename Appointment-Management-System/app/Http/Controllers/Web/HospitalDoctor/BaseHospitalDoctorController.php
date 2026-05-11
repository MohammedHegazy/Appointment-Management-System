<?php

namespace App\Http\Controllers\Web\HospitalDoctor;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\User;

abstract class BaseHospitalDoctorController extends Controller
{
    protected function ensureHospitalDoctor(): User
    {
        $user = auth()->user();
        abort_unless($user && $user->role === RoleType::Doctor, 403, 'Doctor access only.');
        abort_unless($user->hospital_id !== null, 403, 'Hospital doctor access only.');

        return $user;
    }
}

