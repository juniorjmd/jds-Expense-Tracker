<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Services\AuthService;
use InvalidArgumentException;

final class AuthController
{
    public function __construct(
        private readonly AuthService $service = new AuthService()
    ) {
    }

    public function login(Request $request): void
    {
        try {
            Response::ok($this->service->login($request->body()));
        } catch (InvalidArgumentException $exception) {
            Response::fail('AUTH_ERROR', $exception->getMessage(), 422);
        }
    }
}
