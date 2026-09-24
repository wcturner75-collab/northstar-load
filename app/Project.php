<?php

declare(strict_types=1);

namespace Northstar;

final class Project
{
    /** @return list<array<string,mixed>> */
    public static function listForUser(int $userId): array
    {
        $stmt = Database::pdo()->prepare(
            "SELECT id, name, resource_name, theme_key, status, last_saved_at, created_at, updated_at
             FROM projects
             WHERE user_id = ? AND product_key = 'load' AND status != 'archived'
             ORDER BY updated_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public static function findOwned(int $projectId, int $userId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM projects WHERE id = ? AND user_id = ? LIMIT 1'
        );
        $stmt->execute([$projectId, $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        if (is_string($row['config_json'])) {
            $row['config'] = json_decode($row['config_json'], true) ?: [];
        } else {
            $row['config'] = $row['config_json'];
        }
        return $row;
    }

    /** @param array<string,mixed> $configApp */
    public static function create(
        int $userId,
        string $name,
        string $resourceName,
        ?int $templateId,
        array $configApp
    ): array {
        Entitlement::assertCanCreateProject($userId, $configApp);
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 128) {
            throw new \InvalidArgumentException('Invalid project name.');
        }
        $resourceName = ResourceName::assert($resourceName);

        $pdo = Database::pdo();
        $dup = $pdo->prepare('SELECT id FROM projects WHERE user_id = ? AND resource_name = ? LIMIT 1');
        $dup->execute([$userId, $resourceName]);
        if ($dup->fetch()) {
            throw new \InvalidArgumentException('Resource name already used on another project.');
        }

        $theme = 'cinematic';
        $config = BuilderConfigValidator::defaultConfig($name, $theme);
        if ($templateId) {
            $tpl = Template::find($templateId);
            if ($tpl) {
                $decoded = is_string($tpl['config_json'])
                    ? json_decode($tpl['config_json'], true)
                    : $tpl['config_json'];
                if (is_array($decoded)) {
                    $decoded['server']['name'] = $name;
                    $config = BuilderConfigValidator::validate($decoded, $userId, false);
                }
                $theme = $tpl['theme_key'];
            }
        }

        $runtime = (string) ($configApp['builds']['runtime_version'] ?? '1.0.0');
        $stmt = $pdo->prepare(
            'INSERT INTO projects
             (user_id, product_key, name, resource_name, template_id, theme_key, config_json, runtime_version, status, last_saved_at)
             VALUES (?, \'load\', ?, ?, ?, ?, ?, ?, \'draft\', NOW(3))'
        );
        $stmt->execute([
            $userId,
            $name,
            $resourceName,
            $templateId,
            $theme,
            json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $runtime,
        ]);

        $id = (int) $pdo->lastInsertId();
        $project = self::findOwned($id, $userId);
        if (!$project) {
            throw new \RuntimeException('Project create failed.');
        }
        return $project;
    }

    /** @param array<string,mixed> $rawConfig @param array<string,mixed>|null $configApp */
    public static function saveConfig(int $projectId, int $userId, array $rawConfig, bool $strict = false, ?array $configApp = null): array
    {
        $project = self::findOwned($projectId, $userId);
        if (!$project) {
            throw new \RuntimeException('Project not found.');
        }

        $app = $configApp ?? (is_array($GLOBALS['ns_config'] ?? null) ? $GLOBALS['ns_config'] : []);
        $validated = BuilderConfigValidator::validate($rawConfig, $userId, $strict);
        Entitlement::assertConfigAllowed($validated, $userId, $app);

        $stmt = Database::pdo()->prepare(
            'UPDATE projects
             SET config_json = ?, config_version = config_version + 1, last_saved_at = NOW(3), status = \'ready\',
                 name = ?, theme_key = ?
             WHERE id = ? AND user_id = ?'
        );
        $name = $validated['server']['name'] ?? $project['name'];
        $theme = $validated['theme']['preset'] ?? $project['theme_key'];
        $stmt->execute([
            json_encode($validated, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $name,
            $theme,
            $projectId,
            $userId,
        ]);

        self::snapshot($projectId, $userId, $validated, 'autosave');

        return self::findOwned($projectId, $userId) ?? $project;
    }

    /** @param array<string,mixed> $config */
    private static function snapshot(int $projectId, int $userId, array $config, string $note): void
    {
        $pdo = Database::pdo();
        $v = $pdo->prepare('SELECT COALESCE(MAX(version_no), 0) + 1 FROM project_versions WHERE project_id = ?');
        $v->execute([$projectId]);
        $versionNo = (int) $v->fetchColumn();

        // Keep last 20 versions
        if ($versionNo > 20) {
            $pdo->prepare(
                'DELETE FROM project_versions WHERE project_id = ? AND version_no <= ?'
            )->execute([$projectId, $versionNo - 20]);
        }

        $ins = $pdo->prepare(
            'INSERT INTO project_versions (project_id, user_id, version_no, config_json, note)
             VALUES (?, ?, ?, ?, ?)'
        );
        $ins->execute([
            $projectId,
            $userId,
            $versionNo,
            json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $note,
        ]);
    }

    public static function archive(int $projectId, int $userId): void
    {
        $stmt = Database::pdo()->prepare(
            "UPDATE projects SET status = 'archived' WHERE id = ? AND user_id = ?"
        );
        $stmt->execute([$projectId, $userId]);
        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('Project not found.');
        }
    }

    public static function rename(int $projectId, int $userId, string $name, ?string $resourceName = null): array
    {
        $project = self::findOwned($projectId, $userId);
        if (!$project) {
            throw new \RuntimeException('Project not found.');
        }
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 128) {
            throw new \InvalidArgumentException('Invalid name.');
        }
        $rn = $project['resource_name'];
        if ($resourceName !== null) {
            $rn = ResourceName::assert($resourceName);
            $dup = Database::pdo()->prepare(
                'SELECT id FROM projects WHERE user_id = ? AND resource_name = ? AND id != ? LIMIT 1'
            );
            $dup->execute([$userId, $rn, $projectId]);
            if ($dup->fetch()) {
                throw new \InvalidArgumentException('Resource name already in use.');
            }
        }
        $stmt = Database::pdo()->prepare(
            'UPDATE projects SET name = ?, resource_name = ? WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$name, $rn, $projectId, $userId]);
        return self::findOwned($projectId, $userId) ?? $project;
    }
}
