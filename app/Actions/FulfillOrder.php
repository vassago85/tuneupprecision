<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Mail\OrderDispatched;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Dirk has handed a paid shop order to the courier.
 * A second call does not email the customer again.
 */
final class FulfillOrder
{
    public function handle(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $order->refresh();

            if ($order->status !== OrderStatus::Paid) {
                return $order;
            }

            $order->update(['status' => OrderStatus::Fulfilled]);

            if (filled($order->email)) {
                $order->loadMissing('orderItems');
                Mail::to($order->email)->queue(new OrderDispatched($order));
            }

            return $order;
        });
    }
}
