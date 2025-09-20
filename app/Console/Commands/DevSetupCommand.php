<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DevSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:setup {--fresh : Run fresh migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup development environment with default user and sample data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up development environment...');

        if ($this->option('fresh')) {
            $this->info('Running fresh migration...');
            Artisan::call('migrate:fresh');
            $this->info(Artisan::output());
        }

        $this->info('Seeding development data...');
        Artisan::call('db:seed', ['--class' => 'DevelopmentSeeder']);
        $this->info(Artisan::output());

        $this->info('✅ Development setup complete!');
        $this->info('');
        $this->info('Default login credentials:');
        $this->info('Email: percynguyen82@gmail.com');
        $this->info('Password: Qt_3DKP:XnKbs6Z');
        $this->info('');
        $this->info('You can now run: php artisan serve');

        return 0;
    }
}