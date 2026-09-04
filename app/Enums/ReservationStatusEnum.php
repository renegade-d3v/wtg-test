<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum ReservationStatusEnum: string
{
    use EnumUtils;

    case Created = 'created';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
}
