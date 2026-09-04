<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class Property
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $city
 * @property int|null $best_offer_id only present when selected via a correlated subquery, e.g. in SearchPropertiesAction
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Offer> $offers
 * @property-read Offer|null $bestOffer
 */
final class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'city',
    ];

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    /**
     * Resolves against the `best_offer_id` pseudo-column selected by
     * {@see \App\Actions\SearchPropertiesAction} via a correlated subquery —
     * not a real column, so this relation is only usable there.
     */
    public function bestOffer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'best_offer_id');
    }
}
