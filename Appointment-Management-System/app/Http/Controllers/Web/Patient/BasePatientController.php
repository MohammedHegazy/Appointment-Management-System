<?php

namespace App\Http\Controllers\Web\Patient;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\User;

abstract class BasePatientController extends Controller
{
    protected function ensurePatient(): User
    {
        $user = auth()->user();
        abort_unless($user && $user->role === RoleType::Patient, 403, 'Patient access only.');

        return $user;
    }
}

