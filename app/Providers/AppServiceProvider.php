<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Ticket;
use App\Observers\ProductObserver;
use App\Observers\TicketObserver;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        Ticket::observe(TicketObserver::class);
        Product::observe(ProductObserver::class);

        // Синхронизируем локаль Carbon с локалью приложения
        Carbon::setLocale(App::getLocale());

        // Или можно использовать middleware для динамической смены

        // Register Livewire components aliases explicitly so they are discoverable
        if (class_exists(\App\Http\Livewire\ImportProgress::class)) {
            Livewire::component('import-progress', \App\Http\Livewire\ImportProgress::class);
        }
    }
}
