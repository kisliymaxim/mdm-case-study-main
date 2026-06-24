<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\ImportUpdated;
use App\Mdm\MdmImportOrchestrator;
use App\Mdm\MdmProviderRegistry;
use App\Models\Import;
use App\Services\StatsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class RunMdmImportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int
     */
    public int $tries = 1;

    /**
     * @var int
     */
    public int $timeout = 120;

    /**
     * @param string $importId
     */
    public function __construct(public readonly string $importId)
    {
    }

    /**
     * @param MdmProviderRegistry $registry
     * @param MdmImportOrchestrator $orchestrator
     * @param StatsService $stats
     * @return void
     * @throws Throwable
     */
    public function handle(MdmProviderRegistry $registry, MdmImportOrchestrator $orchestrator, StatsService $stats): void
    {
        $import = Import::query()->findOrFail($this->importId);

        $import->update([
            'status' => Import::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        try {
            $provider = $registry->get($import->provider);
            $summary = $orchestrator->run($provider);

            $import->update([
                'status' => Import::STATUS_SUCCEEDED,
                'summary' => $summary->toArray(),
                'finished_at' => now(),
            ]);

            $this->emit($import, $stats);
        } catch (Throwable $e) {
            $import->update([
                'status' => Import::STATUS_FAILED,
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            $this->emit($import, $stats);
            throw $e;
        }
    }

    /**
     * @param Throwable $e
     * @return void
     */
    public function failed(Throwable $e): void
    {
        $import = Import::query()->find($this->importId);

        if ($import && !$import->isFinished()) {
            $import->update([
                'status' => Import::STATUS_FAILED,
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            $this->emit($import, app(StatsService::class));
        }
    }

    /**
     * @param Import $import
     * @param StatsService $stats
     * @return void
     */
    private function emit(Import $import, StatsService $stats): void
    {
        ImportUpdated::dispatch($import);
        $stats->invalidate();
    }
}
