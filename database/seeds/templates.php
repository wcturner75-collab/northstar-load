<?php

declare(strict_types=1);

/**
 * Seed starter templates.
 * Usage: php database/seeds/templates.php
 */

$config = require dirname(__DIR__, 2) . '/app/bootstrap.php';

$cinematic = \Northstar\BuilderConfigValidator::defaultConfig('My Server', 'cinematic');
$cinematic['theme']['fonts'] = ['display' => 'Syne', 'body' => 'DM Sans'];
$cinematic['theme']['accent'] = '#C4A35A';

$minimal = \Northstar\BuilderConfigValidator::defaultConfig('My Server', 'minimal');
$minimal['theme']['accent'] = '#ECEAE4';
$minimal['theme']['fonts'] = ['display' => 'DM Sans', 'body' => 'DM Sans'];
$minimal['background']['overlay']['opacity'] = 0.12;
$minimal['components'] = array_values(array_filter(
    $minimal['components'],
    static fn ($c) => in_array($c['type'], ['serverName', 'loadingBar', 'loadingStatus'], true)
));
$minimal['layersOrder'] = array_column($minimal['components'], 'id');

$neon = \Northstar\BuilderConfigValidator::defaultConfig('Night City RP', 'neon');
$neon['theme']['accent'] = '#2EE6A6';
$neon['theme']['fonts'] = ['display' => 'Orbitron', 'body' => 'DM Sans'];
$neon['background']['color'] = '#04070D';

$dual = \Northstar\BuilderConfigValidator::defaultConfig('My Server', 'dual_panel');
$dual['theme']['preset'] = 'dual_panel';
$dual['theme']['layout'] = 'dual_panel';
$dual['theme']['accent'] = '#E11D48';
$dual['theme']['fonts'] = ['display' => 'Syne', 'body' => 'DM Sans'];
$dual['theme']['colors'] = [
    'text' => '#FFFFFF',
    'muted' => 'rgba(255,255,255,0.78)',
    'panel' => 'rgba(24,6,10,0.62)',
];
$dual['background']['color'] = '#7F1D1D';
$dual['background']['overlay']['opacity'] = 0.2;
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

$glass = \Northstar\BuilderConfigValidator::defaultConfig('My Server', 'glass');
$glass['theme']['accent'] = '#8FD3C8';
$glass['theme']['fonts'] = ['display' => 'Syne', 'body' => 'DM Sans'];
$glass['background']['color'] = '#0B1420';
$glass['background']['overlay']['opacity'] = 0.45;

$pdo = \Northstar\Database::pdo();
$pdo->exec('DELETE FROM templates');

$ins = $pdo->prepare(
    'INSERT INTO templates (product_key, slug, name, description, theme_key, config_json, sort_order)
     VALUES (\'load\', ?, ?, ?, ?, ?, ?)'
);

$rows = [
    ['cinematic', 'Spotlight', 'Centered marque under a soft stage glow.', 'cinematic', $cinematic, 1],
    ['minimal', 'Quiet Line', 'Name + hairline progress. Almost nothing else.', 'minimal', $minimal, 2],
    ['neon', 'Afterhours', 'Left rail brand with nightlife signal glow.', 'neon', $neon, 3],
    ['dual_panel', 'Rulebook', 'Server info + numbered rules on twin boards.', 'dual_panel', $dual, 4],
    ['glass', 'Glass Card', 'Frosted center card floating over the scene.', 'glass', $glass, 5],
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
