<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Services\CategoryService;

final class CategoryController
{
    public function __construct(
        private readonly CategoryService $service = new CategoryService()
    ) {
    }

    public function index(Request $request): void
    {
        Response::ok($this->service->list($request->input('type')));
    }
}
