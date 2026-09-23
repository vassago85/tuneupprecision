<?php

declare(strict_types=1);

namespace App\Shop;

use App\Models\Product;
use App\Support\VatPrice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Session cart. Prices and stock are always read from the product row,
 * never trusted from the session.
 */
final class Cart
{
    private const string SESSION_KEY = 'shop.cart';

    /**
     * @return array<int, int>
     */
    public function items(): array
    {
        $raw = Session::get(self::SESSION_KEY, []);
        if (! is_array($raw)) {
            return [];
        }

        $items = [];
        foreach ($raw as $id => $qty) {
            $productId = (int) $id;
            $quantity = (int) $qty;
            if ($productId > 0 && $quantity > 0) {
                $items[$productId] = $quantity;
            }
        }

        return $items;
    }

    public function count(): int
    {
        return array_sum($this->items());
    }

    public function add(Product $product, int $qty = 1): void
    {
        $qty = max(1, $qty);
        $items = $this->items();
        $next = ($items[$product->id] ?? 0) + $qty;
        $items[$product->id] = min($next, max(1, (int) $product->stock_qty));
        $this->put($items);
    }

    public function set(Product $product, int $qty): void
    {
        $items = $this->items();

        if ($qty < 1 || ! $product->is_active || (int) $product->price_cents < 1) {
            unset($items[$product->id]);
            $this->put($items);

            return;
        }

        $items[$product->id] = min($qty, max(1, (int) $product->stock_qty));
        $this->put($items);
    }

    public function remove(int $productId): void
    {
        $items = $this->items();
        unset($items[$productId]);
        $this->put($items);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Live lines for products that are still for sale. Stale session rows are dropped.
     *
     * @return Collection<int, array{product: Product, qty: int, line_cents: int}>
     */
    public function lines(): Collection
    {
        $items = $this->items();
        if ($items === []) {
            return collect();
        }

        $products = Product::query()
            ->available()
            ->whereIn('id', array_keys($items))
            ->get()
            ->keyBy('id');

        $fresh = [];
        $lines = collect();

        foreach ($items as $id => $qty) {
            $product = $products->get($id);
            if ($product === null) {
                continue;
            }

            $qty = min($qty, (int) $product->stock_qty);
            if ($qty < 1) {
                continue;
            }

            $fresh[$id] = $qty;
            $lines->push([
                'product' => $product,
                'qty' => $qty,
                'line_cents' => (int) $product->price_cents * $qty,
            ]);
        }

        if ($fresh !== $items) {
            $this->put($fresh);
        }

        return $lines;
    }

    public function goodsCents(): int
    {
        return (int) $this->lines()->sum('line_cents');
    }

    public function shippingCents(): int
    {
        if ($this->goodsCents() < 1) {
            return 0;
        }

        return max(0, (int) config('tuneup.shop.shipping_cents', 0));
    }

    public function totalCents(): int
    {
        return $this->goodsCents() + $this->shippingCents();
    }

    public function vatCents(): int
    {
        return VatPrice::includedVatCents($this->totalCents());
    }

    /**
     * @param  array<int, int>  $items
     */
    private function put(array $items): void
    {
        Session::put(self::SESSION_KEY, $items);
    }
}
