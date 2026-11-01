<?php

namespace App\Modules\Product\Domain\Enums;

enum OrderTab: string
{
    case PENDING = 'pending';
    case CONFIRM = 'confirm';
    case DONE = 'done';
    case CANCEL = 'cancel';
}
