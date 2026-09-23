<?php

declare(strict_types=1);

namespace App\Shop;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\Product;
use App\Support\BusinessDetails;
use App\Support\LegalIdentity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

final class PlaceShopOrder
{
    /**
     * @param  array{
     *     customer_name: string,
     *     email: string,
     *     phone: string,
     *     address_line_1: string,
     *     address_line_2: ?string,
     *     suburb: string,
     *     city: string,
     *     province: string,
     *     postal_code: string
     * }  $customer
     */
    public function place(Cart $cart, array $customer): Order
    {
        $order = DB::transaction(function () use ($cart, $customer): Order {
            $wanted = $cart->items();
            if ($wanted === []) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart is empty.',
                ]);
            }

            $lines = [];
            $goods = 0;

            foreach ($wanted as $productId => $qty) {
                $product = Product::query()->whereKey($productId)->lockForUpdate()->first();

                if ($product === null || ! $product->is_active || (int) $product->price_cents < 1 || (int) $product->stock_qty < $qty) {
                    throw ValidationException::withMessages([
                        'cart' => 'An item in your cart is no longer available in that quantity. Review the cart and try again.',
                    ]);
                }

                $lines[] = ['product' => $product, 'qty' => $qty];
                $goods += (int) $product->price_cents * $qty;
            }

            $shipping = $goods > 0 ? max(0, (int) config('tuneup.shop.shipping_cents', 0)) : 0;

            $order = Order::query()->create([
                'customer_name' => $customer['customer_name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'address_line_1' => $customer['address_line_1'],
                'address_line_2' => $customer['address_line_2'],
                'suburb' => $customer['suburb'],
                'city' => $customer['city'],
                'province' => $customer['province'],
                'postal_code' => $customer['postal_code'],
                'subtotal_cents' => $goods,
                'shipping_cents' => $shipping,
            ]);

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line['product'];
                $order->orderItems()->create([
                    'product_id' => $product->id,
                    'name_snapshot' => $product->name,
                    'price_cents_snapshot' => (int) $product->price_cents,
                    'qty' => $line['qty'],
                ]);
            }

            $order->payment()->create([
                'method' => PaymentMethod::Eft,
                'reference' => $order->reference,
                'amount_cents' => $order->totalCents(),
                'status' => PaymentStatus::Pending,
            ]);

            $cart->clear();

            return $order->load('orderItems', 'payment');
        });

        Mail::to($order->email)->queue(new OrderPlaced($order, forCustomer: true));

        $dirk = BusinessDetails::details()['email'] ?? LegalIdentity::email();
        if (filled($dirk)) {
            Mail::to($dirk)->queue(new OrderPlaced($order, forCustomer: false));
        }

        return $order;
    }
}
