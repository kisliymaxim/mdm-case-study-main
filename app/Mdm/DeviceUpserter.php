<?php

declare(strict_types=1);

namespace App\Mdm;

use App\Mdm\Dto\DeviceDto;
use App\Models\Asset;
use App\Models\Employee;

final class DeviceUpserter
{
    /**
     * @param DeviceDto $dto
     * @return array{Employee, bool}
     */
    public function upsertEmployee(DeviceDto $dto): array
    {
        $employee = Employee::query()->firstOrNew(['email' => $dto->email]);
        $wasCreated = !$employee->exists;

        $employee->name = $dto->employeeName ?? $employee->name;
        $employee->phone = $dto->phone ?? $employee->phone;
        $employee->save();

        return [$employee, $wasCreated];
    }

    /**
     * @param DeviceDto $dto
     * @param Employee $employee
     * @return array{Asset, bool}
     */
    public function upsertAsset(DeviceDto $dto, Employee $employee): array
    {
        $asset = Asset::query()->firstOrNew(['serial_code' => $dto->serial]);
        $wasCreated = !$asset->exists;

        $asset->employee_id = $employee->id;
        $asset->device_name = $dto->deviceName;
        $asset->provider = $dto->provider;
        $asset->specs = $dto->specs;
        $asset->save();

        return [$asset, $wasCreated];
    }
}
