<?php

declare(strict_types=1);

namespace Northstar;

final class Entitlement
{
    public const FEATURE_YOUTUBE_MUSIC = 'youtube_music';
    public const FEATURE_SLIDESHOW = 'slideshow_background';
    public const FEATURE_VIDEO_BG = 'video_background';
    public const FEATURE_STAFF = 'staff';
    public const FEATURE_ANNOUNCEMENTS = 'announcements';
    public const FEATURE_KEN_BURNS = 'ken_burns';
    public const FEATURE_MUSIC_FILE = 'music_file';

    /** @param array<string,mixed> $config */
    public static function planFor(int $userId, string $productKey, array $config): string
    {
        $stmt = Database::pdo()->prepare(
            'SELECT plan_key FROM entitlements
             WHERE user_id = ? AND product_key = ?
               AND (ends_at IS NULL OR ends_at > NOW(3))
             ORDER BY FIELD(plan_key, \'pro\', \'standard\', \'free\'), id DESC
             LIMIT 1'
        );
        $stmt->execute([$userId, $productKey]);
        $plan = $stmt->fetchColumn();
        return is_string($plan) && $plan !== '' ? $plan : 'free';
    }

    /**
     * @param array<string,mixed> $config
     * @return array<string,mixed>
     */
    public static function limits(int $userId, string $productKey, array $config): array
    {
        $plan = self::planFor($userId, $productKey, $config);
        $bucket = $config['entitlements'] ?? [];
        $limits = $bucket[$plan] ?? ($bucket['free'] ?? []);
        if (!is_array($limits)) {
            $limits = [];
        }
        $features = is_array($limits['features'] ?? null) ? $limits['features'] : [];

        return [
            'plan' => $plan,
            'max_projects' => (int) ($limits['max_projects'] ?? 0),
            'max_media' => (int) ($limits['max_media'] ?? 0),
            'max_builds_per_day' => (int) ($limits['max_builds_per_day'] ?? 0),
            'max_components' => (int) ($limits['max_components'] ?? 12),
            'features' => [
                self::FEATURE_YOUTUBE_MUSIC => (bool) ($features[self::FEATURE_YOUTUBE_MUSIC] ?? false),
                self::FEATURE_SLIDESHOW => (bool) ($features[self::FEATURE_SLIDESHOW] ?? false),
                self::FEATURE_VIDEO_BG => (bool) ($features[self::FEATURE_VIDEO_BG] ?? false),
                self::FEATURE_STAFF => (bool) ($features[self::FEATURE_STAFF] ?? false),
                self::FEATURE_ANNOUNCEMENTS => (bool) ($features[self::FEATURE_ANNOUNCEMENTS] ?? false),
                self::FEATURE_KEN_BURNS => (bool) ($features[self::FEATURE_KEN_BURNS] ?? false),
                self::FEATURE_MUSIC_FILE => (bool) ($features[self::FEATURE_MUSIC_FILE] ?? true),
            ],
        ];
    }

    /** @param array<string,mixed> $config */
    public static function can(int $userId, string $feature, array $config, string $productKey = 'load'): bool
    {
        $limits = self::limits($userId, $productKey, $config);
        return !empty($limits['features'][$feature]);
    }

    /** @param array<string,mixed> $config */
    public static function assertFeature(int $userId, string $feature, array $config, string $label = ''): void
    {
        if (self::can($userId, $feature, $config)) {
            return;
        }
        $plan = self::planFor($userId, 'load', $config);
        $name = $label !== '' ? $label : $feature;
        throw new \RuntimeException(
            ucfirst($name) . ' is not available on the ' . $plan . ' plan. Upgrade to unlock it.'
        );
    }

    /**
     * Enforce plan limits against a validated builder config.
     *
     * @param array<string,mixed> $doc
     * @param array<string,mixed> $config
     */
    public static function assertConfigAllowed(array $doc, int $userId, array $config): void
    {
        $limits = self::limits($userId, 'load', $config);
        $features = $limits['features'];
        $maxComponents = (int) $limits['max_components'];

        $components = $doc['components'] ?? [];
        if (count($components) > $maxComponents) {
            throw new \RuntimeException(
                'Component limit for your plan is ' . $maxComponents . ' (you have ' . count($components) . ').'
            );
        }

        foreach ($components as $comp) {
            $type = (string) ($comp['type'] ?? '');
            if ($type === 'staff' && empty($features[self::FEATURE_STAFF])) {
                self::assertFeature($userId, self::FEATURE_STAFF, $config, 'Staff blocks');
            }
            if ($type === 'announcements' && empty($features[self::FEATURE_ANNOUNCEMENTS])) {
                self::assertFeature($userId, self::FEATURE_ANNOUNCEMENTS, $config, 'Announcements');
            }
        }

        $bg = $doc['background'] ?? [];
        $bgType = (string) ($bg['type'] ?? 'color');
        if ($bgType === 'slideshow' && empty($features[self::FEATURE_SLIDESHOW])) {
            self::assertFeature($userId, self::FEATURE_SLIDESHOW, $config, 'Slideshow backgrounds');
        }
        if ($bgType === 'video' && empty($features[self::FEATURE_VIDEO_BG])) {
            self::assertFeature($userId, self::FEATURE_VIDEO_BG, $config, 'Video backgrounds');
        }
        if (!empty($bg['kenBurns']) && empty($features[self::FEATURE_KEN_BURNS])) {
            self::assertFeature($userId, self::FEATURE_KEN_BURNS, $config, 'Ken Burns effect');
        }

        $music = $doc['music'] ?? [];
        $source = (string) ($music['source'] ?? 'file');
        if (!empty($music['enabled'])) {
            if ($source === 'youtube') {
                self::assertFeature($userId, self::FEATURE_YOUTUBE_MUSIC, $config, 'YouTube music');
            } elseif ($source === 'file' || !empty($music['mediaId'])) {
                if (empty($features[self::FEATURE_MUSIC_FILE])) {
                    self::assertFeature($userId, self::FEATURE_MUSIC_FILE, $config, 'Uploaded music files');
                }
            }
        }
        if ($source === 'youtube' && !empty($music['youtubeId']) && empty($features[self::FEATURE_YOUTUBE_MUSIC])) {
            self::assertFeature($userId, self::FEATURE_YOUTUBE_MUSIC, $config, 'YouTube music');
        }
    }

    /** @param array<string,mixed> $config */
    public static function assertCanCreateProject(int $userId, array $config): void
    {
        $limits = self::limits($userId, 'load', $config);
        $stmt = Database::pdo()->prepare(
            "SELECT COUNT(*) FROM projects WHERE user_id = ? AND product_key = 'load' AND status != 'archived'"
        );
        $stmt->execute([$userId]);
        if ((int) $stmt->fetchColumn() >= $limits['max_projects']) {
            throw new \RuntimeException('Project limit reached for your plan (' . $limits['plan'] . ').');
        }
    }

    /** @param array<string,mixed> $config */
    public static function assertCanUploadMedia(int $userId, array $config): void
    {
        $limits = self::limits($userId, 'load', $config);
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM media WHERE user_id = ? AND deleted_at IS NULL'
        );
        $stmt->execute([$userId]);
        if ((int) $stmt->fetchColumn() >= $limits['max_media']) {
            throw new \RuntimeException('Media limit reached for your plan (' . $limits['plan'] . ').');
        }
    }

    /** @param array<string,mixed> $config */
    public static function assertCanBuild(int $userId, array $config): void
    {
        $limits = self::limits($userId, 'load', $config);
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM builds WHERE user_id = ? AND created_at >= DATE_SUB(NOW(3), INTERVAL 1 DAY)'
        );
        $stmt->execute([$userId]);
        if ((int) $stmt->fetchColumn() >= $limits['max_builds_per_day']) {
            throw new \RuntimeException('Daily build limit reached for your plan (' . $limits['plan'] . ').');
        }
    }

    /**
     * Change (or create) the user's active plan for a product.
     * Billing is not wired yet — this is an early-access plan switch.
     *
     * @param 'free'|'standard'|'pro' $plan
     */
    public static function setPlan(int $userId, string $plan, string $productKey = 'load', string $source = 'account'): string
    {
        $plan = strtolower(trim($plan));
        if (!in_array($plan, ['free', 'standard', 'pro'], true)) {
            throw new \InvalidArgumentException('Invalid plan.');
        }

        $pdo = Database::pdo();
        $find = $pdo->prepare(
            'SELECT id FROM entitlements
             WHERE user_id = ? AND product_key = ?
             ORDER BY id DESC LIMIT 1'
        );
        $find->execute([$userId, $productKey]);
        $row = $find->fetch();

        $meta = json_encode([
            'changed_at' => date('c'),
            'via' => $source,
        ], JSON_UNESCAPED_SLASHES);

        if ($row) {
            $upd = $pdo->prepare(
                'UPDATE entitlements
                 SET plan_key = ?, source = ?, meta_json = ?, ends_at = NULL
                 WHERE id = ?'
            );
            $upd->execute([$plan, $source, $meta, (int) $row['id']]);
        } else {
            $ins = $pdo->prepare(
                'INSERT INTO entitlements (user_id, product_key, plan_key, source, meta_json)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $ins->execute([$userId, $productKey, $plan, $source, $meta]);
        }

        return $plan;
    }

    /** @return list<array{key:string,label:string,blurb:string,highlights:list<string>}> */
    public static function catalog(): array
    {
        return [
            [
                'key' => 'free',
                'label' => 'Free',
                'blurb' => 'Ship a complete loading screen.',
                'highlights' => [
                    '5 projects · 60 media · 15 builds/day',
                    'YouTube + file music',
                    'Slideshow, staff, announcements',
                ],
            ],
            [
                'key' => 'standard',
                'label' => 'Standard',
                'blurb' => 'More room to grow.',
                'highlights' => [
                    '25 projects · 250 media · 50 builds/day',
                    'Everything in Free',
                    'Ken Burns motion',
                ],
            ],
            [
                'key' => 'pro',
                'label' => 'Pro',
                'blurb' => 'Studio capacity.',
                'highlights' => [
                    '200 projects · 2000 media · 200 builds/day',
                    'Everything in Standard',
                    'Video backgrounds',
                ],
            ],
        ];
    }
}
