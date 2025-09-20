<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default user
        $this->call(DefaultUserSeeder::class);

        // Import real data from import_data.sql
        $this->call(ImportDataSeeder::class);
    }
}
