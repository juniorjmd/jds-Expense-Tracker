<?php
declare(strict_types=1);

namespace App\Core\Http;

final class Response
{
    public static function ok(mixed $data, int $status = 200): void
    {
        self::json([
            'ok' => true,
            'data' => $data,
            'error' => null,
        ], $status);
    }

    public static function created(mixed $data): void
    {
        self::ok($data, 201);
    }

    public static function fail(string $code, string $message, int $status = 400, ?array $meta = null): void
    {
        self::json([
            'ok' => false,
            'data' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'meta' => $meta,
            ],
        ], $status);
    }

    public static function noContent(): void
    {
        http_response_code(204);
        self::sendCors();
        exit;
    }

    public static function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        self::sendCors();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private static function sendCors(): void
    {
        $origin = $_ENV['APP_CORS_ORIGIN'] ?? '*';

        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }
}
