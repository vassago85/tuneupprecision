<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Merged statutory identity for legal pages, disclosure, and legal:check.
 *
 * Config (`config/legal.php`) is the source of truth. Filament Settings may
 * override telephone, email, VAT number and dealer licence at runtime.
 * Placeholder patterns are treated as empty so they never render.
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
     * Effective public identity: settings win for the four dashboard fields.
     *
     * @return array<string, mixed>
     */
    public static function effective(): array
    {
        $business = BusinessDetails::details();

        return [
            'legal_name' => self::filled(config('legal.legal_name')),
            'trading_as' => self::filled(config('legal.trading_as')),
            'legal_status' => self::filled(config('legal.legal_status')),
            'registration_no' => self::filled(config('legal.registration_no')),
            'vat_no' => $business['vat_number'],
            'dealer_licence_no' => $business['dealer_number'],
            'office_bearers' => self::filled(config('legal.office_bearers')),
            'physical_address' => self::filled(config('legal.physical_address')),
            'postal_address' => self::filled(config('legal.postal_address')),
            'legal_email' => $business['email'],
            'legal_phone' => $business['tel'],
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

    public static function email(): string
    {
        return self::effective()['legal_email']
            ?? self::filled(config('legal.legal_email'))
            ?? 'info@tuneupprecision.co.za';
    }
}
