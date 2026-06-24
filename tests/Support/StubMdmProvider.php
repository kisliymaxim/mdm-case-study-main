<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Mdm\Contracts\MdmProvider;
use Generator;

/**
 * Minimal MdmProvider for tests that only need a name + an empty stream.
 * Avoids mocking final classes and keeps the action test focused on the
 * "creates Import + dispatches job" behaviour, not provider mechanics.
 */
final class StubMdmProvider implements MdmProvider
{
    public function __construct(private readonly string $name = 'stub')
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function fetchAssignedDevices(): Generator
    {
        yield from [];
    }
}
