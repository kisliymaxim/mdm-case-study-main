<?php

declare(strict_types=1);

namespace App\Mdm\Contracts;

use App\Mdm\Mapping\MappingResult;

interface DeviceMapper
{
    /**
     * @param array<string, mixed> $rawDevice
     * @return MappingResult
     */
    public function map(array $rawDevice): MappingResult;
}
