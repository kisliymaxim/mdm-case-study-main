<?php

declare(strict_types=1);

namespace Tests\Support;

/**
 * Helpers for building Jamf-shaped raw device arrays in tests.
 *
 * Keeps the noisy nested structure out of individual test methods so each
 * test reads as "the rule under verification" rather than 20 lines of JSON.
 */
final class DeviceFactory
{
    /**
     * @param  array<string, mixed>  $overrides  Nested overrides applied via array_merge_recursive
     * @return array<string, mixed>
     */
    public static function jamfDevice(array $overrides = []): array
    {
        $base = [
            'id' => '101',
            'general' => [
                'name' => 'Alex’s MacBook Pro',
                'displayName' => null,
            ],
            'userAndLocation' => [
                'username' => 'alex.smith',
                'realname' => 'Alex Smith',
                'email' => 'alex.smith@company.test',
                'phone' => '+491234567890',
            ],
            'hardware' => [
                'make' => 'Apple',
                'model' => 'MacBook Pro (14-inch, M1 Pro, 2021)',
                'modelIdentifier' => 'MacBookPro18,3',
                'serialNumber' => 'C02DL0XYZQ6N',
                'processorType' => 'Apple M1 Pro',
                'coreCount' => 10,
                'totalRamMegabytes' => 16384,
            ],
        ];

        return self::mergeDeep($base, $overrides);
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private static function mergeDeep(array $base, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = self::mergeDeep($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }
}
