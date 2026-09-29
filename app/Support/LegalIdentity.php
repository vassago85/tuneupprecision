<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;

/**
 * Merged statutory identity for legal pages, disclosure, and legal:check.
 *
 * Config (`config/legal.php`) is the fallback. Values saved on Legal &
 * Compliance (`legal.*` settings) win, then the older Settings-page keys for
 * telephone, email, VAT and dealer licence. Placeholder patterns are treated
 * as empty so they never render.
 */
final class LegalIdentity
{
    /**
     * Keys that must be real before a production deploy.
     *
     * @return list<string>
     */
    public static function requiredKeys(): array
    {
        return [
            'legal_name',
            'trading_as',
            'legal_status',
            'registration_no',
            'vat_no',
            'dealer_licence_no',
            'office_bearers',
            'physical_address',
            'postal_address',
            'legal_email',
            'legal_phone',
        ];
    }

    public static function isPlaceholder(?string $value): bool
    {
        if ($value === null) {
            return true;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return true;
        }

        if (str_contains($trimmed, '0000')) {
            return true;
        }

        if (in_array($trimmed, self::requiredKeys(), true)) {
            return true;
        }

        return (bool) preg_match('/^[0\s+\-]*$/', $trimmed);
    }

    public static function filled(?string $value): ?string
    {
        if (self::isPlaceholder($value)) {
            return null;
        }

        return trim((string) $value);
    }

    /**
     * Settings-page field => legal identity key. Both stores are kept in sync.
     *
     * @return array<string, string>
     */
    public static function legacyBusinessKeys(): array
    {
        return [
            'tel' => 'legal_phone',
            'email' => 'legal_email',
            'vat_number' => 'vat_no',
            'dealer_number' => 'dealer_licence_no',
        ];
    }

    /**
     * Raw values for the Legal & Compliance form, including placeholders, so
     * an admin can see and replace whatever is currently stored.
     *
     * @return array<string, string>
     */
    public static function editable(): array
    {
        $legacyByLegalKey = array_flip(self::legacyBusinessKeys());
        $values = [];

        foreach (self::requiredKeys() as $key) {
            $saved = Setting::get('legal.'.$key);
            if (is_string($saved) && trim($saved) !== '') {
                $values[$key] = $saved;

                continue;
            }

            $legacyKey = $legacyByLegalKey[$key] ?? null;
            if (is_string($legacyKey)) {
                $legacy = Setting::get('business.'.$legacyKey);
                if (is_string($legacy) && trim($legacy) !== '') {
                    $values[$key] = $legacy;

                    continue;
                }
            }

            $config = config('legal.'.$key);
            $values[$key] = is_string($config) ? $config : '';
        }

        return $values;
    }

    /**
     * Effective public identity: saved settings win, then .env.
     *
     * @return array<string, mixed>
     */
    public static function effective(): array
    {
        return [
            'legal_name' => self::value('legal_name'),
            'trading_as' => self::value('trading_as'),
            'legal_status' => self::value('legal_status'),
            'registration_no' => self::value('registration_no'),
            'vat_no' => self::value('vat_no'),
            'dealer_licence_no' => self::value('dealer_licence_no'),
            'office_bearers' => self::value('office_bearers'),
            'physical_address' => self::value('physical_address'),
            'postal_address' => self::value('postal_address'),
            'legal_email' => self::value('legal_email'),
            'legal_phone' => self::value('legal_phone'),
            'jurisdiction' => config('legal.jurisdiction'),
            'forum' => config('legal.forum'),
            'vat_rate' => config('legal.vat_rate'),
            'updated' => config('legal.updated'),
            'booking' => config('legal.booking'),
            'shipping' => config('legal.shipping'),
        ];
    }

    /**
     * @return list<string>
     */
    public static function failures(): array
    {
        $effective = self::effective();
        $failures = [];

        foreach (self::requiredKeys() as $key) {
            $value = $effective[$key] ?? null;
            $string = is_scalar($value) ? (string) $value : null;

            if (self::isPlaceholder($string)) {
                $failures[] = $key;
            }
        }

        return $failures;
    }

    private static function value(string $key): ?string
    {
        $saved = self::filled(Setting::get('legal.'.$key));
        if ($saved !== null) {
            return $saved;
        }

        $legacyKey = array_flip(self::legacyBusinessKeys())[$key] ?? null;
        if (is_string($legacyKey)) {
            $legacy = self::filled(Setting::get('business.'.$legacyKey));
            if ($legacy !== null) {
                return $legacy;
            }
        }

        $config = config('legal.'.$key);

        return self::filled(is_string($config) ? $config : null);
    }

    public static function email(): string
    {
        return self::effective()['legal_email']
            ?? self::filled(config('legal.legal_email'))
            ?? 'info@tuneupprecision.co.za';
    }
}
