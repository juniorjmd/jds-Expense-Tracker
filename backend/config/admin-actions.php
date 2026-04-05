<?php

/**
 * Mapea acciones legacy del módulo Admin
 *
 * Estructura: 'hash_accion' => [ControllerClass::class, 'methodName']
 * El Router usa este mapeo para resolver acciones sin /api en la URL
 */

use App\Modules\Admin\AdminController;

return [
    'GET_ALL_RECURSOS' => [AdminController::class, 'getAllResources'],
    'SET_PERFIL_RECURSO' => [AdminController::class, 'setProfileResources'],
    'GET_ALL_RECURSOS_BY_PERFIL' => [AdminController::class, 'getAllResourcesByProfile'],
    'OBTENER_USUARIOS' => [AdminController::class, 'getUsers'],
    'CREAR_USUARIO' => [AdminController::class, 'createUser'],
    'ACTUALIZAR_USUARIO' => [AdminController::class, 'updateUser'],
    'OBTENER_MENUS' => [AdminController::class, 'getMenus'],
    'CREAR_OPERACION_MANUAL' => [AdminController::class, 'createManualOperation'],
    'CREAR_OPERACIONES_PREESTABLECIDAS' => [AdminController::class, 'createPresetOperation'],
    'EJECUTAR_OPERACIONES_PREESTABLECIDAS' => [AdminController::class, 'executePresetOperation'],
];
