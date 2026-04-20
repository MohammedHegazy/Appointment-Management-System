<?php

namespace App\Enums;

enum WaitlistStatus: string
{
    case Waiting = 'waiting';
    case Offered = 'offered';
    case Accepted = 'accepted';
    case Expired = 'expired';
}

