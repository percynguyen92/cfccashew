<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bill_number',
        'seller',
        'buyer',
        'w_dunnage_dribag',
        'w_jute_bag',
        'net_on_bl',
        'quantity_of_bags_on_bl',
        'origin',
        'inspection_start_date',
        'inspection_end_date',
        'inspection_location',
        'sampling_ratio',
        'note',
    ];

    protected $with = ['containers'];

    protected $casts = [
        'w_dunnage_dribag' => 'integer',
        'w_jute_bag' => 'decimal:2',
        'net_on_bl' => 'integer',
        'quantity_of_bags_on_bl' => 'integer',
        'inspection_start_date' => 'datetime',
        'inspection_end_date' => 'datetime',
        'sampling_ratio' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the containers for the bill.
     */
    public function containers(): BelongsToMany
    {
        return $this->belongsToMany(Container::class)
            ->withTimestamps()
            ->orderBy('containers.created_at');
    }

    /**
     * Get the cutting tests for the bill.
     */
    public function cuttingTests(): HasMany
    {
        return $this->hasMany(CuttingTest::class);
    }

    /**
     * Get the final sample cutting tests (types 1-3).
     */
    public function finalSamples(): HasMany
    {
        return $this->hasMany(CuttingTest::class)
            ->whereIn('type', [
                \App\Enums\CuttingTestType::FinalFirstCut->value,
                \App\Enums\CuttingTestType::FinalSecondCut->value,
                \App\Enums\CuttingTestType::FinalThirdCut->value,
            ])
            ->whereNull('container_id');
    }

    /**
     * Calculate average outurn rate from final samples.
     */
    public function getAverageOutturnAttribute(): ?float
    {
        $finalSamples = $this->finalSamples()->whereNotNull('outturn_rate')->get();
        
        if ($finalSamples->isEmpty()) {
            return null;
        }

        return round($finalSamples->avg('outturn_rate'), 2);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the slug attribute for URL.
     */
    public function getSlugAttribute(): string
    {
        $billNumber = $this->bill_number ? $this->bill_number : 'bill';
        return $this->id . '-' . $billNumber;
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Extract ID from slug format (id-billnumber)
        if (str_contains($value, '-')) {
            $id = explode('-', $value)[0];
            return $this->where('id', $id)->first();
        }
        
        // Fallback to ID if no slug format
        return $this->where('id', $value)->first();
    }
}