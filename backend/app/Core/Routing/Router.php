<?php
declare(strict_types=1);

namespace App\Core\Routing;

use App\Core\Http\Request;
use App\Core\Http\Response;

final class Router
{
    public function __construct(
        private array $routes
    ) {
    }

    public function dispatch(Request $request): void
    {
        foreach ($this->routes as [$method, $pattern, $handler]) {
            if (strtoupper($method) !== $request->method()) {
                continue;
            }

            $params = $this->match($pattern, $request->path());
            if ($params === null) {
                continue;
            }

            $resolvedRequest = $request->withRouteParams($params);
            $response = $handler($resolvedRequest);

            if ($response !== null) {
                Response::ok($response);
            }

            return;
        }

        Response::fail('ROUTE_NOT_FOUND', 'Ruta no encontrada', 404);
    }

    private function match(string $pattern, string $path): ?array
    {
        if ($pattern === '/' && $path === '/') {
            return [];
        }

        $patternSegments = explode('/', trim($pattern, '/'));
        $pathSegments = explode('/', trim($path, '/'));

        if (count($patternSegments) !== count($pathSegments)) {
            return null;
        }

        $params = [];
        foreach ($patternSegments as $index => $segment) {
            $candidate = $pathSegments[$index] ?? '';

            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $segment, $matches) === 1) {
                $params[$matches[1]] = $candidate;
                continue;
            }

            if ($segment !== $candidate) {
                return null;
            }
        }

        return $params;
    }
}
