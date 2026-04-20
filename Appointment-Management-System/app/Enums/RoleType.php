<?php

namespace App\Enums;

enum RoleType: string
{
    case Admin = 'admin';
    case Doctor = 'doctor';
    case HospitalAdmin = 'hospital_admin';
    case Patient = 'patient';
}

