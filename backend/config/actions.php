<?php

use App\Core\Http\Request;
use App\Modules\Auth\AuthController;

/**
 * |--------------------------------------------------------------------------
 * | Legacy Action Map
 * |--------------------------------------------------------------------------
 *
 * Maps legacy action strings to controller handlers
 * These are used when the request doesn't match the /api/{module}/{method} pattern
 */

return [
    // Auth Module Actions
    'ef2e1d89937fba9f888516293ab1e19e7ed789a5' => function (Request $req) {
        $controller = new AuthController();
        return $controller->login($req);
    },

    '16770d92a6a82ee846f7ff23b4c8ad05b69fba03' => function (Request $req) {
        $controller = new AuthController();
        return $controller->validatekey($req);
    },

    '16770d92a6a82ee8464f678f5f223b4c8ad05b69fba03' => function (Request $req) {
        $controller = new AuthController();
        return $controller->me($req);
    },

    'RESETEAR_USUARIO_PASS' => function (Request $req) {
        $controller = new AuthController();
        return $controller->resetpassword($req);
    },

    'HIJODELAGRANCHINGADA' => function (Request $req) {
        $controller = new AuthController();
        return $controller->setpassword($req);
    },

    'c332258e69e38f18450f9a48c65c89d9e436c561' => function (Request $req) {
        $controller = new AuthController();
        return $controller->logout($req);
    },
];
