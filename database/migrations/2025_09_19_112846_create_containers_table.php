<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->string('truck', 20)->nullable();
            $table->string('container_number', 11)->nullable();
            $table->unsignedInteger('quantity_of_bags')->nullable();
            $table->unsignedInteger('w_total')->nullable();
            $table->unsignedInteger('w_truck')->nullable();
            $table->unsignedInteger('w_container')->nullable();
            $table->unsignedInteger('w_gross')->nullable();
            $table->decimal('w_tare', 10, 2)->nullable();
            $table->decimal('w_net', 10, 2)->nullable();
            $table->string('container_condition')->default('Nguyên vẹn');
            $table->string('seal_condition')->default('Nguyên vẹn');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('created_at', 'idx_containers_created_at');
            $table->index('container_number', 'idx_containers_container_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('containers');
    }
};
