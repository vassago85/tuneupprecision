<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Take a shop order off the queue.
 * Stock is only put back when it was taken — that happens on payment, not on checkout.
 * A sent order stays sent. This does not refund an EFT.
 */
final class CancelOrder
{
    public function handle(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $order->refresh();

            if (in_array($order->status, [OrderStatus::Fulfilled, OrderStatus::Cancelled], true)) {
                return $order;
            }

            if ($order->status === OrderStatus::Paid) {
                $order->load('orderItems.product');

                foreach ($order->orderItems as $item) {
                    $item->product?->increment('stock_qty', (int) $item->qty);
                }
            }

            $order->update(['status' => OrderStatus::Cancelled]);

            return $order;
        });
    }
}
