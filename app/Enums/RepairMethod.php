<?php

namespace App\Enums;

enum RepairMethod: string
{
    case META = 'meta';
    case FLASH = 'flash';
    case PATCH = 'patch';
    case UNLOCKED = 'unlocked';
    case LOCKED = 'locked';
    case RESISTOR = 'resistor';
}
