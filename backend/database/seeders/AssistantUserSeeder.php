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
        $user = User::query()->where('username', 'assistant')->first();

        if ($user) {
            $user->fill([
                'name' => 'Dental Assistant',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ASSISTANT,
            ])->save();

            return;
        }

        User::create([
            'name' => 'Dental Assistant',
            'username' => 'assistant',
            'email' => 'assistant@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ASSISTANT,
        ]);
    }
}
