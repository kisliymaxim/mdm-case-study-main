<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Imports\TriggerMdmImportAction;
use App\Http\Requests\StoreImportRequest;
use App\Http\Resources\ImportResource;
use App\Models\Import;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

final class ImportController extends Controller
{
    /**
     * @param StoreImportRequest $request
     * @param TriggerMdmImportAction $action
     * @return JsonResponse
     */
    public function store(StoreImportRequest $request, TriggerMdmImportAction $action): JsonResponse
    {
        $import = $action->handle(providerKey: $request->providerKey());

        return ImportResource::make($import)
            ->response()
            ->setStatusCode(HttpStatus::HTTP_ACCEPTED);
    }

    /**
     * @param Import $import
     * @return JsonResource
     */
    public function show(Import $import): JsonResource
    {
        return ImportResource::make($import);
    }
}
