<?php

namespace Database\Seeders;

use App\Models\Appointment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PatientVisitSeeder extends Seeder
{
    /**
     * Seed distinct patients (same name/email/contact) with multiple visit appointments.
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
            'Periapical Xray',
        ];

        $patients = [
            [
                'name' => 'Maria Santos',
                'age' => 34,
                'email' => 'maria.santos@example.com',
                'contact_number' => '0917-555-0101',
                'visits' => [
                    ['days_ago' => 120, 'hour' => 9, 'service' => 0, 'status' => 'completed', 'note' => 'Regular cleaning.', 'doctor_comment' => 'Mild plaque. Recalled in 6 months.'],
                    ['days_ago' => 45, 'hour' => 10, 'service' => 1, 'status' => 'completed', 'note' => 'Toothache on upper right.', 'doctor_comment' => 'Composite filling on #16.'],
                    ['days_ago' => -7, 'hour' => 14, 'service' => 0, 'status' => 'confirmed', 'note' => 'Follow-up checkup.', 'doctor_comment' => null],
                ],
            ],
            [
                'name' => 'Juan Dela Cruz',
                'age' => 42,
                'email' => 'juan.delacruz@example.com',
                'contact_number' => '0918-555-0202',
                'visits' => [
                    ['days_ago' => 90, 'hour' => 11, 'service' => 4, 'status' => 'completed', 'note' => 'Persistent pain after cold drinks.', 'doctor_comment' => 'RCT started on #36.'],
                    ['days_ago' => 60, 'hour' => 11, 'service' => 4, 'status' => 'completed', 'note' => 'Root canal continuation.', 'doctor_comment' => 'Canal cleaned and medicated.'],
                    ['days_ago' => 30, 'hour' => 15, 'service' => 5, 'status' => 'completed', 'note' => 'Crown placement.', 'doctor_comment' => 'Temp crown fitted; final next visit.'],
                    ['days_ago' => -3, 'hour' => 15, 'service' => 5, 'status' => 'pending', 'note' => 'Final crown cementation.', 'doctor_comment' => null],
                ],
            ],
            [
                'name' => 'Ana Reyes',
                'age' => 28,
                'email' => 'ana.reyes@example.com',
                'contact_number' => '0920-555-0303',
                'visits' => [
                    ['days_ago' => 14, 'hour' => 9, 'service' => 8, 'status' => 'completed', 'note' => 'Interested in whitening.', 'doctor_comment' => 'In-office whitening done. Shade A2→B1.'],
                    ['days_ago' => -10, 'hour' => 13, 'service' => 0, 'status' => 'confirmed', 'note' => 'Post-whitening checkup.', 'doctor_comment' => null],
                ],
            ],
            [
                'name' => 'Carlo Mendoza',
                'age' => 51,
                'email' => 'carlo.mendoza@example.com',
                'contact_number' => '0916-555-0404',
                'visits' => [
                    ['days_ago' => 200, 'hour' => 10, 'service' => 10, 'status' => 'completed', 'note' => 'X-ray for implant consult.', 'doctor_comment' => 'Bone height adequate for #46.'],
                    ['days_ago' => 150, 'hour' => 8, 'service' => 6, 'status' => 'completed', 'note' => 'Implant surgery day.', 'doctor_comment' => 'Fixture placed; healing abutment.'],
                    ['days_ago' => 40, 'hour' => 9, 'service' => 6, 'status' => 'completed', 'note' => 'Osseointegration check.', 'doctor_comment' => 'Stable. Ready for prosthesis.'],
                    ['days_ago' => -21, 'hour' => 10, 'service' => 5, 'status' => 'pending', 'note' => 'Implant crown delivery.', 'doctor_comment' => null],
                ],
            ],
            [
                'name' => 'Sofia Garcia',
                'age' => 19,
                'email' => 'sofia.garcia@example.com',
                'contact_number' => '0927-555-0505',
                'visits' => [
                    ['days_ago' => 75, 'hour' => 16, 'service' => 7, 'status' => 'completed', 'note' => 'Braces consult.', 'doctor_comment' => 'Recommended metal braces 18–24 months.'],
                    ['days_ago' => 50, 'hour' => 16, 'service' => 7, 'status' => 'completed', 'note' => 'Braces bonding.', 'doctor_comment' => 'Upper and lower arches bonded.'],
                    ['days_ago' => 20, 'hour' => 16, 'service' => 7, 'status' => 'completed', 'note' => 'Monthly adjustment.', 'doctor_comment' => 'Wire changed. Oral hygiene good.'],
                    ['days_ago' => -5, 'hour' => 16, 'service' => 7, 'status' => 'confirmed', 'note' => 'Next adjustment.', 'doctor_comment' => null],
                ],
            ],
            [
                'name' => 'Miguel Torres',
                'age' => 37,
                'email' => 'miguel.torres@example.com',
                'contact_number' => '0915-555-0606',
                'visits' => [
                    ['days_ago' => 10, 'hour' => 14, 'service' => 3, 'status' => 'cancelled', 'note' => 'Wisdom tooth pain.', 'doctor_comment' => 'Patient rescheduled — work conflict.'],
                    ['days_ago' => -2, 'hour' => 8, 'service' => 3, 'status' => 'confirmed', 'note' => 'Rescheduled extraction.', 'doctor_comment' => null],
                ],
            ],
            [
                'name' => 'Liza Villanueva',
                'age' => 45,
                'email' => 'liza.villanueva@example.com',
                'contact_number' => '0919-555-0707',
                'visits' => [
                    ['days_ago' => 8, 'hour' => 11, 'service' => 2, 'status' => 'no_show', 'note' => 'Loose molar.', 'doctor_comment' => 'Did not arrive. Call to rebook.'],
                    ['days_ago' => -14, 'hour' => 11, 'service' => 2, 'status' => 'pending', 'note' => 'Rebooked extraction.', 'doctor_comment' => null],
                ],
            ],
            [
                'name' => 'Paolo Ramos',
                'age' => 8,
                'email' => 'parent.ramos@example.com',
                'contact_number' => '0922-555-0808',
                'visits' => [
                    ['days_ago' => 35, 'hour' => 10, 'service' => 9, 'status' => 'completed', 'note' => 'First dental visit for Paolo.', 'doctor_comment' => 'Fluoride applied. No cavities.'],
                    ['days_ago' => -28, 'hour' => 10, 'service' => 9, 'status' => 'confirmed', 'note' => '6-month recall.', 'doctor_comment' => null],
                ],
            ],
            [
                'name' => 'Elena Cruz',
                'age' => 56,
                'email' => 'elena.cruz@example.com',
                'contact_number' => '0912-555-0909',
                'visits' => [
                    ['days_ago' => 180, 'hour' => 9, 'service' => 0, 'status' => 'completed', 'note' => 'Bleeding gums.', 'doctor_comment' => 'Gingivitis. Scaling done.'],
                    ['days_ago' => 100, 'hour' => 9, 'service' => 1, 'status' => 'completed', 'note' => 'Sensitive tooth.', 'doctor_comment' => 'Glass ionomer on #25.'],
                    ['days_ago' => 1, 'hour' => 13, 'service' => 0, 'status' => 'completed', 'note' => 'Maintenance clean.', 'doctor_comment' => 'Improved hygiene. Next in 4 months.'],
                ],
            ],
            [
                'name' => 'Rico Aquino',
                'age' => 31,
                'email' => 'rico.aquino@example.com',
                'contact_number' => '0928-555-1010',
                'visits' => [
                    ['days_ago' => -1, 'hour' => 15, 'service' => 1, 'status' => 'pending', 'note' => 'Chipped front tooth from sports.', 'doctor_comment' => null],
                ],
            ],
            [
                'name' => 'Bea Lim',
                'age' => 24,
                'email' => 'bea.lim@example.com',
                'contact_number' => '0917-555-1111',
                'visits' => [
                    ['days_ago' => 55, 'hour' => 12, 'service' => 10, 'status' => 'completed', 'note' => 'Check before braces.', 'doctor_comment' => 'PA of #11–#22 clear.'],
                    ['days_ago' => 25, 'hour' => 17, 'service' => 8, 'status' => 'completed', 'note' => 'Veneers consult.', 'doctor_comment' => 'Mock-up approved. Prep next visit.'],
                    ['days_ago' => -12, 'hour' => 17, 'service' => 8, 'status' => 'confirmed', 'note' => 'Veneer prep appointment.', 'doctor_comment' => null],
                ],
            ],
            [
                'name' => 'Diego Navarro',
                'age' => 39,
                'email' => 'diego.navarro@example.com',
                'contact_number' => '0918-555-1212',
                'visits' => [
                    ['days_ago' => 12, 'hour' => 8, 'service' => 0, 'status' => 'completed', 'note' => null, 'doctor_comment' => 'Routine OP. Heavy calculus removed.'],
                    ['days_ago' => 5, 'hour' => 14, 'service' => 2, 'status' => 'completed', 'note' => 'Broken tooth after eating.', 'doctor_comment' => 'Extracted #48. Sutures placed.'],
                    ['days_ago' => -4, 'hour' => 14, 'service' => 2, 'status' => 'confirmed', 'note' => 'Suture removal.', 'doctor_comment' => null],
                ],
            ],
        ];

        foreach ($patients as $patient) {
            foreach ($patient['visits'] as $visit) {
                $serviceIndex = $visit['service'];
                $date = Carbon::now()
                    ->startOfDay()
                    ->subDays($visit['days_ago'])
                    ->setTime($visit['hour'], 0);

                Appointment::create([
                    'name' => $patient['name'],
                    'age' => $patient['age'],
                    'email' => $patient['email'],
                    'contact_number' => $patient['contact_number'],
                    'service' => $services[$serviceIndex],
                    'appointment_date' => $date,
                    'note' => $visit['note'],
                    'doctor_comment' => $visit['doctor_comment'],
                    'status' => $visit['status'],
                ]);
            }
        }
    }
}
