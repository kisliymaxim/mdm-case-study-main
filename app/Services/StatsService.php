<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Asset;
use App\Models\Employee;
use App\Models\Import;
use Illuminate\Support\Facades\Cache;

final class StatsService
{
    private const int CACHE_TTL = 1440;
    private const string CACHE_KEY = 'stats:snapshot';

    /**
     * @return array<string, mixed>
     */
    public function snapshot(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => $this->build());
    }

    /**
     * @return void
     */
    public function invalidate(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    private function build(): array
    {
        $lastImport = Import::query()->latest('created_at')->first();

        return [
            'counts' => [
                'assets' => Asset::query()->count(),
                'employees' => Employee::query()->count(),
                'imports' => Import::query()->count(),
            ],
            'last_import' => $lastImport ? [
                'id' => $lastImport->id,
                'provider' => $lastImport->provider,
                'status' => $lastImport->status,
                'summary' => $lastImport->summary,
                'error' => $lastImport->error,
                'started_at' => $lastImport->started_at?->toIso8601String(),
                'finished_at' => $lastImport->finished_at?->toIso8601String(),
            ] : null,
        ];
    }
}
