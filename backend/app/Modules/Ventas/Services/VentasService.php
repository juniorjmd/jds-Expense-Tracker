<?php
declare(strict_types=1);

namespace App\Modules\Ventas\Services;

use App\Core\Http\Request;

class VentasService
{
    private Request $request;
    private $authContext;

    public function __construct(Request $request, $authContext)
    {
        $this->request = $request;
        $this->authContext = $authContext;
    }

    public function assignPurchaseCreditPayments(
        int $documentOrder,
        array $payments,
        int $installments = 1,
        int $installmentDays = 30
    ): array {
        $this->ensureAuthenticated();
        $this->validateDocumentOrder($documentOrder);
        $summary = $this->summarizePayments($payments);

        return $this->buildLegacyDocumentResponse([
            'orden_documento' => $documentOrder,
            'tipo_operacion' => 'compra_credito',
            'pagos_registrados' => $summary['count'],
            'valor_total_pagado' => $summary['total'],
            'num_cuotas' => $installments,
            'num_dias_cuotas' => $installmentDays,
            'estado' => 'PAGOS_ASIGNADOS',
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ], $payments);
    }

    public function updatePurchaseCreditPayments(
        int $documentOrder,
        array $payments,
        int $installments = 1,
        int $installmentDays = 30,
        string $externalInvoice = '',
        int $supplierId = 0,
        int $establishmentId = 0,
        string $date = ''
    ): array {
        $this->ensureAuthenticated();
        $this->validateDocumentOrder($documentOrder);
        $summary = $this->summarizePayments($payments);

        return $this->buildLegacyDocumentResponse([
            'orden_documento' => $documentOrder,
            'tipo_operacion' => 'compra_credito_edicion',
            'pagos_registrados' => $summary['count'],
            'valor_total_pagado' => $summary['total'],
            'num_cuotas' => $installments,
            'num_dias_cuotas' => $installmentDays,
            'factura_externa' => $externalInvoice,
            'proveedor' => $supplierId,
            'establecimiento' => $establishmentId,
            'fecha_documento' => $date,
            'estado' => 'PAGOS_ACTUALIZADOS',
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ], $payments);
    }

    public function assignSalesCreditPayments(
        int $documentOrder,
        array $payments,
        int $installments = 1,
        int $installmentDays = 30,
        bool $remision = false
    ): array {
        $this->ensureAuthenticated();
        $this->validateDocumentOrder($documentOrder);
        $summary = $this->summarizePayments($payments);

        return $this->buildLegacyDocumentResponse([
            'orden_documento' => $documentOrder,
            'tipo_operacion' => $remision ? 'remision_credito' : 'venta_credito',
            'pagos_registrados' => $summary['count'],
            'valor_total_pagado' => $summary['total'],
            'num_cuotas' => $installments,
            'num_dias_cuotas' => $installmentDays,
            'remision' => $remision,
            'estado' => 'PAGOS_ASIGNADOS',
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ], $payments);
    }

    public function assignCreditInstallmentPayment(int $documentOrder, array $payments): array
    {
        $this->ensureAuthenticated();
        $this->validateDocumentOrder($documentOrder);
        $summary = $this->summarizePayments($payments);

        return $this->buildLegacyDocumentResponse([
            'orden_documento' => $documentOrder,
            'tipo_operacion' => 'abono_credito',
            'pagos_registrados' => $summary['count'],
            'valor_total_pagado' => $summary['total'],
            'estado' => 'ABONO_REGISTRADO',
            'fecha_actualizacion' => date('Y-m-d H:i:s'),
        ], $payments);
    }

    private function ensureAuthenticated(): void
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \Exception('Usuario no autenticado');
        }
    }

    private function validateDocumentOrder(int $documentOrder): void
    {
        if ($documentOrder <= 0) {
            throw new \Exception('Orden de documento inválida');
        }
    }

    private function summarizePayments(array $payments): array
    {
        if ($payments === []) {
            throw new \Exception('Debe enviar al menos un pago');
        }

        $count = 0;
        $total = 0.0;

        foreach ($payments as $payment) {
            if (!is_array($payment)) {
                continue;
            }

            $amount = (float) ($payment['valorPagado'] ?? 0);
            if ($amount <= 0) {
                continue;
            }

            $count++;
            $total += $amount;
        }

        if ($count === 0) {
            throw new \Exception('Debe enviar pagos con valor mayor a cero');
        }

        return [
            'count' => $count,
            'total' => round($total, 2),
        ];
    }

    private function buildLegacyDocumentResponse(array $documentSummary, array $payments): array
    {
        return [
            'error' => 'ok',
            'numdata' => 1,
            'data' => [
                'documentoFinal' => [
                    'orden' => $documentSummary['orden_documento'],
                    'pagos' => $payments,
                    'estado' => $documentSummary['estado'],
                    'tipoOperacion' => $documentSummary['tipo_operacion'],
                    'pagosRegistrados' => $documentSummary['pagos_registrados'],
                    'valorTotalPagado' => $documentSummary['valor_total_pagado'],
                    'resumen' => $documentSummary,
                ],
            ],
        ];
    }
}
