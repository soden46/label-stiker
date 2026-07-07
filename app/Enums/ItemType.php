<?php

namespace App\Enums;

enum ItemType: string
{
    case RawMaterial = 'raw_material';
    case WorkInProgress = 'work_in_progress';
    case FinishedGood = 'finished_good';
    case Trading = 'trading';
    case Service = 'service';
}
