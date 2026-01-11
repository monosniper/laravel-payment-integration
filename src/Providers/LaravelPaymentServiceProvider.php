<?php

namespace Monosniper\LaravelPayment\Providers;

use Illuminate\Support\ServiceProvider;
use Monosniper\LaravelPayment\Services\Payment\Click;
use Monosniper\LaravelPayment\Services\Payment\Payme;
use Monosniper\LaravelPayment\Services\Payment\InfinityPay;
use Monosniper\LaravelPayment\Services\Payment\Paynet;
use Monosniper\LaravelPayment\Services\Payment\QuickPay;
use Monosniper\LaravelPayment\Services\Payment\Uzum;

class LaravelPaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Click::class, fn () => new Click());
        $this->app->singleton(Payme::class, fn () => new Payme());
        $this->app->singleton(Uzum::class, fn () => new Uzum());
        $this->app->singleton(QuickPay::class, fn () => new QuickPay());
        $this->app->singleton(InfinityPay::class, fn () => new InfinityPay());
        $this->app->singleton(Paynet::class, fn () => new Paynet());
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/payment.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'payment');

        $this->publishes([
            __DIR__.'/../config/payment.php' => config_path('payment.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config/payment.php', 'payment'
        );

        if ( ! defined('CURL_SSLVERSION_TLSv1_2')) { define('CURL_SSLVERSION_TLSv1_2', 6); }
    }
}