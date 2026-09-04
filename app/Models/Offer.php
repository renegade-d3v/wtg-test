<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class Offer
 *
 * @property int $id
 * @property int $supplier_id
 * @property int $property_id
 * @property int|null $last_import_id
 * @property string $external_id
 * @property Carbon $check_in
 * @property Carbon $check_out
 * @property int $max_guests
 * @property int $price
 * @property string $currency
 * @property int $available_units
 * @property Carbon $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Supplier $supplier
 * @property-read Property $property
 * @property-read Import|null $lastImport
 * @property-read Collection<int, Reservation> $reservations
 */
final class Offer extends Model
{
    /** @use HasFactory<OfferFactory> */
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'property_id',
        'last_import_id',
        'external_id',
        'check_in',
        'check_out',
        'max_guests',
        'price',
        'currency',
        'available_units',
        'expires_at',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function lastImport(): BelongsTo
    {
        return $this->belongsTo(Import::class, 'last_import_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'expires_at' => 'datetime',
            'max_guests' => 'integer',
            'price' => 'integer',
            'available_units' => 'integer',
        ];
    }
}
