<?php

namespace App\Http\Controllers\Web\Doctor;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\User;

abstract class BaseDoctorController extends Controller
{
    protected function ensureDoctor(): User
    {
        $user = auth()->user();
        abort_unless($user && $user->role === RoleType::Doctor, 403, 'Doctor access only.');
        abort_if($user->hospital_id !== null, 403, 'Standalone doctor access only.');

        return $user;
    }
}
