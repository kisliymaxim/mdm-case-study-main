<?php

declare(strict_types=1);

namespace App\Actions\Imports;

use App\Jobs\RunMdmImportJob;
use App\Mdm\MdmProviderRegistry;
use App\Models\Import;

final readonly class TriggerMdmImportAction
{
    /**
     * @param MdmProviderRegistry $registry
     */
    public function __construct(private MdmProviderRegistry $registry) {}

    /**
     * @param string|null $providerKey
     * @return Import
     */
    public function handle(?string $providerKey = null): Import
    {
        $provider = $this->registry->get($providerKey);

        $import = Import::query()->create([
            'provider' => $provider->name(),
            'status' => Import::STATUS_QUEUED,
        ]);

        RunMdmImportJob::dispatch($import->id);

        return $import;
    }
}
