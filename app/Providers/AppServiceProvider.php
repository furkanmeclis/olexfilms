<?php

namespace App\Providers;

use App\Models\Service;
use App\Models\ServiceItem;
use App\Observers\ServiceItemObserver;
use App\Observers\ServiceObserver;
use Illuminate\Support\ServiceProvider;use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;


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
        Service::observe(ServiceObserver::class);
        ServiceItem::observe(ServiceItemObserver::class);
    }
}
