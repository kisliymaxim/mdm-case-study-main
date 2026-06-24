<?php

declare(strict_types=1);

namespace App\Mdm\Jamf;

use App\Mdm\Contracts\MdmProvider;
use Generator;
use RuntimeException;
use Throwable;

final readonly class JamfProvider implements MdmProvider
{
    /**
     * @param JamfDeviceMapper $mapper
     */
    public function __construct(private JamfDeviceMapper $mapper)
    {
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'jamf';
    }

    /**
     * @return Generator
     * @throws Throwable
     */
    public function fetchAssignedDevices(): Generator
    {
        $mockPath = storage_path('app/jamf/api-mock-response.json');
        if (!is_file($mockPath) || !is_readable($mockPath)) {
            throw new RuntimeException("Jamf mock payload not found at: {$mockPath}");
        }

        $raw = file_get_contents($mockPath);
        $payload = json_decode((string)$raw, true, flags: JSON_THROW_ON_ERROR);

        $results = data_get($payload, 'results', []);
        if (!is_array($results)) {
            return;
        }

        foreach ($results as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            yield $this->mapper->map($entry);
        }
    }
}
