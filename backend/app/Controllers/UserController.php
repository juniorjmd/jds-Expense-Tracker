<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Services\UserService;
use InvalidArgumentException;

final class UserController
{
    public function __construct(
        private readonly UserService $service = new UserService()
    ) {
    }

    public function index(Request $request): void
    {
        Response::ok($this->service->list());
    }

    public function store(Request $request): void
    {
        try {
            Response::created($this->service->create($request->body()));
        } catch (InvalidArgumentException $exception) {
            Response::fail('VALIDATION_ERROR', $exception->getMessage(), 422);
        }
    }

    public function update(Request $request): void
    {
        try {
            Response::ok($this->service->update((int) $request->route('id', 0), $request->body()));
        } catch (InvalidArgumentException $exception) {
            Response::fail('VALIDATION_ERROR', $exception->getMessage(), 422);
        }
    }

    public function destroy(Request $request): void
    {
        if (!$this->service->delete((int) $request->route('id', 0))) {
            Response::fail('USER_NOT_FOUND', 'El usuario no existe.', 404);
        }

        Response::ok(['deleted' => true]);
    }
}
