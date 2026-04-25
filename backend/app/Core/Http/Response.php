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
        self::logFailure($code, $message, $status, $meta);

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
        $origin = self::resolveAllowedOrigin();
        header('Vary: Origin');
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-User-Id, X-Company-Id');
    }

    private static function logFailure(string $code, string $message, int $status, ?array $meta): void
    {
        $logDirectory = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($logDirectory)) {
            @mkdir($logDirectory, 0777, true);
        }

        $logFile = $logDirectory . DIRECTORY_SEPARATOR . 'app.log';
        $payload = [
            'time' => date('c'),
            'status' => $status,
            'code' => $code,
            'message' => $message,
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'userId' => $_SERVER['HTTP_X_USER_ID'] ?? null,
            'companyId' => $_SERVER['HTTP_X_COMPANY_ID'] ?? null,
            'meta' => $meta,
        ];

        @file_put_contents(
            $logFile,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND
        );
    }

    private static function resolveAllowedOrigin(): string
    {
        $configured = trim((string) ($_ENV['APP_CORS_ALLOWED_ORIGINS'] ?? ($_ENV['APP_CORS_ORIGIN'] ?? '*')));
        if ($configured === '' || $configured === '*') {
            return '*';
        }

        $requestOrigin = trim((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));
        $allowedOrigins = array_values(array_filter(array_map(
            static fn (string $item): string => trim($item),
            explode(',', $configured)
        )));

        if ($requestOrigin !== '' && in_array($requestOrigin, $allowedOrigins, true)) {
            return $requestOrigin;
        }

        return $allowedOrigins[0] ?? '*';
    }
}
