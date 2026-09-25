<?php

declare(strict_types=1);

$config = require dirname(__DIR__, 3) . '/app/bootstrap.php';

\Northstar\Auth::requireLogin();
\Northstar\Security::requireCsrfFromRequest();

$userId = (int) \Northstar\Auth::userId();
$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}
$plan = (string) ($data['plan'] ?? '');

try {
    $newPlan = \Northstar\Entitlement::setPlan($userId, $plan, 'load', 'account', $config);
    $limits = \Northstar\Entitlement::limits($userId, 'load', $config);
    \Northstar\Response::jsonOk([
        'plan' => $newPlan,
        'limits' => $limits,
    ]);
} catch (\InvalidArgumentException $e) {
    \Northstar\Response::jsonError($e->getMessage(), 422, 'validation');
} catch (\Throwable $e) {
    \Northstar\Logger::error('plan change', ['error' => $e->getMessage()]);
    \Northstar\Response::jsonError('Could not update plan.', 400, 'plan');
}
