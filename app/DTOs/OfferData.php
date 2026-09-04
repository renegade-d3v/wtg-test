<?php

declare(strict_types=1);

namespace App\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ImportOfferPayload',
    required: ['external_id', 'property', 'check_in', 'check_out', 'max_guests', 'price', 'currency', 'available_units', 'expires_at'],
    properties: [
        new OA\Property(property: 'external_id', type: 'string', example: 'offer-a-10001'),
        new OA\Property(property: 'property', ref: '#/components/schemas/ImportPropertyPayload'),
        new OA\Property(property: 'check_in', type: 'string', format: 'date', example: '2026-10-10'),
        new OA\Property(property: 'check_out', type: 'string', format: 'date', example: '2026-10-15'),
        new OA\Property(property: 'max_guests', type: 'integer', example: 4),
        new OA\Property(property: 'price', type: 'integer', example: 72500, description: 'Minor currency units (cents)'),
        new OA\Property(property: 'currency', type: 'string', example: 'EUR', minLength: 3, maxLength: 3),
        new OA\Property(property: 'available_units', type: 'integer', example: 2),
        new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', example: '2026-09-10T23:59:59Z'),
    ],
    type: 'object',
)]
final readonly class OfferData
{
    public function __construct(
        public string $external_id,
        public PropertyData $property,
        public string $check_in,
        public string $check_out,
        public int $max_guests,
        public int $price,
        public string $currency,
        public int $available_units,
        public string $expires_at,
    ) {}
}
