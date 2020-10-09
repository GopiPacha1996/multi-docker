<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Razorpay\Api\Api as RApi;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(RApi::class, function ($app){
            return new RApi(
                env("RPAY_KEY","rzp_test_3Yal3Yt44eXwut"),
                env("RPAY_SECRET","2GcSQUVnHF4YlqQWyluIcjZ9")
            );
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
