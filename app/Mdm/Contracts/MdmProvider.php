<?php

declare(strict_types=1);

namespace App\Mdm\Contracts;

use App\Mdm\Mapping\MappingResult;

interface MdmProvider
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return iterable<MappingResult>
     */
    public function fetchAssignedDevices(): iterable;
}
