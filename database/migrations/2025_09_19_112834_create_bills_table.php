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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number', 20)->nullable();
            $table->string('seller', 255)->nullable();
            $table->string('buyer', 255)->nullable();
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
