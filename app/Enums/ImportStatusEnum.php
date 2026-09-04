<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum ImportStatusEnum: string
{
    use EnumUtils;

    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
