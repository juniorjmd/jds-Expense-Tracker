<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Services\CompanyService;
use App\Services\CurrentUserService;
use InvalidArgumentException;

final class CompanyController
{
    public function __construct(
        private readonly CompanyService $service = new CompanyService(),
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

    public function show(Request $request): void
    {
        try {
            Response::ok(
                $this->service->overview(
                    $this->currentUser->require($request),
                    (int) $request->route('id', 0),
                    (string) $request->input('month', date('Y-m'))
                )
            );
        } catch (InvalidArgumentException $exception) {
            $message = $exception->getMessage();
            $status = str_contains($message, 'no existe') ? 404 : 403;
            $code = $status === 404 ? 'COMPANY_NOT_FOUND' : 'AUTHORIZATION_ERROR';
            Response::fail($code, $message, $status);
        }
    }
}
