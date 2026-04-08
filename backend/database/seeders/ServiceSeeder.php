<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Oral Prophylaxis', 'description' => 'Cleaning and prevention'],
            ['name' => 'Tooth Restoration', 'description' => 'Fillings and repairs'],
            ['name' => 'Tooth Extraction', 'description' => 'Safe tooth removal'],
            ['name' => 'Wisdom Tooth Extraction', 'description' => 'Specialized surgery'],
            ['name' => 'Root Canal Treatment', 'description' => 'Saving infected teeth'],
            ['name' => 'Crowns, Bridges, & Dentures', 'description' => 'Restorative prosthetics'],
            ['name' => 'Dental Implants', 'description' => 'Permanent tooth replacement'],
            ['name' => 'Orthodontics', 'description' => 'Braces, Aligners, Retainers'],
            ['name' => 'Cosmetic Dentistry', 'description' => 'Teeth Whitening, Veneers'],
            ['name' => 'Pediatric Dentistry', 'description' => 'Specialized care for children'],
            ['name' => 'Periapical Xray', 'description' => 'Detailed dental imaging'],
        ];

        foreach ($services as $service) {
            \App\Models\Service::create($service);
        }
    }
}
