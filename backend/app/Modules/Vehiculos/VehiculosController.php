<?php
declare(strict_types=1);

namespace App\Modules\Vehiculos;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Vehiculos\Services\VehiculosService;

class VehiculosController
{
    public function __construct(
        private Request $request,
        private VehiculosService $service
    ) {
    }

    public function createDocumentForVehicleService(): void
    {
        try {
            $payload = $this->request->input('arraydatos', []);

            if (!is_array($payload)) {
                $payload = [];
            }

            Response::ok($this->service->createDocumentForVehicleService($payload), 200);
        } catch (\Throwable $e) {
            Response::fail(
                'CREAR_DOCUMENTO_POR_SERVICIO_VEHICULO_ERROR',
                $e->getMessage(),
                500
            );
        }
    }
}
