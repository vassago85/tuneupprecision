<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;

/**
 * Letterhead details for quote PDFs and the rifle-builder footer.
 * Settings-table values win over config/legal.php. Placeholders are omitted.
 */
final class BusinessDetails
{
    /**
     * @return array<string, string|null>
     */
    public static function details(): array
    {
        return [
            'tel' => LegalIdentity::filled(Setting::get('business.tel'))
                ?? LegalIdentity::filled(config('legal.legal_phone')),
            'email' => LegalIdentity::filled(Setting::get('business.email'))
                ?? LegalIdentity::filled(config('legal.legal_email')),
            'vat_number' => LegalIdentity::filled(Setting::get('business.vat_number'))
                ?? LegalIdentity::filled(config('legal.vat_no')),
            'dealer_number' => LegalIdentity::filled(Setting::get('business.dealer_number'))
                ?? LegalIdentity::filled(config('legal.dealer_licence_no')),
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return ['tel', 'email', 'vat_number', 'dealer_number'];
    }
}
