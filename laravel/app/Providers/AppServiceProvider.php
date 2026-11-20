<?php

namespace App\Providers;

use App\Cms\Site;
use App\Models\Destination;
use App\Models\Package;
use App\Models\Popup;
use App\Models\Section;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Site::class);
    }

    public function boot(): void
    {
        Relation::morphMap([
            'section' => Section::class,
            'package' => Package::class,
            'destination' => Destination::class,
            'popup' => Popup::class,
        ]);
        Password::defaults(fn () => Password::min(12)->letters()->numbers());
        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
            }
        });
        View::composer(['layouts.*', 'pages.*', 'components.*', 'partials.*'], fn ($view) => $view->with('site', app(Site::class)));
    }
}
