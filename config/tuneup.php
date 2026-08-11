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
