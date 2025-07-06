<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Container extends Model
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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'w_jute_bag' => 'decimal:2',
        'w_tare' => 'decimal:2',
        'w_net' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(related: Bill::class);
    }

    public function cuttingTest(): HasOne
    {
        return $this->hasOne(related: CuttingTest::class);
    }
}