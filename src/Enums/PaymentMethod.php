<?php

namespace Monosniper\LaravelPayment\Enums;

enum PaymentMethod: string
{
    use BaseEnum;

    case COUPON = 'coupon';
    case CLICK = 'click';
    case PAYME = 'payme';
    case UZUM = 'uzum';
    case QUICKPAY = 'quickpay';
    case INFINITYPAY = 'infinitypay';
    case PAYNET = 'paynet';
    case CASH = 'cash';
    case TRANSFER = 'transfer';
    case UZUM_NASIYA = 'uzum_nasiya';
    case ALIF_NASIYA = 'alif_nasiya';
    case OCTOBANK = 'octobank';
}
