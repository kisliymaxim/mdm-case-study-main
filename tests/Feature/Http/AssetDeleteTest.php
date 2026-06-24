<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\Asset;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AssetDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_asset_delete_removes_row_and_preserves_employee(): void
    {
        $employee = Employee::create(['email' => 'a@b.test', 'name' => 'A B']);
        $asset = Asset::create([
            'serial_code' => 'S1',
            'employee_id' => $employee->id,
            'device_name' => 'Mac',
            'provider' => 'jamf',
            'specs' => [],
        ]);

        $this->deleteJson("/api/assets/{$asset->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
        $this->assertDatabaseHas('employees', ['id' => $employee->id]);
    }

    public function test_asset_list_returns_eager_loaded_employee(): void
    {
        $employee = Employee::create(['email' => 'a@b.test', 'name' => 'A B']);
        Asset::create([
            'serial_code' => 'S1',
            'employee_id' => $employee->id,
            'device_name' => 'Mac',
            'provider' => 'jamf',
            'specs' => [],
        ]);

        $this->getJson('/api/assets')
            ->assertOk()
            ->assertJsonPath('data.0.serial_code', 'S1')
            ->assertJsonPath('data.0.employee.email', 'a@b.test');
    }
}
