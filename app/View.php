<?php

declare(strict_types=1);

namespace Northstar;

final class View
{
    /** @param array<string,mixed> $vars */
    public static function render(string $template, array $vars = []): void
    {
        extract($vars, EXTR_SKIP);
        $config = $GLOBALS['ns_config'] ?? [];
        $user = Auth::user();
        $csrf = Security::csrfToken();
        require NORTHSTAR_ROOT . '/app/views/layout_header.php';
        require NORTHSTAR_ROOT . '/app/views/' . $template . '.php';
        require NORTHSTAR_ROOT . '/app/views/layout_footer.php';
    }

    /** @param array<string,mixed> $vars */
    public static function renderBare(string $template, array $vars = []): void
    {
        extract($vars, EXTR_SKIP);
        $config = $GLOBALS['ns_config'] ?? [];
        $user = Auth::user();
        $csrf = Security::csrfToken();
        require NORTHSTAR_ROOT . '/app/views/' . $template . '.php';
    }
}
