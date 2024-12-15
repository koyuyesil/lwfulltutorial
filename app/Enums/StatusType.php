<?php

namespace App\Enums;

enum StatusType: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';

    public function color(): string
    {
        return match ($this) {
            self::OPEN => 'border-blue-500',
            self::IN_PROGRESS => 'border-yellow-500',
            self::DONE => 'border-green-500',
        };
    }
}
