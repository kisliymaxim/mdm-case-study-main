<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Mdm\Contracts\MdmProvider;
use App\Mdm\Jamf\JamfDeviceMapper;
use App\Mdm\Mapping\MappingResult;
use Generator;

/**
 * Test double for tests/Feature/Mdm/.
 *
 * Behaves exactly like JamfProvider except the raw payload is injected as
 * an array instead of read from disk. Reuses the real JamfDeviceMapper so
 * tests still exercise the production mapping logic.
 */
final class JamfArrayProvider implements MdmProvider
{
    /**
     * @param  array<int, array<string, mixed>>  $rawDevices
     */
    public function __construct(
        private readonly array $rawDevices,
        private readonly JamfDeviceMapper $mapper = new JamfDeviceMapper(),
    ) {
    }

    public function name(): string
    {
        return 'jamf';
    }

    public function fetchAssignedDevices(): Generator
    {
        foreach ($this->rawDevices as $device) {
            yield $this->mapper->map($device);
        }
    }
}
