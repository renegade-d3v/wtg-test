<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class SearchPropertiesData
{
    public function __construct(
        public PaginationData $pagination,
        public ?string $city,
        public string $checkIn,
        public string $checkOut,
        public int $guests,
    ) {}
}
