<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'percynguyen82@gmail.com'],
            [
                'name' => 'Percy Nguyen',
                'email' => 'percynguyen82@gmail.com',
                'password' => Hash::make('Qt_3DKP:XnKbs6Z'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Default user created: percynguyen82@gmail.com');
    }
}