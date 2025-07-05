<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills');
            $table->string('truck', 20)->nullable();
            $table->string('container_number', 11)->nullable();
            $table->integer('quantity_of_bags')->nullable();
            $table->decimal('w_jute_bag', 4, 2)->nullable()->default(1);
            $table->integer('w_total')->nullable();
            $table->integer('w_truck')->nullable();
            $table->integer('w_container')->nullable();
            $table->integer('w_gross')->nullable();
            $table->integer('w_dunnage_dribag')->nullable();
            $table->decimal('w_tare', 10, 2)->nullable();
            $table->decimal('w_net', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('containers');
    }
};
