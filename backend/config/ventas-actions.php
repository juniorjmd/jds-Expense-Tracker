<?php
declare(strict_types=1);

use App\Modules\Ventas\VentasController;

return [
    'ASIGNAR_PAGOS_DOCUMENTOS_COMPRA_CREDITO' => [VentasController::class, 'assignPurchaseCreditPayments'],
    'ASIGNAR_PAGOS_DOCUMENTOS_COMPRA_CREDITO_EDICION' => [VentasController::class, 'updatePurchaseCreditPayments'],
    'ASIGNAR_PAGOS_DOCUMENTOS_CREDITO' => [VentasController::class, 'assignSalesCreditPayments'],
    'ASIGNAR_ABONO_DOCUMENTOS_CREDITO' => [VentasController::class, 'assignCreditInstallmentPayment'],
];
