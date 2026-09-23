<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;

/**
 * Flat shop courier fee. A value saved in admin Settings wins over
 * SHOP_SHIPPING_CENTS. 0 means delivery is included in the product price.
 */
final class ShopShipping
{
    public static function cents(): int
    {
        $saved = Setting::get('shop.shipping_cents');

        $cents = ($saved === null || $saved === '')
            ? (int) config('tuneup.shop.shipping_cents', 0)
            : (int) $saved;

        return max(0, $cents);
    }
}
