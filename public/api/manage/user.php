<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 3) . '/app/bootstrap.php';

\Northstar\Manage::requireStaff();
\Northstar\Security::requireCsrfFromRequest();

$userId = (int) (\Northstar\Auth::userId() ?? 0);
$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$targetId = (int) ($data['user_id'] ?? 0);
$action = (string) ($data['action'] ?? '');

if ($targetId <= 0 || $targetId === $userId) {
    \Northstar\Response::jsonError('Invalid user.', 422, 'validation');
}

try {
    if ($action === 'status') {
        \Northstar\Manage::setUserStatus($targetId, (string) ($data['status'] ?? ''));
    } elseif ($action === 'role') {
        \Northstar\Manage::setUserRole($targetId, (string) ($data['role'] ?? ''));
    } else {
        throw new InvalidArgumentException('Unknown action.');
    }
    \Northstar\Response::jsonOk(['user_id' => $targetId]);
} catch (InvalidArgumentException $e) {
    \Northstar\Response::jsonError($e->getMessage(), 422, 'validation');
} catch (Throwable $e) {
    \Northstar\Logger::error('manage user', ['error' => $e->getMessage()]);
    \Northstar\Response::jsonError($e->getMessage(), 400, 'manage');
}
