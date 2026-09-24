<?php

declare(strict_types=1);

namespace Northstar;

final class MediaManager
{
    private const MAGIC = [
        'png' => ["\x89PNG\r\n\x1a\n"],
        'jpg' => ["\xFF\xD8\xFF"],
        'jpeg' => ["\xFF\xD8\xFF"],
        'webp' => ['RIFF'], // further check WEBP
        'mp3' => ["\xFF\xFB", "\xFF\xF3", "\xFF\xF2", 'ID3'],
        'ogg' => ['OggS'],
        'mp4' => [], // ftyp checked separately
    ];

    private const MIME = [
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'mp3' => 'audio/mpeg',
        'ogg' => 'audio/ogg',
        'mp4' => 'video/mp4',
    ];

    /** @param array<string,mixed> $config @return array<string,mixed> */
    public static function upload(int $userId, array $file, array $config): array
    {
        Entitlement::assertCanUploadMedia($userId, $config);

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException('Upload failed.');
        }
        $tmp = $file['tmp_name'] ?? '';
        if (!is_uploaded_file($tmp)) {
            throw new \InvalidArgumentException('Invalid upload.');
        }

        $original = (string) ($file['name'] ?? 'file');
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $allowed = $config['media']['allowed_extensions'] ?? [];
        if (!in_array($ext, $allowed, true)) {
            throw new \InvalidArgumentException('File type not allowed.');
        }

        // Block dangerous extensions even if somehow present
        $blocked = ['php', 'phtml', 'phar', 'exe', 'bat', 'cmd', 'js', 'html', 'htm', 'svg', 'shtml'];
        if (in_array($ext, $blocked, true)) {
            throw new \InvalidArgumentException('File type not allowed.');
        }

        $size = (int) ($file['size'] ?? 0);
        $kind = self::kindForExt($ext);
        $max = match ($kind) {
            'image' => (int) $config['media']['max_image_bytes'],
            'audio' => (int) $config['media']['max_audio_bytes'],
            'video' => (int) $config['media']['max_video_bytes'],
            default => 0,
        };
        if ($size <= 0 || $size > $max) {
            throw new \InvalidArgumentException('File size not allowed.');
        }

        $head = file_get_contents($tmp, false, null, 0, 64) ?: '';
        if (!self::matchesMagic($ext, $head, $tmp)) {
            throw new \InvalidArgumentException('File signature mismatch.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp) ?: '';
        $expected = self::MIME[$ext] ?? '';
        // Allow close variants
        $mimeOk = $mime === $expected
            || ($ext === 'mp3' && in_array($mime, ['audio/mpeg', 'audio/mp3'], true))
            || ($ext === 'mp4' && in_array($mime, ['video/mp4', 'application/mp4'], true));
        if (!$mimeOk) {
            throw new \InvalidArgumentException('MIME type mismatch.');
        }

        $width = null;
        $height = null;
        if ($kind === 'image') {
            $info = @getimagesize($tmp);
            if ($info === false) {
                throw new \InvalidArgumentException('Invalid image.');
            }
            $width = (int) $info[0];
            $height = (int) $info[1];
            $maxDim = (int) $config['media']['max_image_dimension'];
            if ($width > $maxDim || $height > $maxDim) {
                throw new \InvalidArgumentException('Image dimensions too large.');
            }
        }

        $storageName = bin2hex(random_bytes(16)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $relative = 'u/' . $userId . '/' . $storageName;
        $destDir = Path::join($config['paths']['uploads'], 'u', (string) $userId);
        if (!is_dir($destDir) && !mkdir($destDir, 0750, true) && !is_dir($destDir)) {
            throw new \RuntimeException('Could not create upload directory.');
        }
        $dest = Path::join($destDir, $storageName);
        Path::assertInside($config['paths']['uploads'], $dest);

        if (!move_uploaded_file($tmp, $dest)) {
            throw new \RuntimeException('Could not store upload.');
        }
        @chmod($dest, 0640);

        $sha = hash_file('sha256', $dest) ?: '';
        $stmt = Database::pdo()->prepare(
            'INSERT INTO media
             (user_id, original_name, storage_name, relative_path, mime_type, kind, size_bytes, width, height, sha256)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $safeOriginal = mb_substr(preg_replace('/[\x00-\x1F\x7F]/', '', $original) ?? 'file', 0, 255);
        $stmt->execute([
            $userId,
            $safeOriginal,
            $storageName,
            $relative,
            $expected,
            $kind,
            $size,
            $width,
            $height,
            $sha,
        ]);

        $id = (int) Database::pdo()->lastInsertId();
        return self::findOwned($id, $userId) ?? ['id' => $id];
    }

    /** @return list<array<string,mixed>> */
    public static function listForUser(int $userId, ?string $kind = null): array
    {
        if ($kind) {
            $stmt = Database::pdo()->prepare(
                'SELECT id, original_name, storage_name, mime_type, kind, size_bytes, width, height, created_at
                 FROM media WHERE user_id = ? AND deleted_at IS NULL AND kind = ?
                 ORDER BY created_at DESC'
            );
            $stmt->execute([$userId, $kind]);
        } else {
            $stmt = Database::pdo()->prepare(
                'SELECT id, original_name, storage_name, mime_type, kind, size_bytes, width, height, created_at
                 FROM media WHERE user_id = ? AND deleted_at IS NULL
                 ORDER BY created_at DESC'
            );
            $stmt->execute([$userId]);
        }
        return $stmt->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public static function findOwned(int $id, int $userId): ?array
    {
        $stmt = Database::pdo()->prepare(
            'SELECT * FROM media WHERE id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1'
        );
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function absolutePath(array $media, array $config): string
    {
        $path = Path::join($config['paths']['uploads'], $media['relative_path']);
        return Path::assertInside($config['paths']['uploads'], $path);
    }

    public static function softDelete(int $id, int $userId): void
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE media SET deleted_at = NOW(3) WHERE id = ? AND user_id = ? AND deleted_at IS NULL'
        );
        $stmt->execute([$id, $userId]);
        if ($stmt->rowCount() === 0) {
            throw new \RuntimeException('Media not found.');
        }
    }

    private static function kindForExt(string $ext): string
    {
        return match ($ext) {
            'png', 'jpg', 'jpeg', 'webp' => 'image',
            'mp3', 'ogg' => 'audio',
            'mp4' => 'video',
            default => throw new \InvalidArgumentException('Unknown kind.'),
        };
    }

    private static function matchesMagic(string $ext, string $head, string $tmp): bool
    {
        if ($ext === 'webp') {
            return str_starts_with($head, 'RIFF') && str_contains(substr($head, 0, 16), 'WEBP');
        }
        if ($ext === 'mp4') {
            $buf = file_get_contents($tmp, false, null, 0, 12) ?: '';
            return strlen($buf) >= 12 && substr($buf, 4, 4) === 'ftyp';
        }
        foreach (self::MAGIC[$ext] ?? [] as $sig) {
            if ($sig !== '' && str_starts_with($head, $sig)) {
                return true;
            }
        }
        return false;
    }
}
