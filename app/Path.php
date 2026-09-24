<?php

declare(strict_types=1);

namespace Northstar;

final class Path
{
    public static function join(string ...$parts): string
    {
        $parts = array_values(array_filter($parts, static fn ($p) => $p !== ''));
        if ($parts === []) {
            return '';
        }
        $first = array_shift($parts);
        $rest = array_map(static fn ($p) => trim(str_replace('\\', '/', $p), '/'), $parts);
        return rtrim(str_replace('\\', '/', $first), '/') . ($rest !== [] ? '/' . implode('/', $rest) : '');
    }

    public static function assertInside(string $base, string $target): string
    {
        $baseReal = realpath($base);
        if ($baseReal === false) {
            throw new \RuntimeException('Invalid base path.');
        }
        $targetReal = realpath($target);
        if ($targetReal === false) {
            // For paths that do not exist yet, resolve parent
            $parent = realpath(dirname($target));
            $leaf = basename($target);
            if ($parent === false) {
                throw new \RuntimeException('Invalid target path.');
            }
            $targetReal = $parent . DIRECTORY_SEPARATOR . $leaf;
        }
        $baseReal = rtrim(str_replace('\\', '/', $baseReal), '/');
        $targetNorm = str_replace('\\', '/', $targetReal);
        if ($targetNorm !== $baseReal && !str_starts_with($targetNorm, $baseReal . '/')) {
            throw new \RuntimeException('Path traversal blocked.');
        }
        return $targetReal;
    }

    public static function containsTraversal(string $path): bool
    {
        $norm = str_replace('\\', '/', $path);
        return str_contains($norm, '..') || str_starts_with($norm, '/') || str_contains($norm, ':');
    }
}
