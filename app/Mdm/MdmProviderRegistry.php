<?php

declare(strict_types=1);

namespace App\Mdm;

use App\Mdm\Contracts\MdmProvider;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Throwable;

final readonly class MdmProviderRegistry
{
    /**
     * @param Container $container
     * @param string[] $providers
     * @param string $default
     */
    public function __construct(
        private Container $container,
        private array     $providers,
        private string    $default
    ) {}

    /**
     * @param string|null $key
     * @return MdmProvider
     * @throws Throwable
     */
    public function get(?string $key = null): MdmProvider
    {
        $key = $key ?? $this->default;

        if (!isset($this->providers[$key])) {
            throw new InvalidArgumentException("Unknown MDM provider: {$key}");
        }

        return $this->container->make($this->providers[$key]);
    }

    /**
     * @return string[]
     */
    public function available(): array
    {
        return array_keys($this->providers);
    }
}
