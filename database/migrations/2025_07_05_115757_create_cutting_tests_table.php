<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('cutting_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills');
            $table->foreignId('container_id')->nullable()->constrained('containers');
            $table->smallInteger('type')->comment('1-final sample first cut/ 2-final sample second cut/ 3-final sample third cut/4-container cut');
            $table->decimal('moisture', 4, 2)->nullable()->comment('Độ ẩm (%)');
            $table->unsignedSmallInteger('sample_weight')->default(1000)->comment('Trọng lượng mẫu (gram)');
            $table->unsignedSmallInteger('nut_count')->nullable()->comment('Số hạt trong mẫu');
            $table->unsignedSmallInteger('w_reject_nut')->nullable()->comment('Trọng lượng hạt lỗi hoàn toàn');
            $table->unsignedSmallInteger('w_defective_nut')->nullable()->comment('Trọng lượng hạt lỗi một phần');
            $table->unsignedSmallInteger('w_defective_kernel')->nullable()->comment('Trọng lượng nhân lỗi một phần');
            $table->unsignedSmallInteger('w_good_kernel')->nullable()->comment('Trọng lượng nhân điều tốt');
            $table->unsignedSmallInteger('w_sample_after_cut')->nullable()->comment('Tổng trọng lượng mẫu sau khi cắt');
            $table->decimal('outturn_rate', 5, 2)->nullable()->comment('Thu hồi nhân (lbs/80kg)');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cutting_tests');
    }
};
