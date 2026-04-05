<?php
declare(strict_types=1);

namespace App\Modules\Carwash;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Carwash\Services\CarwashService;

class CarwashController
{
    private Request $request;
    private CarwashService $service;

    public function __construct(
        Request $request,
        CarwashService $service
    ) {
        $this->request = $request;
        $this->service = $service;
    }

    /**
     * Abre una caja para transacciones
     * Soporta parámetros legacy: caja_motivo, caja_monto_inicial
     */
    public function openBox(): void
    {
        try {
            $result = $this->service->openBox(
                motivo: $this->request->input('caja_motivo', ''),
                initialAmount: (float) $this->request->input('caja_monto_inicial', 0)
            );

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('OPEN_BOX_ERROR', $e->getMessage());
        }
    }

    /**
     * Cierra la caja activa
     */
    public function closeBox(): void
    {
        try {
            $result = $this->service->closeBox();

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('CLOSE_BOX_ERROR', $e->getMessage());
        }
    }

    /**
     * Cierra parcialmente la caja
     */
    public function closePartialBox(): void
    {
        try {
            $result = $this->service->closePartialBox();

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('CLOSE_PARTIAL_BOX_ERROR', $e->getMessage());
        }
    }

    /**
     * Obtiene resumen de la caja
     */
    public function getBoxSummary(): void
    {
        try {
            $result = $this->service->getBoxSummary();

            Response::ok($result);
        } catch (\Exception $e) {
            Response::fail('GET_BOX_SUMMARY_ERROR', $e->getMessage());
        }
    }
}
