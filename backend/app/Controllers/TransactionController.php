<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Services\TransactionService;
use InvalidArgumentException;

final class TransactionController
{
    public function __construct(
        private readonly TransactionService $service = new TransactionService()
    ) {
    }

    public function index(Request $request): void
    {
        Response::ok($this->service->list(
            $request->input('type'),
            $request->input('month')
        ));
    }

    public function store(Request $request): void
    {
        try {
            Response::created($this->service->create($request->body()));
        } catch (InvalidArgumentException $exception) {
            Response::fail('VALIDATION_ERROR', $exception->getMessage(), 422);
        }
    }

    public function destroy(Request $request): void
    {
        $deleted = $this->service->delete((int) $request->route('id', 0));

        if (!$deleted) {
            Response::fail('TRANSACTION_NOT_FOUND', 'La transaccion no existe', 404);
        }

        Response::ok(['deleted' => true]);
    }
}
