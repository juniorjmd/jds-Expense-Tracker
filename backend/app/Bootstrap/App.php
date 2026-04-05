<?php
declare(strict_types=1);

namespace App\Bootstrap;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Routing\Router;
use Dotenv\Dotenv;
use Throwable;

final class App
{
    public static function run(): void
    {
        self::loadEnvironment();

        $request = Request::fromGlobals();

        if ($request->method() === 'OPTIONS') {
            Response::noContent();
        }

        try {
            $router = new Router(Routes::definitions());
            $router->dispatch($request);
        } catch (Throwable $exception) {
            $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';

            Response::fail(
                'INTERNAL_ERROR',
                'Error interno del servidor',
                500,
                $debug ? [
                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ] : null
            );
        }
    }

    private static function loadEnvironment(): void
    {
        $envFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        if (!is_file($envFile)) {
            return;
        }

        Dotenv::createImmutable(dirname(__DIR__, 2))->safeLoad();
    }
}

