<?php

namespace App\Events\Orders;

use App\Models\OrderItem;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderItemUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public OrderItem $orderItem
    ) {}
}
