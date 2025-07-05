<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\CuttingTestType;
use Illuminate\Database\Eloquent\Casts\AsDecimal;

class CuttingTest extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bill_id',
        'container_id',
        'type',
        'moisture',
        'sample_weight',
        'nut_count',
        'w_reject_nut',
        'w_defective_nut',
        'w_defective_kernel',
        'w_good_kernel',
        'w_sample_after_cut',
        'outturn_rate',
    ];

    protected $casts = [
        'type' => CuttingTestType::class,
        'moisture' => 'decimal:2',
        'outturn_rate' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(related: Bill::class);
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(related: Container::class);
    }
}