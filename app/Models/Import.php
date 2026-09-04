<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ImportStatusEnum;
use Database\Factories\ImportFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class Import
 *
 * @property int $id
 * @property int $supplier_id
 * @property string $external_import_id
 * @property Carbon $sent_at
 * @property ImportStatusEnum $status
 * @property int $total_offers
 * @property int $processed_offers
 * @property array<int, mixed> $payload
 * @property string|null $error
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Supplier $supplier
 * @property-read Collection<int, Offer> $offers
 */
final class Import extends Model
{
    /** @use HasFactory<ImportFactory> */
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'external_import_id',
        'sent_at',
        'status',
        'total_offers',
        'processed_offers',
        'payload',
        'error',
        'completed_at',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'last_import_id');
    }

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'completed_at' => 'datetime',
            'payload' => 'array',
            'total_offers' => 'integer',
            'processed_offers' => 'integer',
            'status' => ImportStatusEnum::class,
        ];
    }
}
