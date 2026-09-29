<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Inbox for owner alerts: new testimonials (reviews), shop orders, and
 * website enquiries. The public legal email stays on the letterhead.
 */
final class OwnerInbox
{
    public static function email(): string
    {
        $configured = config('tuneup.notifications.email');

        if (is_string($configured) && filter_var(trim($configured), FILTER_VALIDATE_EMAIL)) {
            return mb_strtolower(trim($configured));
        }

        return 'dirkpio01@gmail.com';
    }
}
