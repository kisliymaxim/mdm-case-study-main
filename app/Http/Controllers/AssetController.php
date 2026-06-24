<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Assets\DeleteAssetAction;
use App\Http\Resources\AssetResource;
use App\Http\Resources\AssetSummaryResource;
use App\Models\Asset;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

final class AssetController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $assets = Asset::query()
            ->with('employee:id,email,name')
            ->orderBy('device_name')
            ->get(['id', 'serial_code', 'device_name', 'provider', 'employee_id']);

        return AssetSummaryResource::collection($assets);
    }

    /**
     * @param Asset $asset
     * @return JsonResource
     */
    public function show(Asset $asset): JsonResource
    {
        $asset->load('employee:id,email,name,phone');

        return AssetResource::make($asset);
    }

    /**
     * @param Asset $asset
     * @param DeleteAssetAction $action
     * @return Response
     */
    public function destroy(Asset $asset, DeleteAssetAction $action): Response
    {
        $action->handle($asset);

        return response()->noContent();
    }
}
