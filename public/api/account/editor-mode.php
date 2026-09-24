<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 3) . '/app/bootstrap.php';

\Northstar\Auth::requireLogin();
\Northstar\Security::requireCsrfFromRequest();

$userId = (int) \Northstar\Auth::userId();
$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
$mode = (string) ($data['editor_mode'] ?? ($_POST['editor_mode'] ?? ''));

try {
    \Northstar\Auth::setEditorMode($userId, $mode);
    \Northstar\Response::jsonOk(['editor_mode' => $mode]);
} catch (\InvalidArgumentException $e) {
    \Northstar\Response::jsonError($e->getMessage(), 422, 'validation');
} catch (\Throwable $e) {
    \Northstar\Response::jsonError('Could not update preference.', 400, 'update');
}
