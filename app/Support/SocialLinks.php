<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;

/**
 * Footer social profile URLs. Saved from the admin Settings page.
 * A blank field hides that icon. Only http(s) URLs are rendered.
 */
final class SocialLinks
{
    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return ['instagram', 'facebook', 'youtube', 'whatsapp'];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'youtube' => 'YouTube',
            'whatsapp' => 'WhatsApp',
        ];
    }

    /**
     * Raw saved values for the settings form. Null when the field is blank.
     *
     * @return array<string, string|null>
     */
    public static function details(): array
    {
        $details = [];

        foreach (self::keys() as $key) {
            $value = trim((string) Setting::get("social.{$key}"));
            $details[$key] = $value === '' ? null : $value;
        }

        return $details;
    }

    /**
     * Links that should appear in the footer.
     *
     * @return list<array{key: string, label: string, url: string}>
     */
    public static function visible(): array
    {
        $links = [];

        foreach (self::labels() as $key => $label) {
            $url = self::details()[$key] ?? null;

            if (! self::isHttpUrl($url)) {
                continue;
            }

            $links[] = [
                'key' => $key,
                'label' => $label,
                'url' => $url,
            ];
        }

        return $links;
    }

    private static function isHttpUrl(?string $url): bool
    {
        if ($url === null || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return in_array($scheme, ['http', 'https'], true);
    }
}
