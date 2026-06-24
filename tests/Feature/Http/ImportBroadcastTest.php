<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Events\ImportUpdated;
use App\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class ImportBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_import_broadcasts_terminal_state(): void
    {
        Event::fake([ImportUpdated::class]);

        $this->postJson('/api/imports')->assertStatus(202);

        // Queue is sync in tests → handle() runs inline. Only the
        // terminal transition broadcasts; the running state is persisted
        // to DB but not emitted (counts don't change yet, and the UI
        // already shows "Importing…" for queued|running).
        Event::assertDispatched(
            ImportUpdated::class,
            fn (ImportUpdated $e) => $e->status === Import::STATUS_SUCCEEDED,
        );
        Event::assertNotDispatched(
            ImportUpdated::class,
            fn (ImportUpdated $e) => $e->status === Import::STATUS_RUNNING,
        );
    }

    public function test_broadcast_channel_is_import_id(): void
    {
        $import = Import::create(['provider' => 'jamf', 'status' => 'queued']);

        $event = new ImportUpdated($import);

        $channels = $event->broadcastOn();
        $this->assertCount(1, $channels);
        $this->assertSame("import.{$import->id}", $channels[0]->name);
    }
}
