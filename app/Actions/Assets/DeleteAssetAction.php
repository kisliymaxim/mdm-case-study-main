<?php

declare(strict_types=1);

namespace App\Actions\Assets;

use App\Models\Asset;
use App\Services\StatsService;

final readonly class DeleteAssetAction
{
    /**
     * @param StatsService $stats
     */
    public function __construct(private StatsService $stats)
    {
    }

    /**
     * @param Asset $asset
     * @return void
     */
    public function handle(Asset $asset): void
    {
        $asset->delete();
        $this->stats->invalidate();
    }
}
