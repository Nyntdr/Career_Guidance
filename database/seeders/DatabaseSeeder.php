<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            SkillSeeder::class,
            SkillAliasSeeder::class,
            CareerRoleSeeder::class,
            CareerPathSeeder::class,
            LearningResourceSeeder::class,
            JobPostingSeeder::class,
        ]);

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password'), 'role' => 'user', 'email_verified_at' => now()],
        );

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password'), 'role' => 'admin', 'email_verified_at' => now()],
        );
    }
}
