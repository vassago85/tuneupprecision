<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Turns a pasted YouTube link or a bare video ID into the 11-character ID
 * the embed player needs.
 */
final class YoutubeId
{
    public static function extract(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $value) === 1) {
            return $value;
        }

        if (! str_contains($value, '://') && preg_match('/^(?:www\.|m\.)?(?:youtube(?:-nocookie)?\.com|youtu\.be)\//i', $value) === 1) {
            $value = 'https://'.$value;
        }

        $parts = parse_url($value);
        if (! is_array($parts)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $host = preg_replace('/^(?:www\.|m\.)/', '', $host) ?? $host;

        if ($host === 'youtu.be') {
            return self::idOrNull(trim((string) ($parts['path'] ?? ''), '/'));
        }

        if (! in_array($host, ['youtube.com', 'youtube-nocookie.com', 'music.youtube.com'], true)) {
            return null;
        }

        $path = (string) ($parts['path'] ?? '');
        if (preg_match('~^/(?:embed|shorts|live)/([A-Za-z0-9_-]{11})~', $path, $matches) === 1) {
            return $matches[1];
        }

        parse_str((string) ($parts['query'] ?? ''), $query);
        $id = $query['v'] ?? null;

        return is_string($id) ? self::idOrNull($id) : null;
    }

    private static function idOrNull(string $id): ?string
    {
        return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) === 1 ? $id : null;
    }
}
