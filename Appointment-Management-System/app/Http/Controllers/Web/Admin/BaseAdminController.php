<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;

abstract class BaseAdminController extends Controller
{
    protected function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->role === RoleType::Admin, 403, 'Admin access only.');
    }
}
