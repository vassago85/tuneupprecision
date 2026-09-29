<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Letterhead details for quote PDFs and the rifle-builder footer.
 * Resolved through LegalIdentity so Legal & Compliance and Settings agree.
 * Placeholders are omitted.
 */
final class BusinessDetails
{
    /**
     * @return array<string, string|null>
     */
    public static function details(): array
    {
        $legal = LegalIdentity::effective();

        return [
            'tel' => $legal['legal_phone'],
            'email' => $legal['legal_email'],
            'vat_number' => $legal['vat_no'],
            'dealer_number' => $legal['dealer_licence_no'],
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
