<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTOs\PaginationData;
use App\DTOs\SearchPropertiesData;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'SearchPropertiesCityParameter',
    name: 'city',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'string'),
    example: 'Barcelona',
)]
#[OA\Parameter(
    parameter: 'SearchPropertiesCheckInParameter',
    name: 'check_in',
    in: 'query',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'date'),
    example: '2026-10-10',
)]
#[OA\Parameter(
    parameter: 'SearchPropertiesCheckOutParameter',
    name: 'check_out',
    in: 'query',
    required: true,
    schema: new OA\Schema(type: 'string', format: 'date'),
    example: '2026-10-15',
)]
#[OA\Parameter(
    parameter: 'SearchPropertiesGuestsParameter',
    name: 'guests',
    in: 'query',
    required: true,
    schema: new OA\Schema(type: 'integer'),
    example: 2,
)]
#[OA\Parameter(
    parameter: 'SearchPropertiesPerPageParameter',
    name: 'per_page',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'integer'),
    example: 10,
)]
#[OA\Parameter(
    parameter: 'SearchPropertiesPageParameter',
    name: 'page',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'integer'),
    example: 1,
)]
final class SearchPropertiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function makeDTO(): SearchPropertiesData
    {
        return new SearchPropertiesData(
            pagination: new PaginationData(
                page: (int) ($this->validated('page') ?? 1),
                perPage: (int) ($this->validated('per_page') ?? 10),
            ),
            city: $this->validated('city'),
            checkIn: $this->validated('check_in'),
            checkOut: $this->validated('check_out'),
            guests: (int) $this->validated('guests'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'city' => ['nullable', 'string', 'max:255'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests' => ['required', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
