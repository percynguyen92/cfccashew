<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data first
        $this->command->info('Clearing existing data...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('cutting_tests')->truncate();
        DB::table('bill_container')->truncate(); // Clear pivot table
        DB::table('containers')->truncate();
        DB::table('bills')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Read and execute the import_data.sql file
        $sqlFile = database_path('seeders/import_data.sql');
        
        if (!file_exists($sqlFile)) {
            $this->command->error('Import data file not found: ' . $sqlFile);
            return;
        }

        $sql = file_get_contents($sqlFile);
        
        // Split the SQL into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function ($statement) {
                return !empty($statement) && !str_starts_with($statement, '--');
            }
        );

        DB::transaction(function () use ($statements) {
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    DB::unprepared($statement);
                }
            }
        });

        $this->command->info('Real data imported successfully!');
        $this->command->info('Bills: ' . DB::table('bills')->count());
        $this->command->info('Containers: ' . DB::table('containers')->count());
        $this->command->info('Cutting Tests: ' . DB::table('cutting_tests')->count());
    }
}