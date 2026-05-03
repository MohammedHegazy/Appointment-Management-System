<?php

namespace App\Http\Controllers\Web\HospitalAdmin;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\User;

abstract class BaseHospitalAdminController extends Controller
{
    protected function ensureHospitalAdmin(): User
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user && $user->role === RoleType::HospitalAdmin, 403, 'Hospital admin access only.');
        abort_unless($user->hospital_id !== null, 403, 'Hospital admin must belong to a hospital.');

        return $user;
    }
}
