<?php

use Illuminate\Support\Facades\Route;
use Monosniper\LaravelPayment\Http\Controllers\Api\{
    ClickController,
    OctobankController,
    InfinityPayController,
    PaymeController,
    QuickPayController,
    UzumController,
};

Route::prefix(config('payment.api_prefix'))->group(function () {
    Route::prefix('payment')->as('payment.')
        ->group(function () {
            if(config('payment.octobank.enabled')) {
                Route::post('octobank', OctobankController::class)->name('octobank');
            }

            if(config('payment.click.enabled')) {
                Route::post('click', ClickController::class)->name('click');
            }

            if(config('payment.payme.enabled')) {
                Route::post('payme', PaymeController::class)->name('payme');
            }

            if(config('payment.uzum.enabled')) {
                Route::post('uzum', UzumController::class)->name('uzum');
            }

            if(config('payment.quickpay.enabled')) {
                Route::post('quickpay', QuickPayController::class)->name('quickpay');
            }

            if(config('payment.infinitypay.enabled')) {
                Route::prefix('infinitypay')
                    ->as('infinitypay.')
                    ->controller(InfinityPayController::class)
                    ->group(function () {
                        Route::post('info', 'info')->name('info');
                        Route::post('pay', 'pay')->name('pay');
                        Route::post('notify', 'notify')->name('notify');
                        Route::post('cancel', 'cancel')->name('cancel');
                        Route::post('statement', 'statement')->name('statement');
                        Route::post('fiscalization', 'fiscalization')->name('fiscalization');
                    });
            }
    });
});