<?php

namespace App\Events\Orders;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Order $order,
        public OrderStatusEnum $oldStatus,
        public OrderStatusEnum $newStatus
    ) {
    }
}

