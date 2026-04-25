<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Services\CurrentUserService;
use App\Services\UserService;
use InvalidArgumentException;

final class UserController
{
    public function __construct(
        private readonly UserService $service = new UserService(),
        private readonly CurrentUserService $currentUser = new CurrentUserService()
    ) {
    }

    public function index(Request $request): void
    {
        try {
            Response::ok($this->service->list($this->currentUser->require($request)));
        } catch (InvalidArgumentException $exception) {
            Response::fail('AUTHORIZATION_ERROR', $exception->getMessage(), 403);
        }
    }

    public function store(Request $request): void
    {
        try {
            Response::created($this->service->create($this->currentUser->require($request), $request->body()));
        } catch (InvalidArgumentException $exception) {
            Response::fail('VALIDATION_ERROR', $exception->getMessage(), 422);
        }
    }

    public function update(Request $request): void
    {
        try {
            Response::ok($this->service->update($this->currentUser->require($request), (int) $request->route('id', 0), $request->body()));
        } catch (InvalidArgumentException $exception) {
            Response::fail('VALIDATION_ERROR', $exception->getMessage(), 422);
        }
    }

    public function changePassword(Request $request): void
    {
        try {
            Response::ok($this->service->changePassword($this->currentUser->require($request), $request->body()));
        } catch (InvalidArgumentException $exception) {
            Response::fail('VALIDATION_ERROR', $exception->getMessage(), 422);
        }
    }

    public function destroy(Request $request): void
    {
        try {
            if (!$this->service->delete($this->currentUser->require($request), (int) $request->route('id', 0))) {
                Response::fail('USER_NOT_FOUND', 'El usuario no existe.', 404);
            }

            Response::ok(['deleted' => true]);
        } catch (InvalidArgumentException $exception) {
            Response::fail('VALIDATION_ERROR', $exception->getMessage(), 422);
        }
    }
}
