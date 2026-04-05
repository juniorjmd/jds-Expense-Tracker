<?php
declare(strict_types=1);

use App\Modules\Documentos\DocumentosController;

return [
    'GET_DOCUMENTOS_USUARIO_ACTUAL' => [DocumentosController::class, 'getCurrentUserDocuments'],
    'GET_DOCUMENTOS_USUARIO_ACTUAL_CAJA_ACTIVA' => [DocumentosController::class, 'getCurrentUserDocumentsByActiveBox'],
    'CREAR_DOCUMENTO_POR_USUARIO' => [DocumentosController::class, 'createCurrentUserDocument'],
    'CREAR_DOCUMENTO_COMPRA_POR_USUARIO' => [DocumentosController::class, 'createCurrentUserPurchaseDocument'],
    'CAMBIAR_DOCUMENTO_ACTIVO_POR_USUARIO' => [DocumentosController::class, 'changeCurrentUserDocument'],
    'CAMBIAR_DOCUMENTO_COMPRA_ACTIVO_POR_USUARIO' => [DocumentosController::class, 'changeCurrentUserPurchaseDocument'],
    'LISTAR_DOCUMENTOS' => [DocumentosController::class, 'listDocuments'],
    'SUBIR_DOCUMENTO' => [DocumentosController::class, 'uploadDocument'],
    'DESCARGAR_DOCUMENTO' => [DocumentosController::class, 'downloadDocument'],
    'BORRAR_DOCUMENTO' => [DocumentosController::class, 'deleteDocument'],
];
