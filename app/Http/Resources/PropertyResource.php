<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PropertyResource',
    properties: [
        new OA\Property(property: 'code', type: 'string', example: 'BCN-0001'),
        new OA\Property(property: 'name', type: 'string', example: 'Apartment near Sagrada Familia'),
        new OA\Property(property: 'city', type: 'string', example: 'Barcelona'),
        new OA\Property(property: 'best_offer', properties: [
            new OA\Property(property: 'id', type: 'integer', example: 125),
            new OA\Property(property: 'supplier', type: 'string', example: 'supplier-a'),
            new OA\Property(property: 'price', type: 'integer', example: 72500),
            new OA\Property(property: 'currency', type: 'string', example: 'EUR'),
            new OA\Property(property: 'available_units', type: 'integer', example: 2),
            new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', example: '2026-09-10T23:59:59Z'),
        ],
            type: 'object',
        ),
    ],
)]
/**
 * Expects `bestOffer.supplier` eager-loaded, as done by {@see \App\Actions\SearchPropertiesAction}.
 * @mixin Property
 */
final class PropertyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'city' => $this->city,
            'best_offer' => [
                'id' => $this->bestOffer->id,
                'supplier' => $this->bestOffer->supplier->slug,
                'price' => $this->bestOffer->price,
                'currency' => $this->bestOffer->currency,
                'available_units' => $this->bestOffer->available_units,
                'expires_at' => $this->bestOffer->expires_at->toISOString(),
            ],
        ];
    }
}
