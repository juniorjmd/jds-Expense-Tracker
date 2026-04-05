<?php

declare(strict_types=1);

namespace App\Core\Routing;

use App\Core\Http\Request;
use App\Core\Http\Response;

final class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->map('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->map('DELETE', $path, $handler);
    }

    private function map(string $method, string $path, callable $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(): void
    {
        $request = new Request();

        if ($request->method() === 'OPTIONS') {
            Response::json(['ok' => true], 200);
            return;
        }

        $handler = $this->routes[$request->method()][$request->uri()] ?? null;

        if (!$handler) {
            Response::json([
                'ok' => false,
                'message' => 'Route not found',
                'path' => $request->uri(),
                'method' => $request->method(),
            ], 404);
            return;
        }

        $handler($request);
    }
}
