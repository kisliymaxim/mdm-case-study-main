<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Asset
 */
final class AssetResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'serial_code' => $this->serial_code,
            'device_name' => $this->device_name,
            'provider' => $this->provider,
            'specs' => $this->specs,
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee->id,
                'email' => $this->employee->email,
                'name' => $this->employee->name,
                'phone' => $this->employee->phone,
            ]),
        ];
    }
}
