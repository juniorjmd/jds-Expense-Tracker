<?php
declare(strict_types=1);

namespace App\Modules\Ventas;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Modules\Ventas\Services\VentasService;

class VentasController
{
    private Request $request;
    private VentasService $service;

    public function __construct(Request $request, VentasService $service)
    {
        $this->request = $request;
        $this->service = $service;
    }

    public function assignPurchaseCreditPayments(): void
    {
        try {
            $result = $this->service->assignPurchaseCreditPayments(
                documentOrder: (int) $this->request->input('ordenDocumento', 0),
                payments: $this->normalizePayments($this->request->input('pagos', [])),
                installments: (int) $this->request->input('numCuotas', 1),
                installmentDays: (int) $this->request->input('numDiasCuotas', 30)
            );

            (new Response())
                ->status(200)
                ->json($result)
                ->send();
        } catch (\Exception $e) {
            (new Response())
                ->status(400)
                ->json(['error' => $e->getMessage()])
                ->send();
        }
    }

    public function updatePurchaseCreditPayments(): void
    {
        try {
            $result = $this->service->updatePurchaseCreditPayments(
                documentOrder: (int) $this->request->input('ordenDocumento', 0),
                payments: $this->normalizePayments($this->request->input('pagos', [])),
                installments: (int) $this->request->input('numCuotas', 1),
                installmentDays: (int) $this->request->input('numDiasCuotas', 30),
                externalInvoice: (string) $this->request->input('facturaExterna', ''),
                supplierId: (int) $this->request->input('proveedor', 0),
                establishmentId: (int) $this->request->input('establecimiento', 0),
                date: (string) $this->request->input('fecha', '')
            );

            (new Response())
                ->status(200)
                ->json($result)
                ->send();
        } catch (\Exception $e) {
            (new Response())
                ->status(400)
                ->json(['error' => $e->getMessage()])
                ->send();
        }
    }

    public function assignSalesCreditPayments(): void
    {
        try {
            $result = $this->service->assignSalesCreditPayments(
                documentOrder: (int) $this->request->input('ordenDocumento', 0),
                payments: $this->normalizePayments($this->request->input('pagos', [])),
                installments: (int) $this->request->input('numCuotas', 1),
                installmentDays: (int) $this->request->input('numDiasCuotas', 30),
                remision: (bool) $this->request->input('remision', false)
            );

            (new Response())
                ->status(200)
                ->json($result)
                ->send();
        } catch (\Exception $e) {
            (new Response())
                ->status(400)
                ->json(['error' => $e->getMessage()])
                ->send();
        }
    }

    public function assignCreditInstallmentPayment(): void
    {
        try {
            $result = $this->service->assignCreditInstallmentPayment(
                documentOrder: (int) $this->request->input('ordenDocumento', 0),
                payments: $this->normalizePayments($this->request->input('pagos', []))
            );

            (new Response())
                ->status(200)
                ->json($result)
                ->send();
        } catch (\Exception $e) {
            (new Response())
                ->status(400)
                ->json(['error' => $e->getMessage()])
                ->send();
        }
    }

    private function normalizePayments(mixed $payments): array
    {
        if (is_string($payments)) {
            $decoded = json_decode($payments, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($payments) ? $payments : [];
    }
}
