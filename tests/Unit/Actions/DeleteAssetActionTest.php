<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Assets\DeleteAssetAction;
use App\Models\Asset;
use App\Models\Employee;
use App\Services\StatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class DeleteAssetActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_the_asset_and_keeps_the_employee(): void
    {
        [$asset, $employee] = $this->makeAsset();

        $this->action()->handle($asset);

        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
        $this->assertDatabaseHas('employees', ['id' => $employee->id]);
    }

    public function test_invalidates_stats_cache_on_delete(): void
    {
        [$asset] = $this->makeAsset('S2');

        Cache::put('stats:snapshot', ['primed' => true], 60);
        $this->assertTrue(Cache::has('stats:snapshot'));

        $this->action()->handle($asset);

        $this->assertFalse(Cache::has('stats:snapshot'));
    }

    private function action(): DeleteAssetAction
    {
        return new DeleteAssetAction(app(StatsService::class));
    }

    /**
     * @return array{0: Asset, 1: Employee}
     */
    private function makeAsset(string $serial = 'S1'): array
    {
        $employee = Employee::firstOrCreate(['email' => 'a@b.test'], ['name' => 'A']);
        $asset = Asset::create([
            'serial_code' => $serial,
            'employee_id' => $employee->id,
            'device_name' => 'Mac',
            'provider' => 'jamf',
            'specs' => [],
        ]);

        return [$asset, $employee];
    }
}
