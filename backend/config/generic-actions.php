<?php
declare(strict_types=1);

use App\Core\Http\Request;
use App\Modules\Auth\AuthContext;
use App\Modules\LegacyCrud\LegacyCrudController;
use App\Modules\LegacyCrud\LegacyCrudRepository;
use App\Modules\LegacyCrud\LegacyCrudService;

$buildController = static function (Request $request): LegacyCrudController {
    return new LegacyCrudController(
        new LegacyCrudService(
            $request,
            new LegacyCrudRepository(),
            new AuthContext()
        )
    );
};

return [
    'DATABASE_GENERIC_CONTRUCT_SELECT' => function (Request $request) use ($buildController) {
        $buildController($request)->select();
    },
    'DATABASE_GENERIC_CONTRUCT_SELECT_BY_USER_LOGGED' => function (Request $request) use ($buildController) {
        $buildController($request)->selectByLoggedUser();
    },
    'e06c06e7e4ef58bdb0kieujfñ541b3017fdd35473' => function (Request $request) use ($buildController) {
        $buildController($request)->selectMany();
    },
    'DATABASE_GENERIC_CONTRUCT_INSERT' => function (Request $request) use ($buildController) {
        $buildController($request)->insert();
    },
    'DATABASE_GENERIC_CONTRUCT_UPDATE' => function (Request $request) use ($buildController) {
        $buildController($request)->update();
    },
    'DATABASE_GENERIC_CONTRUCT_DELETE' => function (Request $request) use ($buildController) {
        $buildController($request)->delete();
    },
    'DATABASE_GENERIC_CONTRUCT_PROCEDURE' => function (Request $request) use ($buildController) {
        $buildController($request)->procedure();
    },
    'DATABASE_GENERIC_CONTRUCT_INSERT_SELECT' => function (Request $request) use ($buildController) {
        $buildController($request)->insertSelect();
    },
    'INSERT_PERFIL_USUARIO' => function (Request $request) use ($buildController) {
        $buildController($request)->assignUserProfile();
    },
    'mnbvcxzxcxcxasdfewq15616' => function (Request $request) use ($buildController) {
        $buildController($request)->boxesByUser();
    },
    'qwer12356yhn7ujm8ik' => function (Request $request) use ($buildController) {
        $buildController($request)->assignBoxesToUser();
    },
    'BUSCAR_STOCK_LOCATION' => function (Request $request) use ($buildController) {
        $buildController($request)->searchStockLocations();
    },
];
