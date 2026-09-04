<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumUtils;

enum QueueEnum: string
{
    use EnumUtils;

    case Imports = 'imports';
}
