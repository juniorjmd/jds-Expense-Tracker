<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;

final class HealthController
{
    public function show(Request $request): void
    {
        Response::ok([
            'message' => 'Expense Tracker API is running',
            'time' => date('c'),
        ]);
    }
}
