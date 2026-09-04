<?php

declare(strict_types=1);

namespace App\DTOs;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ImportPropertyPayload',
    required: ['code', 'name', 'city'],
    properties: [
        new OA\Property(property: 'code', type: 'string', example: 'BCN-0001'),
        new OA\Property(property: 'name', type: 'string', example: 'Apartment near Sagrada Familia'),
        new OA\Property(property: 'city', type: 'string', example: 'Barcelona'),
    ],
    type: 'object',
)]
final readonly class PropertyData
{
    public function __construct(public string $code, public string $name, public string $city) {}
}
