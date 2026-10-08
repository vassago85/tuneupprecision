<?php

declare(strict_types=1);

return [

    /*
    | How long a public booking holds its seats before unpaid holds are
    | released by bookings:release-holds. Dirk confirming payment clears this.
    */
    'booking_hold_hours' => (int) env('BOOKING_HOLD_HOURS', 72),

    /*
    |--------------------------------------------------------------------------
    | EFT bank details
    |--------------------------------------------------------------------------
    |
    | Displayed at shop checkout, on a course booking, and on the admin
    | Settings page. A value saved in Settings wins over these defaults.
    |
    */

    'eft' => [
        'bank_name' => env('EFT_BANK_NAME', 'FNB'),
        'account_name' => env('EFT_ACCOUNT_NAME', 'Tune Up Long Range Precision Shooting'),
        'account_number' => env('EFT_ACCOUNT_NUMBER', '0000000000'),
        'branch_code' => env('EFT_BRANCH_CODE', '250655'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reference formats
    |--------------------------------------------------------------------------
    |
    | Bookings => TU-B-000123, Orders => TU-S-000123 (see App\Support\HasReference).
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Shop
    |--------------------------------------------------------------------------
    |
    | Courier fee in cents. 0 means delivery is included in the product price.
    | SHOP_SHIPPING_CENTS is the fallback until a fee is saved under
    | Admin → Settings → Shop delivery. A saved 0 still means included.
    |
    */

    'shop' => [
        'shipping_cents' => (int) env('SHOP_SHIPPING_CENTS', 0),
    ],

    'references' => [
        'booking' => 'TU-B-######',
        'order' => 'TU-S-######',
        'quote' => 'TU-{yy}{mm}-{4 digits}',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rifle builder
    |--------------------------------------------------------------------------
    */

    'rifle_builder' => [
        'vat_rate' => 0.15,
        'default_lead_time' => '8–12 weeks',
        'lead_time_buffer_weeks' => 4,
        'quote_validity_days' => 14,
        'default_deposit_percent' => 50,
        'chamberings' => [
            '6mm Dasher',
            '6mm Creedmoor',
            '6 GT',
            '22 Creedmoor',
            '6.5 Creedmoor',
            '6.5 PRC',
            '.308 Win',
            '7mm PRC',
            '.223 Rem',
            '.300 Win Mag',
        ],
        'barrel_lengths' => ['20"', '22"', '24"', '26"', '28"'],
        'twists' => ['1:7', '1:7.5', '1:8', '1:9', '1:10', '1:11'],
        'finishes' => [
            'Bead-blast stainless',
            'Nitride black',
            'Cerakote (see extras)',
        ],
        'footprint_labels' => [
            'rem700' => 'Rem 700',
            'tikka' => 'Tikka',
            'ruger' => 'Ruger American',
        ],
        'labour_slugs' => [
            'chambering' => 'chambering-fitting-headspacing',
            'assembly' => 'assembly-torque-function-check',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mail defaults
    |--------------------------------------------------------------------------
    |
    | Fallback values for outgoing mail. Anything saved on the admin Email
    | settings page (settings table, mail.* keys) takes precedence over these
    | env-based defaults — see App\Support\MailSettings. Secrets should still
    | live in .env in production; the admin UI simply lets Dirk switch mailer,
    | tweak the from address, or drop in Mailgun credentials without a deploy.
    |
    */

    'mail' => [
        'mailer' => env('MAIL_MAILER', 'log'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'info@tuneupprecision.co.za'),
        'from_name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Tune Up Precision')),
        'mailgun_domain' => env('MAILGUN_DOMAIN'),
        'mailgun_secret' => env('MAILGUN_SECRET'),
        'mailgun_endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

];
