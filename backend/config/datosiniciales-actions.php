<?php
declare(strict_types=1);

use App\Modules\DatosIniciales\DatosInicialesController;

return [
    'GET_SUCURSAL_PRINCIPAL_DATA' => [DatosInicialesController::class, 'getPrincipalBranchData'],
    '52444d9072f7ec12a26cb2879ebb4ab0bf5aa553' => [DatosInicialesController::class, 'changePasswordWithSession'],
    '52444d9072f7ec12aJEE8FFJJKVNASDHQWFLKA' => [DatosInicialesController::class, 'setPasswordByUserCode'],
    '23929870008e23007350be74a708ab3a806dce13' => [DatosInicialesController::class, 'generateSimulationPdf'],
    '8e9ae038c37d3b59fc1eed456c77aefb5eadffea' => [DatosInicialesController::class, 'getSimulationResults'],
    '99c505a66a9d8a984059baf1b99bb9e6456ae4bb' => [DatosInicialesController::class, 'assignQuestionsToForm'],
];
