<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            'Oral Prophylaxis',
            'Tooth Restoration',
            'Tooth Extraction',
            'Wisdom Tooth Extraction',
            'Root Canal Treatment',
            'Crowns, Bridges, & Dentures',
            'Dental Implants',
            'Orthodontics (Braces, Aligners, Retainers)',
            'Cosmetic Dentistry (Teeth Whitening, Veneers)',
            'Pediatric Dentistry',
            'Periapical Xray'
        ];

        foreach ($services as $service) {
            \App\Models\Appointment::create([
                'name' => 'Sample Patient ' . ($services === array_search($service, $services) + 1),
                'age' => rand(18, 65),
                'email' => 'patient' . rand(1, 100) . '@example.com',
                'contact_number' => '0917-' . rand(100, 999) . '-' . rand(1000, 9999),
                'service' => $service,
                'appointment_date' => now()->addDays(rand(1, 14))->format('Y-m-d'),
                'note' => 'Automatic seed data for service: ' . $service,
                'status' => 'pending'
            ]);
        }
    }
}
