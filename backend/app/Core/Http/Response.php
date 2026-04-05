<?php

declare(strict_types=1);

namespace App\Core\Http;

final class Response
{
    public static function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    public static function noContent(): void
    {
        http_response_code(204);
    }
}
