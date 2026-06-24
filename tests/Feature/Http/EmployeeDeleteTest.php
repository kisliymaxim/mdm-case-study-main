<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\Asset;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EmployeeDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_delete_blocked_when_assets_exist(): void
    {
        $employee = Employee::create(['email' => 'a@b.test', 'name' => 'A B']);
        Asset::create([
            'serial_code' => 'S1',
            'employee_id' => $employee->id,
            'device_name' => 'Mac',
            'provider' => 'jamf',
            'specs' => [],
        ]);

        $this->deleteJson("/api/employees/{$employee->id}")
            ->assertStatus(409)
            ->assertJsonStructure(['message', 'error_code'])
            ->assertJsonPath('error_code', 'employee_has_assets');

        $this->assertDatabaseHas('employees', ['id' => $employee->id]);
    }

    public function test_employee_delete_succeeds_when_no_assets(): void
    {
        $employee = Employee::create(['email' => 'a@b.test', 'name' => 'A B']);

        $this->deleteJson("/api/employees/{$employee->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }
}
