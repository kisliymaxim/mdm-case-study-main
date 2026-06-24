<?php

declare(strict_types=1);

namespace App\Mdm\Mapping;

use App\Mdm\Dto\DeviceDto;

final readonly class MappingResult
{
    private function __construct(
        public ?DeviceDto  $dto,
        public ?SkipReason $skipReason,
    ) {
    }

    public static function ok(DeviceDto $dto): self
    {
        return new self($dto, null);
    }

    public static function skip(SkipReason $reason): self
    {
        return new self(null, $reason);
    }

    public function isOk(): bool
    {
        return $this->dto !== null;
    }
}
