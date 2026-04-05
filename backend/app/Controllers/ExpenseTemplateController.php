<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Services\CurrentUserService;
use App\Services\ExpenseTemplateService;
use InvalidArgumentException;

final class ExpenseTemplateController
{
    public function __construct(
        private readonly ExpenseTemplateService $service = new ExpenseTemplateService(),
        private readonly CurrentUserService $currentUser = new CurrentUserService()
    ) {
    }

    public function index(Request $request): void
    {
        try {
            Response::ok($this->service->listByEstablishment($this->currentUser->require($request), (int) $request->route('id', 0)));
        } catch (InvalidArgumentException $exception) {
            Response::fail('VALIDATION_ERROR', $exception->getMessage(), 422);
        }
    }

    public function store(Request $request): void
    {
        try {
            Response::created($this->service->create($this->currentUser->require($request), (int) $request->route('id', 0), $request->body()));
        } catch (InvalidArgumentException $exception) {
            Response::fail('VALIDATION_ERROR', $exception->getMessage(), 422);
        }
    }

    public function apply(Request $request): void
    {
        try {
            Response::created($this->service->apply($this->currentUser->require($request), (int) $request->route('id', 0)));
        } catch (InvalidArgumentException $exception) {
            Response::fail('VALIDATION_ERROR', $exception->getMessage(), 422);
        }
    }

    public function destroy(Request $request): void
    {
        if (!$this->service->delete($this->currentUser->require($request), (int) $request->route('id', 0))) {
            Response::fail('EXPENSE_TEMPLATE_NOT_FOUND', 'El gasto predeterminado no existe.', 404);
        }

        Response::ok(['deleted' => true]);
    }
}
