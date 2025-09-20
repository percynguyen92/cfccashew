<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateDefaultUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-default';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create default user for development';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::firstOrCreate(
            ['email' => 'percynguyen82@gmail.com'],
            [
                'name' => 'Percy Nguyen',
                'email' => 'percynguyen82@gmail.com',
                'password' => Hash::make('Qt_3DKP:XnKbs6Z'),
                'email_verified_at' => now(),
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->info('✅ Default user created successfully!');
        } else {
            $this->info('ℹ️  Default user already exists.');
        }

        $this->info('');
        $this->info('Login credentials:');
        $this->info('Email: percynguyen82@gmail.com');
        $this->info('Password: Qt_3DKP:XnKbs6Z');

        return 0;
    }
}