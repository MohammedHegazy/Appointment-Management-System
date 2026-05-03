<?php

namespace Database\Seeders;

use App\Models\Specialization;
use Illuminate\Database\Seeder;

class SpecializationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            'Cardiology',
            'Dermatology',
            'Pediatrics',
            'Orthopedics',
            'Neurology',
        ];

        foreach ($items as $name) {
            Specialization::firstOrCreate(['name' => $name]);
        }
    }
}
