<?php

namespace App\Enum;

enum TransactionCategoryEnum: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';
}
