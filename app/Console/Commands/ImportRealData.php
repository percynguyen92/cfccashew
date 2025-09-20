<?php

namespace App\Console\Commands;

use Database\Seeders\ImportDataSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ImportRealData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:import {--fresh : Run fresh migration before importing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import real CFCCashew data from import_data.sql';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('fresh')) {
            $this->info('Running fresh migration...');
            Artisan::call('migrate:fresh');
            $this->info('Migration completed.');
        }

        $this->info('Importing real data...');
        
        // Run the import data seeder
        Artisan::call('db:seed', [
            '--class' => ImportDataSeeder::class
        ]);

        $this->info('Real data imported successfully!');
        
        return Command::SUCCESS;
    }
}
