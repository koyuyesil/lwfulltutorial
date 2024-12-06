<?php

namespace App\Enums;

enum StatusType: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';
}