<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ReservationData
{
    public function __construct(
        public string $client_reference,
        public string $customer_name,
        public string $customer_email,
    ) {}
}
