<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Contact URLs that prefill the subject without creating a second page.
 *
 * A query string (/contact?subject=…) is a distinct URL. Google lists those
 * as "Alternate page with proper canonical tag" and does not index them.
 * The fragment stays on the one contact URL; the form reads it in the browser.
 */
final class ContactLink
{
    public const int MAX_SUBJECT = 160;

    public static function url(?string $subject = null): string
    {
        $base = route('contact.create');
        $subject = self::subject($subject);

        if ($subject === '') {
            return $base;
        }

        return $base.'#'.rawurlencode($subject);
    }

    public static function subject(?string $subject): string
    {
        $subject = str_replace(["\r", "\n"], ' ', (string) $subject);
        $subject = preg_replace('/\s+/u', ' ', $subject) ?? '';

        return mb_substr(trim($subject), 0, self::MAX_SUBJECT);
    }
}
