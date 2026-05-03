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
        'hospital_admin' => 'home',
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
            ['label' => 'Home', 'route' => 'home', 'active' => ['home'], 'icon' => 'grid'],
        ],
        'doctor' => [
            ['label' => 'Home', 'route' => 'home', 'active' => ['home'], 'icon' => 'grid'],
        ],
        'patient' => [
            ['label' => 'Home', 'route' => 'home', 'active' => ['home'], 'icon' => 'grid'],
        ],
    ],
];
