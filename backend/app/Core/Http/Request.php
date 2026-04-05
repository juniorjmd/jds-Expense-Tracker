<?php
declare(strict_types=1);

namespace App\Core\Http;

final class Request
{
    public function __construct(
        private string $method,
        private string $path,
        private array $headers,
        private array $body,
        private array $query,
        private array $server,
        private array $routeParams = []
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = (string) parse_url($uri, PHP_URL_PATH);
        $headers = self::getHeaders();
        $query = $_GET ?? [];
        $body = self::parseBody($headers);

        return new self($method, $path !== '' ? $path : '/', $headers, $body, $query, $_SERVER ?? []);
    }

    public function withRouteParams(array $routeParams): self
    {
        $clone = clone $this;
        $clone->routeParams = $routeParams;

        return $clone;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function body(): array
    {
        return $this->body;
    }

    public function query(): array
    {
        return $this->query;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body, $this->routeParams);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key]
            ?? $this->query[$key]
            ?? $this->routeParams[$key]
            ?? $default;
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $authorization = $this->headers['authorization'] ?? null;
        if ($authorization === null) {
            return null;
        }

        if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches) !== 1) {
            return null;
        }

        return trim($matches[1]) !== '' ? trim($matches[1]) : null;
    }

    private static function parseBody(array $headers): array
    {
        $rawBody = file_get_contents('php://input') ?: '';
        if ($rawBody === '') {
            return $_POST ?? [];
        }

        $contentType = $headers['content-type'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode($rawBody, true);
            return is_array($decoded) ? $decoded : [];
        }

        parse_str($rawBody, $parsed);
        return is_array($parsed) && $parsed !== [] ? $parsed : ($_POST ?? []);
    }

    private static function getHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (!str_starts_with($key, 'HTTP_')) {
                continue;
            }

            $normalized = strtolower(str_replace('_', '-', substr($key, 5)));
            $headers[$normalized] = (string) $value;
        }

        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = (string) $_SERVER['CONTENT_TYPE'];
        }

        return $headers;
    }
}
