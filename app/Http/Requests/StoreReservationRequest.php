<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTOs\ReservationData;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreReservationRequest',
    required: ['client_reference', 'customer_name', 'customer_email'],
    properties: [
        new OA\Property(
            property: 'client_reference',
            description: 'Client-generated idempotency token',
            type: 'string',
            example: 'web-order-9f782b1c'
        ),
        new OA\Property(property: 'customer_name', type: 'string', example: 'John Smith'),
        new OA\Property(property: 'customer_email', type: 'string', format: 'email', example: 'john@example.com'),
    ],
)]
final class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function makeDTO(): ReservationData
    {
        return new ReservationData(...$this->validated());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_reference' => ['required', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email'],
        ];
    }
}
