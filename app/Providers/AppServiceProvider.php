<?php

namespace App\Providers;

use App\Events\Orders\OrderCancelled;
use App\Events\Orders\OrderItemCreated;
use App\Events\Orders\OrderItemDeleted;
use App\Events\Orders\OrderItemUpdated;
use App\Events\Orders\OrderStatusChanged;
use App\Listeners\Orders\HandleOrderCancellation;
use App\Listeners\Orders\HandleOrderItemDeletion;
use App\Listeners\Orders\HandleOrderItemStockAssignment;
use App\Listeners\Orders\HandleOrderStatusChanged;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\User;
use App\Models\Warranty;
use App\Observers\DealerObserver;
use App\Observers\OrderItemObserver;
use App\Observers\OrderObserver;
use App\Observers\ServiceItemObserver;
use App\Observers\ServiceObserver;
use App\Observers\UserObserver;
use App\Observers\WarrantyObserver;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Fieldset::configureUsing(fn (Fieldset $fieldset) => $fieldset
            ->columnSpanFull());

        Grid::configureUsing(fn (Grid $grid) => $grid
            ->columnSpanFull());

        Section::configureUsing(fn (Section $section) => $section
            ->columnSpanFull());

        // Observer'ları kaydet
        Service::observe(ServiceObserver::class);
        ServiceItem::observe(ServiceItemObserver::class);
        Warranty::observe(WarrantyObserver::class);
        Order::observe(OrderObserver::class);
        OrderItem::observe(OrderItemObserver::class);
        Dealer::observe(DealerObserver::class);
        User::observe(UserObserver::class);

        // Event-Listener mapping'lerini kaydet
        Event::listen(
            OrderStatusChanged::class,
            HandleOrderStatusChanged::class
        );

        Event::listen(
            OrderCancelled::class,
            HandleOrderCancellation::class
        );

        Event::listen(
            OrderItemCreated::class,
            [HandleOrderItemStockAssignment::class, 'handleOrderItemCreated']
        );

        Event::listen(
            OrderItemUpdated::class,
            [HandleOrderItemStockAssignment::class, 'handleOrderItemUpdated']
        );

        Event::listen(
            OrderItemDeleted::class,
            HandleOrderItemDeletion::class
        );
    }
}
