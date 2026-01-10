<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Ticket;
use App\Observers\CategoryObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\TicketObserver;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use SocialiteProviders\Discord\DiscordExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

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
        Category::observe(CategoryObserver::class);
        Order::observe(OrderObserver::class);

        // Синхронизируем локаль Carbon с локалью приложения
        Carbon::setLocale(App::getLocale());

        // Register View Composers for cached data
        \Illuminate\Support\Facades\View::composer(
            ['layouts.app', 'layouts.navigation', 'components.navigation'],
            \App\View\Composers\NavigationComposer::class
        );

        \Illuminate\Support\Facades\View::composer(
            'welcome',
            \App\View\Composers\HomepageComposer::class
        );

        // Register Livewire components aliases explicitly so they are discoverable
        if (class_exists(\App\Http\Livewire\ImportProgress::class)) {
            Livewire::component('import-progress', \App\Http\Livewire\ImportProgress::class);
        }

        // Register Discord Socialite driver
        Event::listen(SocialiteWasCalled::class, DiscordExtendSocialite::class.'@handle');
    }
}
