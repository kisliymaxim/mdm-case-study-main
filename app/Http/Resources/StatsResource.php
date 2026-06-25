<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StatsResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'counts' => $this->resource['counts'],
            'last_import' => $this->resource['last_import'] ?? null,
        ];
    }
}
