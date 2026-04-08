<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AssistantUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'assistant@urmaza.com'],
            [
                'name' => 'Dental Assistant',
                'username' => 'assistant',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ASSISTANT,
            ],
        );
    }
}
