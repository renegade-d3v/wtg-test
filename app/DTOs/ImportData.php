<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ImportData
{
    /** @param  array<int, array<string, mixed>> $payload */
    public function __construct(
        public string $supplier,
        public string $external_import_id,
        public string $sent_at,
        public array $payload,
    ) {}
}
