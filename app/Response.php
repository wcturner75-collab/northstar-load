<?php

declare(strict_types=1);

namespace Northstar;

// Re-export Response at root namespace for simpler use in pages
// Actual class lives in Northstar\Response via alias below

final class Response
{
    /** @param array<string,mixed> $data */
    public static function jsonOk(array $data = [], int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function jsonError(string $message, int $status = 400, string $code = 'error'): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'error' => ['code' => $code, 'message' => $message],
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
