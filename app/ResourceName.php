<?php

declare(strict_types=1);

namespace Northstar;

final class ResourceName
{
    public static function isValid(string $name): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $name);
    }

    public static function assert(string $name): string
    {
        $name = trim($name);
        if (!self::isValid($name)) {
            throw new \InvalidArgumentException(
                'Resource name must match ^[a-zA-Z0-9_-]{1,64}$'
            );
        }
        return $name;
    }

    public static function sanitizeSuggestion(string $input): string
    {
        $s = strtolower(trim($input));
        $s = preg_replace('/[^a-z0-9_-]+/', '_', $s) ?? '';
        $s = trim($s, '_-');
        if ($s === '') {
            $s = 'loadscreen';
        }
        return substr($s, 0, 64);
    }
}
