<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Services\CurrentUserService;
use App\Services\CategoryService;
use InvalidArgumentException;

final class CategoryController
{
    public function __construct(
        private readonly CategoryService $service = new CategoryService(),
        private readonly CurrentUserService $currentUser = new CurrentUserService()
    ) {
    }

    public function index(Request $request): void
    {
        try {
            Response::ok($this->service->list(
                $this->currentUser->require($request),
                $request->input('type'),
                $request->input('establishmentId') !== null ? (int) $request->input('establishmentId') : null
            ));
        } catch (InvalidArgumentException $exception) {
            Response::fail('VALIDATION_ERROR', $exception->getMessage(), 422);
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
}
