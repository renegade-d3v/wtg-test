<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class PaginationData
{
    public function __construct(
        public int $page,
        public int $perPage,
    ) {}
}
