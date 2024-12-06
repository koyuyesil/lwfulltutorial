<?php

namespace App\Enums;

enum PriotyType: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
}