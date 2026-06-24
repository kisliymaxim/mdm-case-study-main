<?php

declare(strict_types=1);

namespace App\Mdm\Mapping;

enum SkipReason: string
{
    case MissingSerial = 'missing_serial';
    case Unassigned = 'unassigned';
    case Invalid = 'invalid';
}
