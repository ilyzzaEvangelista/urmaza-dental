<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->orderBy('id')->take(5)->get();
        if ($users->isEmpty()) {
            return;
        }

        $samples = [
            ['action' => 'login', 'description' => 'Staff login'],
            ['action' => 'appointment.updated', 'description' => 'Appointment status changed to confirmed'],
            ['action' => 'appointment.created', 'description' => 'New appointment created from admin'],
            ['action' => 'patient.view', 'description' => 'Opened patient list'],
            ['action' => 'service.updated', 'description' => 'Service pricing note updated'],
            ['action' => 'logout', 'description' => 'Staff logout'],
        ];

        $base = Carbon::now()->subDays(14);
        foreach (range(1, 28) as $i) {
            $u = $users[($i - 1) % $users->count()];
            $row = $samples[($i - 1) % count($samples)];
            AuditLog::create([
                'user_id' => $u->id,
                'action' => $row['action'],
                'description' => $row['description'],
                'ip_address' => '127.0.0.1',
                'properties' => ['seed' => true, 'index' => $i],
                'created_at' => $base->copy()->addHours($i * 3),
            ]);
        }
    }
}
