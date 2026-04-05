<?php
declare(strict_types=1);

namespace App\Modules\Carwash\Services;

use App\Core\Http\Request;

class CarwashService
{
    private Request $request;
    private object $authContext;

    public function __construct(Request $request, object $authContext)
    {
        $this->request = $request;
        $this->authContext = $authContext;
    }

    /**
     * Abre una caja para transacciones
     * 
     * @param string $motivo Razón de apertura (ej: "Apertura de jornada")
     * @param float $initialAmount Monto inicial en caja
     * @return array Estado de la caja
     * @throws \Exception Si hay error en la apertura
     */
    public function openBox(string $motivo = '', float $initialAmount = 0): array
    {
        $usuario = $this->resolveCompactUser();
        $requestedBoxId = $this->resolveRequestedBoxId();

        return [
            'message' => 'Caja abierta correctamente',
            'box' => [
                'caja_id' => $requestedBoxId ?? 1,
                'requested_caja_id' => $requestedBoxId,
                'usuario' => $usuario['nombre'] ?? 'anonymous',
                'estado' => 'ABIERTA',
                'fecha_hora_apertura' => date('Y-m-d H:i:s'),
                'monto_inicial' => $initialAmount,
                'motivo' => $motivo,
            ],
        ];
    }

    /**
     * Cierra la caja activa
     * 
     * @return array Resumen de cierre
     * @throws \Exception Si hay error en el cierre
     */
    public function closeBox(): array
    {
        return [
            'message' => 'Caja cerrada correctamente',
            'summary' => $this->buildSummary('CERRADA'),
        ];
    }

    /**
     * Cierra parcialmente la caja
     * 
     * @return array Estado de cierre parcial
     * @throws \Exception Si hay error
     */
    public function closePartialBox(): array
    {
        return [
            'message' => 'Caja cerrada parcialmente',
            'summary' => $this->buildSummary('PARCIALMENTE_CERRADA'),
        ];
    }

    /**
     * Obtiene resumen de la caja
     * 
     * @return array Resumen con movimientos
     * @throws \Exception Si hay error
     */
    public function getBoxSummary(): array
    {
        return [
            'summary' => $this->buildSummary('ABIERTA'),
        ];
    }

    private function resolveCompactUser(): array
    {
        $authResult = $this->authContext->resolve($this->request);

        if (!($authResult['success'] ?? false)) {
            throw new \Exception($authResult['message'] ?? 'Usuario no autenticado');
        }

        return $authResult['compact_user'] ?? [];
    }

    private function resolveRequestedBoxId(): ?int
    {
        $legacyParams = $this->request->input('_parametro', []);

        if (!is_array($legacyParams)) {
            return null;
        }

        $boxId = (int) ($legacyParams['idCaja'] ?? 0);

        return $boxId > 0 ? $boxId : null;
    }

    private function buildSummary(string $status): array
    {
        $usuario = $this->resolveCompactUser();
        $requestedBoxId = $this->resolveRequestedBoxId();
        $now = date('Y-m-d H:i:s');

        return [
            'id' => $requestedBoxId ?? 1,
            'caja_id' => $requestedBoxId ?? 1,
            'estado' => $status,
            'usuario' => $usuario['nombre'] ?? 'anonymous',
            'usuario_apertura' => $usuario['id'] ?? null,
            'usuario_cierre' => $usuario['id'] ?? null,
            'NusuarioApertura' => $usuario['nombre'] ?? 'anonymous',
            'NusuarioCierre' => $usuario['nombre'] ?? 'anonymous',
            'fecha_apertura' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'fecha_cierre' => $now,
            'base' => 500000,
            'sub_total_venta' => 2100840,
            'total_iva' => 399160,
            'total_descuento' => 0,
            'total_venta' => 2500000,
            'efectivo' => 1300000,
            'pagos' => 2500000,
            'creditos' => 350000,
            'recaudos' => 200000,
            'total_gastos' => 150000,
            'id_cierre_total' => 1,
            'ingresoEfectivo' => 1300000,
            'recaudos_externos' => 0,
            'arrPagos' => [
                [
                    'nombrepago' => 'Efectivo',
                    'total' => 1300000,
                ],
                [
                    'nombrepago' => 'Tarjeta',
                    'total' => 1200000,
                ],
            ],
        ];
    }
}
