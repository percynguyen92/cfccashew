<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds for development environment.
     */
    public function run(): void
    {
        // Create default user
        $user = User::firstOrCreate(
            ['email' => 'percynguyen82@gmail.com'],
            [
                'name' => 'Percy Nguyen',
                'email' => 'percynguyen82@gmail.com',
                'password' => Hash::make('Qt_3DKP:XnKbs6Z'),
                'email_verified_at' => now(),
            ]
        );

        $this->call(ImportDataSeeder::class);

        $this->command->info('Default user created: percynguyen82@gmail.com');
    }
}