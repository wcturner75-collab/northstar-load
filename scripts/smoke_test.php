<?php

declare(strict_types=1);

/**
 * End-to-end smoke test for Northstar Load core pipeline (hosted ZIP).
 */

$config = require __DIR__ . '/../app/bootstrap.php';

$email = 'smoke_' . bin2hex(random_bytes(3)) . '@example.com';
$user = 'smoke_' . bin2hex(random_bytes(2));
$pass = 'TestPass123!';

echo "Register...\n";
\Northstar\Auth::register($email, $user, $pass);
$userId = (int) \Northstar\Auth::userId();
echo "user_id={$userId}\n";

echo "Create project...\n";
$project = \Northstar\Project::create($userId, 'Sierra Roleplay', 'sirp_loading', null, $config);
$pid = (int) $project['id'];
echo "project_id={$pid}\n";

echo "Save config...\n";
$cfg = $project['config'];
$cfg['server']['name'] = 'Sierra Roleplay';
$cfg['server']['tagline'] = 'Smoke Test';
\Northstar\Project::saveConfig($pid, $userId, $cfg, false);

echo "Generate resource...\n";
$result = \Northstar\ResourceGenerator::generate($pid, $userId, $config);
echo "token={$result['buildToken']}\n";
echo "zip={$result['downloadUrl']}\n";
echo "loadUrl={$result['loadUrl']}\n";

if (empty($result['loadUrl']) || !str_contains((string) $result['loadUrl'], '/load.php?t=')) {
    fwrite(STDERR, "Missing hosted loadUrl\n");
    exit(1);
}

$zipPath = $config['paths']['builds'] . '/' . $result['buildToken'] . '.zip';
if (!is_file($zipPath)) {
    fwrite(STDERR, "ZIP missing\n");
    exit(1);
}

$zip = new ZipArchive();
$zip->open($zipPath);
$names = [];
for ($i = 0; $i < $zip->numFiles; $i++) {
    $names[] = $zip->getNameIndex($i);
}
$manifest = $zip->getFromName('sirp_loading/fxmanifest.lua');
$zip->close();

$need = [
    'sirp_loading/fxmanifest.lua',
    'sirp_loading/client.lua',
    'sirp_loading/README.txt',
];
foreach ($need as $n) {
    if (!in_array($n, $names, true)) {
        fwrite(STDERR, "Missing in ZIP: {$n}\n");
        exit(1);
    }
}

if (!is_string($manifest) || !str_contains($manifest, (string) $result['loadUrl'])) {
    fwrite(STDERR, "fxmanifest missing load URL\n");
    exit(1);
}

// Bundled web assets must NOT be in hosted ZIP
foreach ($names as $n) {
    if (str_contains($n, '/web/') || str_ends_with($n, 'config.json')) {
        fwrite(STDERR, "Hosted ZIP should not include: {$n}\n");
        exit(1);
    }
}

echo "ZIP entries: " . count($names) . "\n";
echo "OK\n";
