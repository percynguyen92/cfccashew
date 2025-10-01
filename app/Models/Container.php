<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Container extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'truck',
        'container_number',
        'quantity_of_bags',
        'w_total',
        'w_truck',
        'w_container',
        'w_gross',
        'w_tare',
        'w_net',
        'container_condition',
        'seal_condition',
        'note',
    ];

    protected $casts = [
        'quantity_of_bags' => 'integer',
        'w_total' => 'integer',
        'w_truck' => 'integer',
        'w_container' => 'integer',
        'w_gross' => 'integer',
        'w_tare' => 'decimal:2',
        'w_net' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the bills associated with the container.
     */
    public function bills(): BelongsToMany
    {
        return $this->belongsToMany(Bill::class)
            ->withTimestamps()
            ->withPivot(['note'])
            ->orderBy('bills.created_at');
    }

    /**
     * Get the cutting tests for the container.
     */
    public function cuttingTests(): HasMany
    {
        return $this->hasMany(CuttingTest::class);
    }

    /**
     * Calculate gross weight using the formula: w_gross = w_total - w_truck - w_container
     */
    public function calculateGrossWeight(): ?float
    {
        if (is_null($this->w_total) || is_null($this->w_truck) || is_null($this->w_container)) {
            return null;
        }
        
        return max(0, $this->w_total - $this->w_truck - $this->w_container);
    }

    /**
     * Calculate tare weight using the formula: w_tare = quantity_of_bags * w_jute_bag
     * Note: w_jute_bag comes from the associated Bill model
     */
    public function calculateTareWeight(): ?float
    {
        if (is_null($this->quantity_of_bags)) {
            return null;
        }
        
        // Get w_jute_bag from the first associated bill
        $bill = $this->bills()->first();
        if (!$bill || is_null($bill->w_jute_bag)) {
            return null;
        }
        
        return $this->quantity_of_bags * $bill->w_jute_bag;
    }

    /**
     * Calculate net weight using the formula: w_net = w_gross - w_dunnage_dribag - w_tare
     * Note: w_dunnage_dribag comes from the associated Bill model
     */
    public function calculateNetWeight(): ?float
    {
        $grossWeight = $this->calculateGrossWeight();
        $tareWeight = $this->calculateTareWeight();
        
        if (is_null($grossWeight) || is_null($tareWeight)) {
            return null;
        }
        
        // Get w_dunnage_dribag from the first associated bill
        $bill = $this->bills()->first();
        if (!$bill || is_null($bill->w_dunnage_dribag)) {
            return null;
        }
        
        return max(0, $grossWeight - $bill->w_dunnage_dribag - $tareWeight);
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