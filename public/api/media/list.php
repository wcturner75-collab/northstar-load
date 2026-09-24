<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 3) . '/app/bootstrap.php';

\Northstar\Auth::requireLogin();
$userId = (int) \Northstar\Auth::userId();
$kind = isset($_GET['kind']) ? (string) $_GET['kind'] : null;
if ($kind !== null && !in_array($kind, ['image', 'audio', 'video'], true)) {
    $kind = null;
}

\Northstar\Response::jsonOk([
    'media' => \Northstar\MediaManager::listForUser($userId, $kind),
]);
