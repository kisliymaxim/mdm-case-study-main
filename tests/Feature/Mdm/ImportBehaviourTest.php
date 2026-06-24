<?php

declare(strict_types=1);

namespace Tests\Feature\Mdm;

use App\Mdm\MdmImportOrchestrator;
use App\Models\Asset;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DeviceFactory;
use Tests\Support\JamfArrayProvider;
use Tests\TestCase;

/**
 * Covers every rule listed under "MDM Sync Behaviour Rules" in the README.
 *
 * Each test feeds the orchestrator a tiny in-memory payload via
 * JamfArrayProvider (which reuses the real JamfDeviceMapper), so we exercise
 * the production mapping + import logic without touching disk.
 */
final class ImportBehaviourTest extends TestCase
{
    use RefreshDatabase;

    private function import(array $rawDevices): array
    {
        $provider = new JamfArrayProvider($rawDevices);
        $summary = $this->app->make(MdmImportOrchestrator::class)->run($provider);

        return $summary->toArray();
    }

    public function test_unassigned_device_is_skipped(): void
    {
        $summary = $this->import([
            DeviceFactory::jamfDevice(['userAndLocation' => ['email' => null, 'username' => null]]),
        ]);

        $this->assertSame(0, $summary['created_assets']);
        $this->assertSame(1, $summary['skipped']['unassigned'] ?? 0);
        $this->assertSame(0, Asset::count());
        $this->assertSame(0, Employee::count());
    }

    public function test_missing_serial_is_skipped(): void
    {
        $summary = $this->import([
            DeviceFactory::jamfDevice(['hardware' => ['serialNumber' => null]]),
        ]);

        $this->assertSame(0, $summary['created_assets']);
        $this->assertSame(1, $summary['skipped']['missing_serial'] ?? 0);
        $this->assertSame(0, Asset::count());
    }

    public function test_missing_employee_is_created(): void
    {
        $this->import([DeviceFactory::jamfDevice()]);

        $this->assertDatabaseHas('employees', [
            'email' => 'alex.smith@company.test',
            'name' => 'Alex Smith',
            'phone' => '+491234567890',
        ]);
    }

    public function test_employee_matched_case_insensitively_by_email(): void
    {
        Employee::create(['email' => 'alex.smith@company.test', 'name' => 'Existing']);

        $this->import([
            DeviceFactory::jamfDevice([
                'userAndLocation' => ['email' => 'ALEX.SMITH@Company.Test', 'realname' => 'Alex Updated'],
            ]),
        ]);

        $this->assertSame(1, Employee::count());
        $this->assertDatabaseHas('employees', [
            'email' => 'alex.smith@company.test',
            'name' => 'Alex Updated',
        ]);
    }

    public function test_asset_matched_by_serial_not_recreated(): void
    {
        $this->import([DeviceFactory::jamfDevice()]);
        $this->import([DeviceFactory::jamfDevice()]);

        $this->assertSame(1, Asset::count());
    }

    public function test_deleted_asset_is_recreated_on_reimport(): void
    {
        $this->import([DeviceFactory::jamfDevice()]);
        Asset::where('serial_code', 'C02DL0XYZQ6N')->delete();
        $this->assertSame(0, Asset::count());

        $summary = $this->import([DeviceFactory::jamfDevice()]);

        $this->assertSame(1, $summary['created_assets']);
        $this->assertSame(1, Asset::where('serial_code', 'C02DL0XYZQ6N')->count());
    }

    public function test_attribute_change_is_reflected(): void
    {
        $this->import([DeviceFactory::jamfDevice(['hardware' => ['totalRamMegabytes' => 8192]])]);
        $this->assertSame(8.0, (float) Asset::first()->specs['ram_gb']);

        $this->import([DeviceFactory::jamfDevice(['hardware' => ['totalRamMegabytes' => 16384]])]);

        $this->assertSame(16.0, (float) Asset::first()->fresh()->specs['ram_gb']);
    }

    public function test_multiple_devices_per_employee_supported(): void
    {
        $this->import([
            DeviceFactory::jamfDevice(),
            DeviceFactory::jamfDevice([
                'hardware' => ['serialNumber' => 'C02INTEL9999', 'model' => 'MacBook Pro 16'],
            ]),
        ]);

        $this->assertSame(1, Employee::count());
        $this->assertSame(2, Asset::where('employee_id', Employee::first()->id)->count());
    }

    public function test_device_name_overwritten_jamf_is_source_of_truth(): void
    {
        $this->import([DeviceFactory::jamfDevice(['general' => ['displayName' => 'Old Name']])]);

        $this->import([DeviceFactory::jamfDevice(['general' => ['displayName' => 'New Name']])]);

        $this->assertSame('New Name', Asset::first()->device_name);
    }

    public function test_provider_tag_is_jamf(): void
    {
        $this->import([DeviceFactory::jamfDevice()]);

        $this->assertSame('jamf', Asset::first()->provider);
    }
}
