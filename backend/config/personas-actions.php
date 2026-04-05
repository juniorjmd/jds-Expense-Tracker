<?php
declare(strict_types=1);

use App\Modules\Personas\PersonasController;

return [
    'BUSCAR_ODOO_TITULO_PERSONA' => [PersonasController::class, 'searchOdooPersonTitle'],
    'GET_MAESTROS_CLIENTES' => [PersonasController::class, 'getClientMasters'],
];
