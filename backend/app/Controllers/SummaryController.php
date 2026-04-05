<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Services\TransactionService;

final class SummaryController
{
    public function __construct(
        private readonly TransactionService $service = new TransactionService()
    ) {
    }

    public function show(Request $request): void
    {
        Response::ok(
            $this->service->summary((string) $request->input('month', date('Y-m')))
        );
    }
}
