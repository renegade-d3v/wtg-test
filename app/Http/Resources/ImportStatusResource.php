<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Import;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ImportStatusResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 15),
        new OA\Property(property: 'status', type: 'string', example: 'pending', enum: [
            'pending', 'processing', 'completed', 'failed',
        ]),
    ],
)]
/** @mixin Import */
final class ImportStatusResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
        ];
    }
}
