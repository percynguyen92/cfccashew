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
        Schema::create('cutting_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->onDelete('cascade');
            $table->foreignId('container_id')->nullable()->constrained('containers')->onDelete('cascade');
            $table->smallInteger('type'); // 1-3 for final samples, 4 for container tests
            $table->decimal('moisture', 4, 2)->nullable(); // 0-100%
            $table->unsignedSmallInteger('sample_weight')->default(1000); // grams
            $table->unsignedSmallInteger('nut_count')->nullable();
            $table->unsignedSmallInteger('w_reject_nut')->nullable();
            $table->unsignedSmallInteger('w_defective_nut')->nullable();
            $table->unsignedSmallInteger('w_defective_kernel')->nullable();
            $table->unsignedSmallInteger('w_good_kernel')->nullable();
            $table->unsignedSmallInteger('w_sample_after_cut')->nullable();
            $table->decimal('outturn_rate', 5, 2)->nullable(); // 0-60 lbs/80kg
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('created_at', 'idx_cutting_tests_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cutting_tests');
    }
};
