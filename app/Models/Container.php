<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Container extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bill_id',
        'truck',
        'container_number',
        'quantity_of_bags',
        'w_jute_bag',
        'w_total',
        'w_truck',
        'w_container',
        'w_gross',
        'w_dunnage_dribag',
        'w_tare',
        'w_net',
        'note',
    ];

    protected $casts = [
        'bill_id' => 'integer',
        'quantity_of_bags' => 'integer',
        'w_jute_bag' => 'decimal:2',
        'w_total' => 'integer',
        'w_truck' => 'integer',
        'w_container' => 'integer',
        'w_gross' => 'integer',
        'w_dunnage_dribag' => 'integer',
        'w_tare' => 'decimal:2',
        'w_net' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the bill that owns the container.
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Get the cutting tests for the container.
     */
    public function cuttingTests(): HasMany
    {
        return $this->hasMany(CuttingTest::class);
    }

    /**
     * Get the average moisture from cutting tests.
     */
    public function getAverageMoistureAttribute(): ?float
    {
        $tests = $this->cuttingTests()->whereNotNull('moisture')->get();
        
        if ($tests->isEmpty()) {
            return null;
        }

        return round($tests->avg('moisture'), 1);
    }

    /**
     * Get the outurn rate from cutting tests.
     */
    public function getOutturnRateAttribute(): ?float
    {
        $test = $this->cuttingTests()->whereNotNull('outturn_rate')->first();
        
        return $test ? $test->outturn_rate : null;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'container_number';
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // If the value looks like a container number (letters + digits), search by container_number
        if (preg_match('/^[A-Z]{4}\d{7}$/', $value)) {
            return $this->where('container_number', $value)->firstOrFail();
        }
        
        // Otherwise, fall back to ID lookup for backward compatibility
        return $this->where('id', $value)->firstOrFail();
    }
}