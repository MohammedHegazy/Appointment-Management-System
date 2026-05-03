<?php

return [
    'role_labels' => [
        'admin' => ['welcome' => 'Welcome Admin', 'subtitle' => 'Admin Console'],
        'hospital_admin' => ['welcome' => 'Welcome Hospital Admin', 'subtitle' => 'Hospital Console'],
        'doctor' => ['welcome' => 'Welcome Doctor', 'subtitle' => 'Doctor Console'],
        'patient' => ['welcome' => 'Welcome Patient', 'subtitle' => 'Patient Console'],
    ],

    'role_home_routes' => [
        'admin' => 'admin.index',
        'hospital_admin' => 'hospital-admin.index',
        'doctor' => 'home',
        'patient' => 'home',
    ],

    'navigation' => [
        'admin' => [
            ['label' => 'Overview', 'route' => 'admin.index', 'active' => ['admin.index'], 'icon' => 'grid'],
            ['label' => 'Audit Logs', 'route' => 'admin.audit-logs.index', 'active' => ['admin.audit-logs.*'], 'icon' => 'list'],
            ['label' => 'Hospitals', 'route' => 'admin.hospitals.index', 'active' => ['admin.hospitals.*'], 'icon' => 'bars'],
            ['label' => 'Doctors', 'route' => 'admin.doctors.index', 'active' => ['admin.doctors.*'], 'icon' => 'doctor'],
            ['label' => 'Patients', 'route' => 'admin.patients.index', 'active' => ['admin.patients.*'], 'icon' => 'users'],
            ['label' => 'My Profile', 'route' => 'admin.settings.profile', 'active' => ['admin.settings.*'], 'icon' => 'settings'],
        ],
        'hospital_admin' => [
            ['label' => 'Overview', 'route' => 'hospital-admin.index', 'active' => ['hospital-admin.index'], 'icon' => 'grid'],
            ['label' => 'Doctors', 'route' => 'hospital-admin.doctors.index', 'active' => ['hospital-admin.doctors.*'], 'icon' => 'doctor'],
            ['label' => 'Appointments', 'route' => 'hospital-admin.appointments.index', 'active' => ['hospital-admin.appointments.*'], 'icon' => 'list'],
            ['label' => 'My Profile', 'route' => 'hospital-admin.settings.profile', 'active' => ['hospital-admin.settings.profile', 'hospital-admin.settings.profile.*'], 'icon' => 'settings'],
            ['label' => 'Hospital Settings', 'route' => 'hospital-admin.settings.hospital', 'active' => ['hospital-admin.settings.hospital', 'hospital-admin.settings.hospital.*'], 'icon' => 'bars'],
        ],
        'doctor' => [
            ['label' => 'Home', 'route' => 'home', 'active' => ['home'], 'icon' => 'grid'],
        ],
        'patient' => [
            ['label' => 'Home', 'route' => 'home', 'active' => ['home'], 'icon' => 'grid'],
        ],
    ],
];
