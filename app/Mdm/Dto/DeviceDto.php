<?php

declare(strict_types=1);

namespace App\Mdm\Dto;

final readonly class DeviceDto
{
    /**
     * @param string $serial
     * @param string $email
     * @param string $deviceName
     * @param string $provider
     * @param string|null $employeeName
     * @param string|null $phone
     * @param array<string, mixed> $specs
     */
    public function __construct(
        public string  $serial,
        public string  $email,
        public string  $deviceName,
        public string  $provider,
        public ?string $employeeName = null,
        public ?string $phone = null,
        public array   $specs = [],
    )
    {
    }
}
