<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReservationStatusEnum;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class Reservation
 *
 * @property int $id
 * @property int $offer_id
 * @property string $client_reference
 * @property string $customer_name
 * @property string $customer_email
 * @property ReservationStatusEnum $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Offer $offer
 */
final class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'offer_id',
        'client_reference',
        'customer_name',
        'customer_email',
        'status',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    protected function casts(): array
    {
        return [
            'status' => ReservationStatusEnum::class,
        ];
    }
}
