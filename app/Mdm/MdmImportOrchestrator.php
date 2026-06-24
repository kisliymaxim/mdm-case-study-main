<?php

declare(strict_types=1);

namespace App\Mdm;

use App\Mdm\Contracts\MdmProvider;
use Illuminate\Support\Facades\DB;

final readonly class MdmImportOrchestrator
{
    /**
     * @param DeviceUpserter $upserter
     */
    public function __construct(private DeviceUpserter $upserter) {}

    /**
     * @param MdmProvider $provider
     * @return ImportSummary
     */
    public function run(MdmProvider $provider): ImportSummary
    {
        $summary = new ImportSummary();

        DB::transaction(function () use ($provider, $summary) {
            foreach ($provider->fetchAssignedDevices() as $result) {
                if (!$result->isOk()) {
                    $summary->recordSkip($result->skipReason);
                    continue;
                }

                [$employee, $employeeCreated] = $this->upserter->upsertEmployee($result->dto);
                $employeeCreated && $summary->recordCreatedEmployee();

                [$asset, $assetCreated] = $this->upserter->upsertAsset($result->dto, $employee);
                $assetCreated ? $summary->recordCreatedAsset() : $summary->recordUpdatedAsset();
            }
        });

        return $summary;
    }
}
