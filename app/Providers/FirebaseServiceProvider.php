<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Factory;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Messaging::class, function () {
            return (new Factory)
                ->withServiceAccount(config('firebase.credentials'))
                ->createMessaging();
        });
    }

    public function boot(): void
    {
    }
}