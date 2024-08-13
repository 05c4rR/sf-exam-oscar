<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PENDING   = 'pending';     
    case CONFIRMED = 'confirmed';
    case SHIPPED   = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}
