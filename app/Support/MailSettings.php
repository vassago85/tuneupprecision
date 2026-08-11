<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Resolves the outgoing-mail configuration the app should use.
 *
 * Values saved on the admin Email settings page (settings table, mail.* keys)
 * win over the .env-based defaults in config/tuneup.php. This mirrors
 * App\Support\Eft, and is applied over Laravel's runtime config at boot so a
 * deploy is not required to switch mailer or drop in Mailgun credentials.
 */
final class MailSettings
{
    /**
     * The Mailgun API key is stored encrypted at rest.
     */
    private const string SECRET_KEY = 'mailgun_secret';

    /**
     * Effective settings: saved values (secret decrypted), else env defaults.
     *
     * @return array<string, string|null>
     */
    public static function details(): array
    {
        return [
            'mailer' => Setting::get('mail.mailer', config('tuneup.mail.mailer')),
            'from_address' => Setting::get('mail.from_address', config('tuneup.mail.from_address')),
            'from_name' => Setting::get('mail.from_name', config('tuneup.mail.from_name')),
            'mailgun_domain' => Setting::get('mail.mailgun_domain', config('tuneup.mail.mailgun_domain')),
            'mailgun_secret' => self::secret(),
            'mailgun_endpoint' => Setting::get('mail.mailgun_endpoint', config('tuneup.mail.mailgun_endpoint')),
        ];
    }

    /**
     * The editable keys, in display order.
     *
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return ['mailer', 'from_address', 'from_name', 'mailgun_domain', 'mailgun_secret', 'mailgun_endpoint'];
    }

    /**
     * Persist a set of form values, encrypting the secret.
     *
     * @param  array<string, mixed>  $data
     */
    public static function save(array $data): void
    {
        foreach (self::keys() as $key) {
            $value = $data[$key] ?? null;

            if ($key === self::SECRET_KEY) {
                // Blank submission = keep the existing secret rather than wiping it.
                if (blank($value)) {
                    continue;
                }

                $value = Crypt::encryptString((string) $value);
            }

            Setting::put("mail.{$key}", $value !== null ? (string) $value : null);
        }
    }

    /**
     * Push the effective settings onto Laravel's runtime config. Safe to call
     * on every request/worker boot; a no-op if the settings table is absent
     * (e.g. before the first migration).
     */
    public static function apply(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (Throwable) {
            // No usable DB connection yet (install/build step) — skip silently.
            return;
        }

        $settings = self::details();

        config([
            'mail.default' => $settings['mailer'] ?: config('mail.default'),
            'mail.from.address' => $settings['from_address'] ?: config('mail.from.address'),
            'mail.from.name' => $settings['from_name'] ?: config('mail.from.name'),
            'services.mailgun.domain' => $settings['mailgun_domain'] ?: config('services.mailgun.domain'),
            'services.mailgun.secret' => $settings['mailgun_secret'] ?: config('services.mailgun.secret'),
            'services.mailgun.endpoint' => $settings['mailgun_endpoint'] ?: config('services.mailgun.endpoint'),
        ]);
    }

    /**
     * Decrypt the stored Mailgun secret, falling back to the env default.
     */
    private static function secret(): ?string
    {
        $stored = Setting::get('mail.mailgun_secret');

        if (blank($stored)) {
            return config('tuneup.mail.mailgun_secret');
        }

        try {
            return Crypt::decryptString($stored);
        } catch (DecryptException) {
            // Legacy/plaintext value — return as-is so it still works.
            return $stored;
        }
    }
}
