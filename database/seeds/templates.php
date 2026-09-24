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

$dual = \Northstar\BuilderConfigValidator::defaultConfig('My Server', 'dual_panel');
$dual['theme']['preset'] = 'dual_panel';
$dual['theme']['layout'] = 'dual_panel';
$dual['theme']['accent'] = '#C62828';
$dual['theme']['fonts'] = ['display' => 'Source Sans 3', 'body' => 'Source Sans 3'];
$dual['theme']['colors'] = [
    'text' => '#FFFFFF',
    'muted' => '#F0D0D0',
    'panel' => 'rgba(40,0,0,0.55)',
];
$dual['background']['color'] = '#B71C1C';
$dual['background']['overlay']['opacity'] = 0.22;
$dual['server']['map'] = 'RP_Map';
$dual['server']['slots'] = 64;
$dual['server']['mode'] = 'Roleplay';
$dual['content']['rules'] = [
    ['title' => 'Respect', 'body' => 'Respect staff and other players!'],
    ['title' => 'No RDM', 'body' => "Don't kill players without a reason (RDM)"],
    ['title' => 'No CDM', 'body' => "Don't kill players with cars (CDM)"],
    ['title' => 'No FailRP', 'body' => 'No FailRP'],
    ['title' => 'No Metagaming', 'body' => 'No Metagaming'],
    ['title' => 'No Powergaming', 'body' => 'No Powergaming'],
    ['title' => 'FearRP', 'body' => 'FearRP'],
    ['title' => 'Fear Guns', 'body' => 'Fear Guns'],
    ['title' => 'NLR', 'body' => 'NLR'],
    ['title' => 'NLR Time', 'body' => 'NLR Time - 5 Min.'],
];
$dual['content']['player'] = [
    'name' => 'Connecting…',
    'steamId' => 'STEAM_0:0:00000000',
    'lastSeen' => 'First join',
];
$dual['watermark'] = [
    'madeBy' => 'My Server',
    'copyright' => 'NorthStar Scripts',
];

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
    ['dual_panel', 'Dual Panel', 'Server info + numbered rules side by side (info_rules layout).', 'dual_panel', $dual, 4],
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
