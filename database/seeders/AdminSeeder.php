<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

/** Creates the first admin from .env (never hard-coded). Safe to re-run. */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_SEED_EMAIL');
        $password = env('ADMIN_SEED_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_SEED_EMAIL / ADMIN_SEED_PASSWORD not set: no admin created.');

            return;
        }

        if (AdminUser::where('email', $email)->exists()) {
            return;
        }

        AdminUser::create(['name' => 'Site Admin', 'email' => $email, 'password' => $password]);
    }
}
