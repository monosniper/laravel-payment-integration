<?php

namespace Monosniper\LaravelPayment\Enums\Payme;

use Monosniper\LaravelPayment\Enums\BaseEnum;

enum Method: string
{
    use BaseEnum;

    case CHECK_PERFORM_TRANSACTION = 'CheckPerformTransaction';
    case CHECK_TRANSACTION = 'CheckTransaction';
    case CREATE_TRANSACTION = 'CreateTransaction';
    case PERFORM_TRANSACTION = 'PerformTransaction';
    case CANCEL_TRANSACTION = 'CancelTransaction';
    case CHANGE_PASSWORD = 'ChangePassword';
    case GET_STATEMENT = 'GetStatement';
}
