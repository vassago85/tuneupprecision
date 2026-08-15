<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;

/**
 * Letterhead details for quote PDFs. Settings-table values win over config.
 */
final class BusinessDetails
{
    /**
     * @return array<string, string|null>
     */
    public static function details(): array
    {
        return [
            'tel' => Setting::get('business.tel', config('tuneup.business.tel')),
            'email' => Setting::get('business.email', config('tuneup.business.email')),
            'vat_number' => Setting::get('business.vat_number', config('tuneup.business.vat_number')),
            'dealer_number' => Setting::get('business.dealer_number', config('tuneup.business.dealer_number')),
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
