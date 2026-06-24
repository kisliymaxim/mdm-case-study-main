<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Imports\TriggerMdmImportAction;
use App\Jobs\RunMdmImportJob;
use App\Mdm\MdmProviderRegistry;
use App\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use InvalidArgumentException;
use Tests\Support\StubMdmProvider;
use Tests\TestCase;

final class TriggerMdmImportActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('mdm.providers.stub', StubMdmProvider::class);
    }

    public function test_creates_queued_import_and_dispatches_job(): void
    {
        Queue::fake();

        $action = new TriggerMdmImportAction($this->freshRegistry('stub'));

        $import = $action->handle();

        $this->assertInstanceOf(Import::class, $import);
        $this->assertSame('stub', $import->provider);
        $this->assertSame(Import::STATUS_QUEUED, $import->status);
        $this->assertDatabaseHas('imports', ['id' => $import->id, 'status' => 'queued']);

        Queue::assertPushed(
            RunMdmImportJob::class,
            fn (RunMdmImportJob $job) => $job->importId === $import->id,
        );
    }

    public function test_passes_provider_key_through_to_registry(): void
    {
        Queue::fake();

        Config::set('mdm.providers.other', StubMdmProvider::class);
        $this->app->bind(StubMdmProvider::class, fn () => new StubMdmProvider('other'));

        $action = new TriggerMdmImportAction($this->freshRegistry('stub'));

        $import = $action->handle('other');

        $this->assertSame('other', $import->provider);
    }

    public function test_unknown_provider_bubbles_up(): void
    {
        Queue::fake();

        $action = new TriggerMdmImportAction($this->freshRegistry('stub'));

        $this->expectException(InvalidArgumentException::class);

        $action->handle('nope');
    }

    private function freshRegistry(string $default): MdmProviderRegistry
    {
        return new MdmProviderRegistry(
            $this->app,
            (array) config('mdm.providers'),
            $default,
        );
    }
}
