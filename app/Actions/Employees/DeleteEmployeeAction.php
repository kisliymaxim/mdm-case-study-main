<?php

declare(strict_types=1);

namespace App\Actions\Employees;

use App\Exceptions\Domain\EmployeeHasAssetsException;
use App\Models\Employee;
use App\Services\StatsService;

final readonly class DeleteEmployeeAction
{
    /**
     * @param StatsService $stats
     */
    public function __construct(private StatsService $stats)
    {
    }

    /**
     * @param Employee $employee
     * @return void
     */
    public function handle(Employee $employee): void
    {
        if ($employee->assets()->exists()) {
            throw new EmployeeHasAssetsException($employee);
        }

        $employee->delete();
        $this->stats->invalidate();
    }
}
