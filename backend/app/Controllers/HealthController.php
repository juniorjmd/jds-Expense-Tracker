<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Response;

final class HealthController
{
    public function __invoke(): void
    {
        Response::json([
            'ok' => true,
            'message' => 'Expense Tracker API is running',
        ]);
    }
}
