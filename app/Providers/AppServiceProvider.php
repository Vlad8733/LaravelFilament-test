<?php

namespace App\Providers;

use App\Models\Ticket;
use App\Observers\TicketObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

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

        // Синхронизируем локаль Carbon с локалью приложения
        Carbon::setLocale(App::getLocale());
        
        // Или можно использовать middleware для динамической смены
    }
}
