<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number', 20)->nullable();
            $table->string('seller', 255)->nullable();
            $table->string('buyer', 255)->nullable();
            $table->unsignedInteger('w_dunnage_dribag')->nullable();
            $table->decimal('w_jute_bag', 4, 2)->default(1.00);
            $table->integer('net_on_bl')->nullable();
            $table->integer('quantity_of_bags_on_bl')->nullable();
            $table->string('origin')->nullable();
            $table->timestamp('inspection_start_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('inspection_end_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('inspection_location')->nullable();
            $table->decimal('sampling_ratio', 5, 2)->default(10.00);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('created_at', 'idx_bills_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
