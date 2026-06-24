<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\Asset;
use App\Models\Employee;
use App\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

final class ImportEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_post_imports_dispatches_job_and_returns_record(): void
    {
        // phpunit.xml sets QUEUE_CONNECTION=sync, so the job runs inline.
        $response = $this->postJson('/api/imports');

        $response->assertStatus(202)
            ->assertJsonStructure(['import' => ['id', 'provider', 'status']])
            ->assertJsonPath('import.provider', 'jamf');

        $this->assertSame(1, Import::count());
        $import = Import::first();
        $this->assertSame('succeeded', $import->status);

        // 7 devices in the bundled mock JSON, all assigned.
        $this->assertSame(7, Asset::count());
        $this->assertSame(6, Employee::count()); // Alex owns two devices.
    }

    public function test_post_imports_rejects_unknown_provider_with_422(): void
    {
        $this->postJson('/api/imports', ['provider' => 'nope'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['provider']);

        $this->assertSame(0, Import::count());
    }

    public function test_get_import_returns_current_state(): void
    {
        $import = Import::create([
            'provider' => 'jamf',
            'status' => 'queued',
        ]);

        $this->getJson("/api/imports/{$import->id}")
            ->assertOk()
            ->assertJsonPath('import.id', $import->id)
            ->assertJsonPath('import.status', 'queued');
    }
}
