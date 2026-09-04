<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'WTG — REST API',
    title: 'WTG API',
)]
abstract class Controller
{
    //
}
