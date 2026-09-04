<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTOs\ImportData;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StoreImportRequest',
    required: ['supplier', 'external_import_id', 'sent_at', 'offers'],
    properties: [
        new OA\Property(property: 'supplier', description: 'Supplier slug', type: 'string', example: 'supplier-a'),
        new OA\Property(property: 'external_import_id', type: 'string', example: 'import-2026-09-01-001'),
        new OA\Property(property: 'sent_at', type: 'string', format: 'date-time', example: '2026-09-01T10:00:00Z'),
        new OA\Property(
            property: 'offers',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ImportOfferPayload'),
            minItems: 1,
        ),
    ],
)]
final class StoreImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function makeDTO(): ImportData
    {
        return new ImportData(
            supplier: $this->validated('supplier'),
            external_import_id: $this->validated('external_import_id'),
            sent_at: $this->validated('sent_at'),
            payload: $this->validated('offers'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'supplier' => ['required', 'string', 'exists:suppliers,slug'],
            'external_import_id' => ['required', 'string', 'max:255'],
            'sent_at' => ['required', 'date'],
            'offers' => ['required', 'array', 'min:1'],
            'offers.*.external_id' => ['required', 'string', 'max:255'],
            'offers.*.property' => ['required', 'array'],
            'offers.*.property.code' => ['required', 'string', 'max:255'],
            'offers.*.property.name' => ['required', 'string', 'max:255'],
            'offers.*.property.city' => ['required', 'string', 'max:255'],
            'offers.*.check_in' => ['required', 'date'],
            'offers.*.check_out' => ['required', 'date', 'after:offers.*.check_in'],
            'offers.*.max_guests' => ['required', 'integer', 'min:1'],
            'offers.*.price' => ['required', 'integer', 'gt:0'],
            'offers.*.currency' => ['required', 'string', 'size:3'],
            'offers.*.available_units' => ['required', 'integer', 'min:0'],
            'offers.*.expires_at' => ['required', 'date'],
        ];
    }
}
