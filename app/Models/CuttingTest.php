<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuttingTest extends Model
{
    use HasFactory, SoftDeletes;

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
        'note',
    ];

    protected $casts = [
        'bill_id' => 'integer',
        'container_id' => 'integer',
        'type' => 'integer',
        'moisture' => 'decimal:1',
        'sample_weight' => 'integer',
        'nut_count' => 'integer',
        'w_reject_nut' => 'integer',
        'w_defective_nut' => 'integer',
        'w_defective_kernel' => 'integer',
        'w_good_kernel' => 'integer',
        'w_sample_after_cut' => 'integer',
        'outturn_rate' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the bill that owns the cutting test.
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Get the container that owns the cutting test.
     */
    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    /**
     * Get the cutting test type enum.
     */
    public function getTypeEnum(): \App\Enums\CuttingTestType
    {
        return \App\Enums\CuttingTestType::from($this->type);
    }

    /**
     * Check if this is a final sample test.
     */
    public function isFinalSample(): bool
    {
        return $this->getTypeEnum()->isFinalSample() && is_null($this->container_id);
    }

    /**
     * Check if this is a container test.
     */
    public function isContainerTest(): bool
    {
        return $this->getTypeEnum()->isContainerTest() && !is_null($this->container_id);
    }
}
