<?php

declare(strict_types=1);

namespace Northstar;

final class BuilderConfigValidator
{
    private const COMPONENT_TYPES = [
        'logo', 'serverName', 'tagline', 'text', 'image', 'loadingBar', 'loadingStatus',
        'musicPlayer', 'rulesButton', 'discordButton', 'websiteButton', 'socialButton',
        'serverInfo', 'announcements', 'staff', 'clock', 'panel',
    ];

    /**
     * @return array<string,mixed>
     */
    public static function validate(array $config, int $userId, bool $strict = true): array
    {
        $version = (int) ($config['version'] ?? 0);
        if ($version !== 1) {
            throw new \InvalidArgumentException('Unsupported config version.');
        }

        $out = [
            'version' => 1,
            'meta' => self::meta($config['meta'] ?? []),
            'server' => self::server($config['server'] ?? []),
            'theme' => self::theme($config['theme'] ?? []),
            'background' => self::background($config['background'] ?? [], $userId, $strict),
            'music' => self::music($config['music'] ?? [], $userId, $strict),
            'loading' => self::loading($config['loading'] ?? []),
            'content' => self::content($config['content'] ?? []),
            'watermark' => self::watermark($config['watermark'] ?? [], $config['server'] ?? []),
            'components' => [],
            'layersOrder' => [],
        ];

        $components = $config['components'] ?? [];
        if (!is_array($components)) {
            throw new \InvalidArgumentException('components must be an array.');
        }
        if (count($components) > 80) {
            throw new \InvalidArgumentException('Too many components (max 80).');
        }

        $ids = [];
        foreach ($components as $comp) {
            if (!is_array($comp)) {
                throw new \InvalidArgumentException('Invalid component.');
            }
            $normalized = self::component($comp, $userId, $strict);
            if (isset($ids[$normalized['id']])) {
                throw new \InvalidArgumentException('Duplicate component id.');
            }
            $ids[$normalized['id']] = true;
            $out['components'][] = $normalized;
        }

        $order = $config['layersOrder'] ?? array_column($out['components'], 'id');
        if (!is_array($order)) {
            $order = array_column($out['components'], 'id');
        }
        $out['layersOrder'] = array_values(array_filter(
            array_map('strval', $order),
            static fn ($id) => isset($ids[$id])
        ));

        return $out;
    }

    /** @param array<string,mixed> $meta @return array<string,mixed> */
    private static function meta(array $meta): array
    {
        return [
            'canvas' => [
                'baseWidth' => 1920,
                'baseHeight' => 1080,
            ],
            'previewMode' => self::str($meta['previewMode'] ?? '1920x1080', 32),
        ];
    }

    /** @param array<string,mixed> $server @return array<string,mixed> */
    private static function server(array $server): array
    {
        $slotsRaw = $server['slots'] ?? null;
        $slots = null;
        if ($slotsRaw !== null && $slotsRaw !== '') {
            $slots = max(0, min(1024, (int) $slotsRaw));
        }

        return [
            'name' => self::str($server['name'] ?? 'My Server', 80),
            'tagline' => self::str($server['tagline'] ?? '', 160),
            'creator' => self::str($server['creator'] ?? '', 80),
            'map' => self::str($server['map'] ?? '', 80),
            'slots' => $slots,
            'mode' => self::str($server['mode'] ?? '', 64),
            'locale' => self::str($server['locale'] ?? 'en', 8),
        ];
    }

    /** @param array<string,mixed> $theme @return array<string,mixed> */
    private static function theme(array $theme): array
    {
        $allowed = [
            'cinematic', 'minimal', 'neon', 'dual_panel', 'info_rules', 'rulebook',
            'horizon', 'ember', 'arctic', 'noir', 'stadium', 'glass',
        ];
        $preset = self::str($theme['preset'] ?? 'cinematic', 64);
        if (!in_array($preset, $allowed, true)) {
            $preset = 'cinematic';
        }
        if ($preset === 'info_rules' || $preset === 'rulebook') {
            $preset = 'dual_panel';
        }
        $layout = self::str($theme['layout'] ?? '', 32);
        if ($layout === 'info_rules') {
            $layout = 'dual_panel';
        }
        if ($preset === 'dual_panel') {
            $layout = 'dual_panel';
        } elseif ($layout !== 'dual_panel') {
            $layout = 'freeform';
        }
        return [
            'preset' => $preset,
            'layout' => $layout,
            'accent' => self::color($theme['accent'] ?? '#C4A35A'),
            'fonts' => [
                'display' => self::str($theme['fonts']['display'] ?? 'Syne', 64),
                'body' => self::str($theme['fonts']['body'] ?? 'DM Sans', 64),
            ],
            'colors' => [
                'text' => self::color($theme['colors']['text'] ?? '#F5F5F5'),
                'muted' => self::color($theme['colors']['muted'] ?? '#A0A0A0'),
                'panel' => self::str($theme['colors']['panel'] ?? 'rgba(0,0,0,0.45)', 64),
            ],
        ];
    }

    /** @param array<string,mixed> $bg @return array<string,mixed> */
    private static function background(array $bg, int $userId, bool $strict): array
    {
        $type = self::str($bg['type'] ?? 'color', 32);
        if (!in_array($type, ['color', 'image', 'slideshow', 'video'], true)) {
            $type = 'color';
        }
        $mediaIds = [];
        foreach (($bg['mediaIds'] ?? []) as $id) {
            $mid = (int) $id;
            if ($mid > 0) {
                if ($strict) {
                    self::assertMediaOwned($mid, $userId, ['image', 'video']);
                }
                $mediaIds[] = $mid;
            }
        }
        return [
            'type' => $type,
            'color' => self::color($bg['color'] ?? '#0B0C10'),
            'mediaIds' => array_values(array_unique($mediaIds)),
            'fit' => in_array($bg['fit'] ?? 'cover', ['cover', 'contain'], true) ? $bg['fit'] : 'cover',
            'intervalMs' => max(2000, min(60000, (int) ($bg['intervalMs'] ?? 8000))),
            'kenBurns' => (bool) ($bg['kenBurns'] ?? false),
            'overlay' => [
                'enabled' => (bool) ($bg['overlay']['enabled'] ?? true),
                'color' => self::color($bg['overlay']['color'] ?? '#000000'),
                'opacity' => max(0, min(1, (float) ($bg['overlay']['opacity'] ?? 0.35))),
            ],
        ];
    }

    /** @param array<string,mixed> $music @return array<string,mixed> */
    private static function music(array $music, int $userId, bool $strict): array
    {
        $source = strtolower(trim((string) ($music['source'] ?? 'file')));
        if (!in_array($source, ['file', 'youtube'], true)) {
            $source = 'file';
        }

        $mediaId = isset($music['mediaId']) ? (int) $music['mediaId'] : null;
        $youtubeUrl = trim((string) ($music['youtubeUrl'] ?? ''));
        $youtubeId = trim((string) ($music['youtubeId'] ?? ''));

        if ($youtubeId === '' && $youtubeUrl !== '') {
            $youtubeId = self::extractYoutubeId($youtubeUrl) ?? '';
        }
        if ($youtubeUrl === '' && $youtubeId !== '') {
            $youtubeUrl = 'https://www.youtube.com/watch?v=' . $youtubeId;
        }

        if ($source === 'youtube') {
            if ($youtubeId === '' || !preg_match('/^[a-zA-Z0-9_-]{11}$/', $youtubeId)) {
                if ($strict || $youtubeUrl !== '' || !empty($music['enabled'])) {
                    throw new \InvalidArgumentException('Invalid YouTube URL or video id.');
                }
            }
            $mediaId = null;
        } else {
            $youtubeId = '';
            $youtubeUrl = '';
            if ($mediaId && $strict) {
                self::assertMediaOwned($mediaId, $userId, ['audio']);
            }
        }

        return [
            'enabled' => (bool) ($music['enabled'] ?? false),
            'source' => $source,
            'mediaId' => $mediaId ?: null,
            'youtubeUrl' => $youtubeUrl,
            'youtubeId' => $youtubeId !== '' ? $youtubeId : null,
            'volume' => max(0, min(1, (float) ($music['volume'] ?? 0.15))),
            'autoplay' => (bool) ($music['autoplay'] ?? true),
            'loop' => (bool) ($music['loop'] ?? true),
            'startMutedHint' => (bool) ($music['startMutedHint'] ?? true),
        ];
    }

    public static function extractYoutubeId(string $input): ?string
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
            return $input;
        }
        if (!preg_match('#^https?://#i', $input)) {
            return null;
        }
        $parts = parse_url($input);
        if ($parts === false) {
            return null;
        }
        $host = strtolower((string) ($parts['host'] ?? ''));
        $host = preg_replace('/^www\./', '', $host) ?? $host;
        $path = (string) ($parts['path'] ?? '');

        if ($host === 'youtu.be') {
            $id = ltrim($path, '/');
            $id = explode('/', $id)[0] ?? '';
            return preg_match('/^[a-zA-Z0-9_-]{11}$/', $id) ? $id : null;
        }

        if (!in_array($host, ['youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtube-nocookie.com'], true)) {
            return null;
        }

        if (isset($parts['query'])) {
            parse_str($parts['query'], $q);
            if (!empty($q['v']) && preg_match('/^[a-zA-Z0-9_-]{11}$/', (string) $q['v'])) {
                return (string) $q['v'];
            }
        }

        if (preg_match('#/(?:embed|shorts|live|v)/([a-zA-Z0-9_-]{11})#', $path, $m)) {
            return $m[1];
        }

        return null;
    }

    /** @param array<string,mixed> $loading @return array<string,mixed> */
    private static function loading(array $loading): array
    {
        return [
            'showBar' => (bool) ($loading['showBar'] ?? true),
            'showStatus' => (bool) ($loading['showStatus'] ?? true),
            'showPercentWhenKnown' => (bool) ($loading['showPercentWhenKnown'] ?? true),
            'indeterminateWhenUnknown' => (bool) ($loading['indeterminateWhenUnknown'] ?? true),
        ];
    }

    /** @param array<string,mixed> $content @return array<string,mixed> */
    private static function content(array $content): array
    {
        $rules = [];
        foreach (array_slice($content['rules'] ?? [], 0, 30) as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            $rules[] = [
                'title' => self::str($rule['title'] ?? '', 80),
                'body' => self::str($rule['body'] ?? '', 500),
            ];
        }
        $announcements = [];
        foreach (array_slice($content['announcements'] ?? [], 0, 20) as $a) {
            if (!is_array($a)) {
                continue;
            }
            $announcements[] = [
                'title' => self::str($a['title'] ?? '', 80),
                'body' => self::str($a['body'] ?? '', 500),
            ];
        }
        $staff = [];
        foreach (array_slice($content['staff'] ?? [], 0, 40) as $s) {
            if (!is_array($s)) {
                continue;
            }
            $staff[] = [
                'name' => self::str($s['name'] ?? '', 64),
                'role' => self::str($s['role'] ?? '', 64),
                'mediaId' => isset($s['mediaId']) ? (int) $s['mediaId'] : null,
            ];
        }
        $socials = [];
        foreach (array_slice($content['socials'] ?? [], 0, 12) as $soc) {
            if (!is_array($soc)) {
                continue;
            }
            $url = self::safeUrl((string) ($soc['url'] ?? ''));
            if ($url === null && ($soc['url'] ?? '') !== '') {
                continue;
            }
            $socials[] = [
                'type' => self::str($soc['type'] ?? 'website', 32),
                'label' => self::str($soc['label'] ?? 'Link', 48),
                'url' => $url ?? '',
            ];
        }
        $playerIn = is_array($content['player'] ?? null) ? $content['player'] : [];
        $player = [
            'name' => self::str($playerIn['name'] ?? '', 80),
            'steamId' => self::str($playerIn['steamId'] ?? '', 64),
            'lastSeen' => self::str($playerIn['lastSeen'] ?? '', 64),
        ];
        return compact('rules', 'announcements', 'staff', 'socials', 'player');
    }

    /**
     * @param array<string,mixed> $watermark
     * @param array<string,mixed> $server
     * @return array<string,mixed>
     */
    private static function watermark(array $watermark, array $server): array
    {
        $madeBy = self::str($watermark['madeBy'] ?? '', 80);
        if ($madeBy === '') {
            $madeBy = self::str($server['creator'] ?? '', 80);
        }
        if ($madeBy === '') {
            $madeBy = self::str($server['name'] ?? 'Server', 80);
        }
        return [
            'madeBy' => $madeBy,
            'copyright' => self::str($watermark['copyright'] ?? 'NorthStar Scripts', 80) ?: 'NorthStar Scripts',
        ];
    }

    /** @param array<string,mixed> $comp @return array<string,mixed> */
    private static function component(array $comp, int $userId, bool $strict): array
    {
        $type = (string) ($comp['type'] ?? '');
        if (!in_array($type, self::COMPONENT_TYPES, true)) {
            throw new \InvalidArgumentException('Unknown component type: ' . $type);
        }
        $id = self::str($comp['id'] ?? '', 64);
        if ($id === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
            throw new \InvalidArgumentException('Invalid component id.');
        }

        $props = is_array($comp['props'] ?? null) ? $comp['props'] : [];
        if (isset($props['mediaId']) && $props['mediaId'] && $strict) {
            self::assertMediaOwned((int) $props['mediaId'], $userId, ['image']);
        }
        if (isset($props['url']) && is_string($props['url']) && $props['url'] !== '') {
            $safe = self::safeUrl($props['url']);
            if ($safe === null) {
                throw new \InvalidArgumentException('Unsafe URL in component.');
            }
            $props['url'] = $safe;
        }

        return [
            'id' => $id,
            'type' => $type,
            'name' => self::str($comp['name'] ?? $type, 64),
            'visible' => (bool) ($comp['visible'] ?? true),
            'locked' => (bool) ($comp['locked'] ?? false),
            'zIndex' => max(0, min(10000, (int) ($comp['zIndex'] ?? 1))),
            'x' => (float) ($comp['x'] ?? 0),
            'y' => (float) ($comp['y'] ?? 0),
            'w' => max(1, (float) ($comp['w'] ?? 100)),
            'h' => max(1, (float) ($comp['h'] ?? 40)),
            'props' => $props,
        ];
    }

    /** @param list<string> $kinds */
    private static function assertMediaOwned(int $mediaId, int $userId, array $kinds): void
    {
        $placeholders = implode(',', array_fill(0, count($kinds), '?'));
        $params = array_merge([$mediaId, $userId], $kinds);
        $stmt = Database::pdo()->prepare(
            "SELECT id FROM media WHERE id = ? AND user_id = ? AND deleted_at IS NULL AND kind IN ($placeholders) LIMIT 1"
        );
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            throw new \InvalidArgumentException('Referenced media is invalid or not owned.');
        }
    }

    private static function str(mixed $v, int $max): string
    {
        $s = trim((string) $v);
        if (mb_strlen($s) > $max) {
            $s = mb_substr($s, 0, $max);
        }
        return $s;
    }

    private static function color(mixed $v): string
    {
        $s = trim((string) $v);
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $s)) {
            return strtoupper($s);
        }
        if (preg_match('/^rgba?\([\d\s.,%]+\)$/', $s)) {
            return $s;
        }
        return '#FFFFFF';
    }

    private static function safeUrl(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (!preg_match('#^https://#i', $url) && !preg_match('#^https://discord\.gg/#i', $url)) {
            // Allow https only
            if (!preg_match('#^https://#i', $url)) {
                return null;
            }
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        $parts = parse_url($url);
        if (($parts['scheme'] ?? '') !== 'https') {
            return null;
        }
        return $url;
    }

    /** @return array<string,mixed> */
    public static function defaultConfig(string $serverName = 'My Server', string $theme = 'cinematic'): array
    {
        return [
            'version' => 1,
            'meta' => [
                'canvas' => ['baseWidth' => 1920, 'baseHeight' => 1080],
                'previewMode' => '1920x1080',
            ],
            'server' => [
                'name' => $serverName,
                'tagline' => 'Your Story Starts Here',
                'creator' => $serverName,
                'map' => '',
                'slots' => null,
                'mode' => '',
                'locale' => 'en',
            ],
            'theme' => [
                'preset' => $theme,
                'layout' => $theme === 'dual_panel' || $theme === 'info_rules' || $theme === 'rulebook' ? 'dual_panel' : 'freeform',
                'accent' => '#C4A35A',
                'fonts' => ['display' => 'Syne', 'body' => 'DM Sans'],
                'colors' => [
                    'text' => '#F5F5F5',
                    'muted' => '#A8A8A8',
                    'panel' => 'rgba(8,10,14,0.55)',
                ],
            ],
            'background' => [
                'type' => 'color',
                'color' => '#0B0C10',
                'mediaIds' => [],
                'fit' => 'cover',
                'intervalMs' => 8000,
                'kenBurns' => false,
                'overlay' => ['enabled' => true, 'color' => '#000000', 'opacity' => 0.35],
            ],
            'music' => [
                'enabled' => false,
                'source' => 'file',
                'mediaId' => null,
                'youtubeUrl' => '',
                'youtubeId' => null,
                'volume' => 0.15,
                'autoplay' => true,
                'loop' => true,
                'startMutedHint' => true,
            ],
            'loading' => [
                'showBar' => true,
                'showStatus' => true,
                'showPercentWhenKnown' => true,
                'indeterminateWhenUnknown' => true,
            ],
            'content' => [
                'rules' => [
                    ['title' => 'Respect', 'body' => 'Treat every player with respect.'],
                    ['title' => 'No RDM / VDM', 'body' => 'Follow server combat and vehicle rules.'],
                ],
                'announcements' => [
                    ['title' => 'Welcome', 'body' => 'Thanks for joining — read the rules and enjoy.'],
                ],
                'staff' => [],
                'socials' => [
                    ['type' => 'discord', 'label' => 'Discord', 'url' => 'https://discord.gg/example'],
                    ['type' => 'website', 'label' => 'Website', 'url' => 'https://example.com'],
                ],
                'player' => [
                    'name' => '',
                    'steamId' => '',
                    'lastSeen' => '',
                ],
            ],
            'watermark' => [
                'madeBy' => $serverName,
                'copyright' => 'NorthStar Scripts',
            ],
            'components' => [
                [
                    'id' => 'c_name_1',
                    'type' => 'serverName',
                    'name' => 'Server Name',
                    'visible' => true,
                    'locked' => false,
                    'zIndex' => 20,
                    'x' => 360, 'y' => 340, 'w' => 1200, 'h' => 90,
                    'props' => [
                        'fontFamily' => 'Orbitron',
                        'fontSize' => 64,
                        'fontWeight' => 700,
                        'align' => 'center',
                        'color' => '#FFFFFF',
                        'letterSpacing' => 2,
                        'shadow' => true,
                    ],
                ],
                [
                    'id' => 'c_tag_1',
                    'type' => 'tagline',
                    'name' => 'Tagline',
                    'visible' => true,
                    'locked' => false,
                    'zIndex' => 21,
                    'x' => 460, 'y' => 440, 'w' => 1000, 'h' => 48,
                    'props' => [
                        'fontFamily' => 'Source Sans 3',
                        'fontSize' => 24,
                        'fontWeight' => 400,
                        'align' => 'center',
                        'color' => '#C4A35A',
                    ],
                ],
                [
                    'id' => 'c_bar_1',
                    'type' => 'loadingBar',
                    'name' => 'Loading Bar',
                    'visible' => true,
                    'locked' => false,
                    'zIndex' => 30,
                    'x' => 560, 'y' => 880, 'w' => 800, 'h' => 12,
                    'props' => ['height' => 12, 'radius' => 2],
                ],
                [
                    'id' => 'c_status_1',
                    'type' => 'loadingStatus',
                    'name' => 'Loading Status',
                    'visible' => true,
                    'locked' => false,
                    'zIndex' => 31,
                    'x' => 560, 'y' => 900, 'w' => 800, 'h' => 32,
                    'props' => [
                        'fontFamily' => 'Source Sans 3',
                        'fontSize' => 14,
                        'align' => 'center',
                        'color' => '#A8A8A8',
                    ],
                ],
            ],
            'layersOrder' => ['c_name_1', 'c_tag_1', 'c_bar_1', 'c_status_1'],
        ];
    }
}
