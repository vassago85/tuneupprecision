<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;

/**
 * Inbox for owner alerts: bookings, shop orders, build enquiries, new
 * testimonials, website enquiries and questionnaire answers. Editable on the
 * admin Email settings page.
 */
final class OwnerInbox
{
    public const string DEFAULT_EMAIL = 'info@tuneupprecision.co.za';

    public const string SETTING_KEY = 'mail.notify_email';

    public static function email(): string
    {
        $saved = trim((string) Setting::get(self::SETTING_KEY));

        if (filter_var($saved, FILTER_VALIDATE_EMAIL)) {
            return mb_strtolower($saved);
        }

        return self::DEFAULT_EMAIL;
    }
}
