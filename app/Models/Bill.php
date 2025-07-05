<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'billNumber',
        'seller',
        'buyer',
    ];

    public function containers(): HasMany
    {
        return $this->hasMany(related: Container::class);
    }

    public function cuttingTests(): HasMany
    {
        return $this->hasMany(related: CuttingTest::class);
    }
}