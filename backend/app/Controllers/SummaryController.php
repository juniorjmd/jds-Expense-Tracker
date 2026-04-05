<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Services\CurrentUserService;
use App\Services\TransactionService;
use InvalidArgumentException;

final class SummaryController
{
    public function __construct(
        private readonly TransactionService $service = new TransactionService(),
        private readonly CurrentUserService $currentUser = new CurrentUserService()
    ) {
    }

    public function show(Request $request): void
    {
        try {
            Response::ok(
                $this->service->summary(
                    $this->currentUser->require($request),
                    (string) $request->input('month', date('Y-m')),
                    $request->input('companyId') !== null ? (int) $request->input('companyId') : null
                )
            );
        } catch (InvalidArgumentException $exception) {
            Response::fail('AUTHORIZATION_ERROR', $exception->getMessage(), 403);
        }
    }
}
