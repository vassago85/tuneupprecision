<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Statutory identity (ECTA s43 / POPIA)
    |--------------------------------------------------------------------------
    |
    | Single source of truth for legal documents and the public disclosure.
    | Telephone, email, VAT and dealer licence can still be overridden at
    | runtime from Filament Settings (see App\Support\LegalIdentity).
    |
    */

    'legal_name' => env('LEGAL_NAME'),
    'trading_as' => env('LEGAL_TRADING_AS'),
    'legal_status' => env('LEGAL_STATUS'),
    'registration_no' => env('LEGAL_REGISTRATION_NO'),
    'vat_no' => env('LEGAL_VAT_NO'),
    'dealer_licence_no' => env('LEGAL_DEALER_LICENCE_NO'),
    'office_bearers' => env('LEGAL_OFFICE_BEARERS'),
    'physical_address' => env('LEGAL_PHYSICAL_ADDRESS'),
    'postal_address' => env('LEGAL_POSTAL_ADDRESS'),
    'legal_email' => env('LEGAL_EMAIL', 'info@tuneupprecision.co.za'),
    'legal_phone' => env('LEGAL_PHONE'),
    'jurisdiction' => 'South Africa',
    'forum' => 'Gauteng',
    'vat_rate' => 0.15,

    'updated' => [
        'terms' => '2026-09-14',
        'privacy' => '2026-09-14',
        'shipping' => '2026-09-14',
        'refunds' => '2026-09-14',
    ],

    /*
    |--------------------------------------------------------------------------
    | Booking cancellation (draft defaults — confirm with Dirk)
    |--------------------------------------------------------------------------
    */

    'booking' => [
        'full_refund_days' => (int) env('LEGAL_BOOKING_FULL_REFUND_DAYS', 14),
        'deposit_forfeit_days' => (int) env('LEGAL_BOOKING_DEPOSIT_FORFEIT_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Merch shipping (draft defaults — confirm with Dirk)
    |--------------------------------------------------------------------------
    */

    'shipping' => [
        'dispatch_business_days' => env('LEGAL_SHIPPING_DISPATCH_DAYS', '3–5'),
        'courier' => env('LEGAL_SHIPPING_COURIER', 'a South African courier'),
    ],

];
