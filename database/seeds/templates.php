<?php

declare(strict_types=1);

/**
 * Seed starter templates.
 * Usage: php database/seeds/templates.php
 */

$config = require dirname(__DIR__, 2) . '/app/bootstrap.php';

$cinematic = \Northstar\BuilderConfigValidator::defaultConfig('My Server', 'cinematic');
$minimal = \Northstar\BuilderConfigValidator::defaultConfig('My Server', 'minimal');
$minimal['theme']['accent'] = '#E8E6E1';
$minimal['background']['overlay']['opacity'] = 0.2;
$minimal['components'] = array_values(array_filter(
    $minimal['components'],
    static fn ($c) => in_array($c['type'], ['serverName', 'loadingBar', 'loadingStatus'], true)
));
$minimal['layersOrder'] = array_column($minimal['components'], 'id');

$neon = \Northstar\BuilderConfigValidator::defaultConfig('Night City RP', 'neon');
$neon['theme']['accent'] = '#3DDC97';
$neon['background']['color'] = '#05080F';

$pdo = \Northstar\Database::pdo();
$pdo->exec('DELETE FROM templates');

$ins = $pdo->prepare(
    'INSERT INTO templates (product_key, slug, name, description, theme_key, config_json, sort_order)
     VALUES (\'load\', ?, ?, ?, ?, ?, ?)'
);

$rows = [
    ['cinematic', 'Cinematic', 'Full branding with gold accent and status bar.', 'cinematic', $cinematic, 1],
    ['minimal', 'Minimal', 'Name + loading only. Clean and fast.', 'minimal', $minimal, 2],
    ['neon', 'Neon Night', 'Dark stage with teal accents for nightlife servers.', 'neon', $neon, 3],
];

foreach ($rows as $r) {
    $ins->execute([
        $r[0],
        $r[1],
        $r[2],
        $r[3],
        json_encode($r[4], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        $r[5],
    ]);
}

echo "Seeded " . count($rows) . " templates.\n";
