<?php

declare(strict_types=1);

namespace App\Mdm;

use App\Mdm\Mapping\SkipReason;

final class ImportSummary
{
    /**
     * @var int
     */
    private int $createdAssets = 0;

    /**
     * @var int
     */
    private int $updatedAssets = 0;

    /**
     * @var int
     */
    private int $createdEmployees = 0;

    /**
     * @var array<string, int>
     */
    private array $skipped = [];

    /**
     * @return void
     */
    public function recordCreatedAsset(): void
    {
        $this->createdAssets++;
    }

    /**
     * @return void
     */
    public function recordUpdatedAsset(): void
    {
        $this->updatedAssets++;
    }

    /**
     * @return void
     */
    public function recordCreatedEmployee(): void
    {
        $this->createdEmployees++;
    }

    /**
     * @param SkipReason $reason
     * @return void
     */
    public function recordSkip(SkipReason $reason): void
    {
        $this->skipped[$reason->value] = ($this->skipped[$reason->value] ?? 0) + 1;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'created_assets' => $this->createdAssets,
            'updated_assets' => $this->updatedAssets,
            'created_employees' => $this->createdEmployees,
            'skipped' => $this->skipped,
        ];
    }
}
