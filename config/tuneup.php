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

];
