<?php

declare(strict_types=1);

namespace App\Mdm\Jamf;

use App\Mdm\Contracts\DeviceMapper;
use App\Mdm\Dto\DeviceDto;
use App\Mdm\Mapping\MappingResult;
use App\Mdm\Mapping\SkipReason;

final class JamfDeviceMapper implements DeviceMapper
{
    /**
     * @param array $rawDevice
     * @return MappingResult
     */
    public function map(array $rawDevice): MappingResult
    {
        $serial = $this->serial($rawDevice);
        if ($serial === null) {
            return MappingResult::skip(SkipReason::MissingSerial);
        }

        $email = $this->email($rawDevice);
        if ($email === null) {
            return MappingResult::skip(SkipReason::Unassigned);
        }

        return MappingResult::ok(new DeviceDto(
            serial: $serial,
            email: $email,
            deviceName: $this->deviceName($rawDevice),
            provider: 'jamf',
            employeeName: $this->name($rawDevice),
            phone: $this->phone($rawDevice),
            specs: [
                'model' => data_get($rawDevice, 'hardware.model') ?? data_get($rawDevice, 'model'),
                'model_identifier' => data_get($rawDevice, 'hardware.modelIdentifier'),
                'processor' => data_get($rawDevice, 'hardware.processorType'),
                'cores' => data_get($rawDevice, 'hardware.coreCount'),
                'ram_gb' => $this->ramGb($rawDevice),
                'storage_gb' => $this->storageGb($rawDevice),
            ],
        ));
    }

    /**
     * @param array $device
     * @return string|null
     */
    private function serial(array $device): ?string
    {
        $serial = data_get($device, 'hardware.serialNumber');
        if (!is_string($serial)) {
            return null;
        }
        $serial = trim($serial);

        return $serial !== '' ? $serial : null;
    }

    /**
     * @param array $device
     * @return string|null
     */
    private function email(array $device): ?string
    {
        $email = data_get($device, 'userAndLocation.email')
            ?? data_get($device, 'userAndLocation.emailAddress')
            ?? data_get($device, 'username');

        if (!is_string($email)) {
            return null;
        }

        $email = strtolower(trim($email));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * @param array $device
     * @return string|null
     */
    private function name(array $device): ?string
    {
        $name = data_get($device, 'userAndLocation.realname')
            ?? data_get($device, 'userAndLocation.realName');

        if (!is_string($name)) {
            return null;
        }
        $name = trim($name);

        return $name !== '' ? $name : null;
    }

    /**
     * @param array $device
     * @return string|null
     */
    private function phone(array $device): ?string
    {
        $phone = data_get($device, 'userAndLocation.phone')
            ?? data_get($device, 'userAndLocation.phoneNumber');

        if (!is_string($phone)) {
            return null;
        }
        $phone = trim($phone);

        return $phone !== '' ? $phone : null;
    }

    /**
     * @param array $device
     * @return string
     */
    private function deviceName(array $device): string
    {
        $name = data_get($device, 'general.displayName')
            ?? data_get($device, 'general.name')
            ?? data_get($device, 'hardware.model')
            ?? data_get($device, 'model')
            ?? data_get($device, 'name');

        return is_string($name) && trim($name) !== '' ? trim($name) : 'Unknown';
    }

    /**
     * @param array $device
     * @return float|null
     */
    private function ramGb(array $device): ?float
    {
        $mb = data_get($device, 'hardware.totalRamMegabytes');
        if (!is_numeric($mb) || (float) $mb <= 0) {
            return null;
        }

        return round(((float) $mb) / 1024, 2);
    }

    /**
     * @param array $device
     * @return float|null
     */
    private function storageGb(array $device): ?float
    {
        $disks = data_get($device, 'storage.disks');
        if (!is_array($disks)) {
            return null;
        }

        $totalMb = 0.0;
        foreach ($disks as $disk) {
            $size = is_array($disk) ? ($disk['sizeMegabytes'] ?? null) : null;
            if (is_numeric($size)) {
                $totalMb += (float) $size;
            }
        }

        if ($totalMb <= 0) {
            return null;
        }

        return round($totalMb / 1024, 2);
    }
}
