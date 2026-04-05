<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Services\EstablishmentService;
use InvalidArgumentException;

final class EstablishmentController
{
    public function __construct(
        private readonly EstablishmentService $service = new EstablishmentService()
    ) {
    }

    public function index(Request $request): void
    {
        Response::ok($this->service->list($request->input('month')));
    }

    public function show(Request $request): void
    {
        $establishment = $this->service->show((int) $request->route('id', 0), $request->input('month'));
        if ($establishment === null) {
            Response::fail('ESTABLISHMENT_NOT_FOUND', 'El establecimiento no existe.', 404);
        }

        Response::ok($establishment);
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
        if (!$this->service->delete((int) $request->route('id', 0))) {
            Response::fail('ESTABLISHMENT_NOT_FOUND', 'El establecimiento no existe.', 404);
        }

        Response::ok(['deleted' => true]);
    }
}
