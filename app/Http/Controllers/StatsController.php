<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\StatsResource;
use App\Services\StatsService;
use Illuminate\Http\Resources\Json\JsonResource;

final class StatsController extends Controller
{
    /**
     * @param StatsService $service
     * @return JsonResource
     */
    public function index(StatsService $service): JsonResource
    {
        return StatsResource::make($service->snapshot());
    }
}
