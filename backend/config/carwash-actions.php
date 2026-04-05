<?php

/**
 * Mapea acciones legacy del módulo Carwash
 * 
 * Estructura: 'hash_accion' => [ControllerClass::class, 'methodName']
 * El Router usa este mapeo para resolver acciones sin /api en la URL
 * 
 * Las acciones se cargan en Routes::map() y se usan en Router::dispatchLegacyAction()
 */

use App\Modules\Carwash\CarwashController;

return [
    'ABRIR_CAJA_ACTIVA' => [CarwashController::class, 'openBox'],
    'CERRAR_CAJA_ACTIVA' => [CarwashController::class, 'closeBox'],
    'CERRAR_CAJA_PARCIAL' => [CarwashController::class, 'closePartialBox'],
    'OBTENER_RESUMEN_CAJA' => [CarwashController::class, 'getBoxSummary'],
];
