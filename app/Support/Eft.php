<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;

/**
 * Resolves the EFT bank details the app should use: any value saved in the
 * admin Settings page (settings table) takes precedence over the .env-based
 * config defaults. This is the single source used by the Settings page and,
 * later, the guest checkout screen.
 */
final class Eft
{
    /**
     * @return array<string, string|null>
     */
    public static function details(): array
    {
        return [
            'bank_name' => Setting::get('eft.bank_name', config('tuneup.eft.bank_name')),
            'account_name' => Setting::get('eft.account_name', config('tuneup.eft.account_name')),
            'account_number' => Setting::get('eft.account_number', config('tuneup.eft.account_number')),
            'branch_code' => Setting::get('eft.branch_code', config('tuneup.eft.branch_code')),
        ];
    }

    /**
     * The editable EFT keys, in display order.
     *
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return ['bank_name', 'account_name', 'account_number', 'branch_code'];
    }
}
