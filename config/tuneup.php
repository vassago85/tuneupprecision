<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | EFT bank details
    |--------------------------------------------------------------------------
    |
    | Displayed to guests at checkout (Phase 2) and on the admin Settings page.
    | For now these are sourced from the environment. A future commit can move
    | them into a persisted settings store if the client wants to edit them
    | without a deploy.
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

    'references' => [
        'booking' => 'TU-B-######',
        'order' => 'TU-S-######',
        'quote' => 'TU-{yy}{mm}-{4 digits}',
    ],

    /*
    |--------------------------------------------------------------------------
    | Business details (letterhead / quote PDF)
    |--------------------------------------------------------------------------
    |
    | Defaults from the environment. The admin Settings page can override
    | these at runtime via the settings table (see App\Support\BusinessDetails).
    |
    */

    'business' => [
        'tel' => env('TUNEUP_TEL', '+27 00 000 0000'),
        'email' => env('TUNEUP_EMAIL', 'dirk@tuneupprecision.co.za'),
        'vat_number' => env('TUNEUP_VAT_NO', '0000000000'),
        'dealer_number' => env('TUNEUP_DEALER_NO', '0000000'),
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
        'from_address' => env('MAIL_FROM_ADDRESS', 'hello@tuneupprecision.co.za'),
        'from_name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Tune Up Precision')),
        'mailgun_domain' => env('MAILGUN_DOMAIN'),
        'mailgun_secret' => env('MAILGUN_SECRET'),
        'mailgun_endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

];
