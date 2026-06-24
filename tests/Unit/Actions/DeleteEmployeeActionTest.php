<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Employees\DeleteEmployeeAction;
use App\Exceptions\Domain\EmployeeHasAssetsException;
use App\Models\Asset;
use App\Models\Employee;
use App\Services\StatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class DeleteEmployeeActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_employee_with_no_assets(): void
    {
        $employee = Employee::create(['email' => 'a@b.test', 'name' => 'A']);

        $this->action()->handle($employee);

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    public function test_invalidates_stats_cache_after_successful_delete(): void
    {
        $employee = Employee::create(['email' => 'a@b.test', 'name' => 'A']);

        Cache::put('stats:snapshot', ['primed' => true], 60);

        $this->action()->handle($employee);

        $this->assertFalse(Cache::has('stats:snapshot'));
    }

    public function test_throws_when_assets_still_assigned(): void
    {
        $employee = Employee::create(['email' => 'a@b.test', 'name' => 'A']);
        Asset::create([
            'serial_code' => 'S1',
            'employee_id' => $employee->id,
            'device_name' => 'Mac',
            'provider' => 'jamf',
            'specs' => [],
        ]);

        Cache::put('stats:snapshot', ['primed' => true], 60);

        try {
            $this->action()->handle($employee);
            $this->fail('Expected EmployeeHasAssetsException');
        } catch (EmployeeHasAssetsException $e) {
            $this->assertSame($employee->id, $e->employee->id);
        }

        // Employee still there, cache NOT invalidated on failure path.
        $this->assertDatabaseHas('employees', ['id' => $employee->id]);
        $this->assertTrue(Cache::has('stats:snapshot'));
    }

    private function action(): DeleteEmployeeAction
    {
        return new DeleteEmployeeAction(app(StatsService::class));
    }
}
