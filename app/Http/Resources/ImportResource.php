<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Import;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ImportResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 15),
        new OA\Property(property: 'supplier', type: 'string', example: 'supplier-a'),
        new OA\Property(property: 'external_import_id', type: 'string', example: 'import-2026-09-01-001'),
        new OA\Property(property: 'sent_at', type: 'string', format: 'date-time', example: '2026-09-01T10:00:00Z'),
        new OA\Property(property: 'status', type: 'string', example: 'completed', enum: [
            'pending', 'processing', 'completed', 'failed',
        ]),
        new OA\Property(property: 'total_offers', type: 'integer', example: 20),
        new OA\Property(property: 'processed_offers', type: 'integer', example: 20),
        new OA\Property(property: 'error', type: 'string', example: null, nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-09-01T10:00:02Z'),
        new OA\Property(
            property: 'completed_at',
            type: 'string',
            format: 'date-time',
            example: '2026-09-01T10:00:04Z',
            nullable: true
        ),
    ],
)]
/** @mixin Import */
final class ImportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing('supplier');

        return [
            'id' => $this->id,
            'supplier' => $this->supplier->slug,
            'external_import_id' => $this->external_import_id,
            'sent_at' => $this->sent_at?->toISOString(),
            'status' => $this->status->value,
            'total_offers' => $this->total_offers,
            'processed_offers' => $this->processed_offers,
            'error' => $this->error,
            'created_at' => $this->created_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
        ];
    }
}
