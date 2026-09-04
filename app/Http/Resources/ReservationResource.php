<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ReservationResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 42),
        new OA\Property(property: 'offer_id', type: 'integer', example: 125),
        new OA\Property(property: 'client_reference', type: 'string', example: 'web-order-9f782b1c'),
        new OA\Property(property: 'customer_name', type: 'string', example: 'John Smith'),
        new OA\Property(property: 'customer_email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'status', type: 'string', enum: ['created', 'confirmed', 'cancelled'], example: 'confirmed'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-09-01T10:05:00Z'),
    ],
)]
/** @mixin \App\Models\Reservation */
final class ReservationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'offer_id' => $this->offer_id,
            'client_reference' => $this->client_reference,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
