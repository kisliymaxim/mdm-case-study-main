<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Import;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

final class ImportUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;

    /**
     * @var string
     */
    public readonly string $importId;

    /**
     * @var string
     */
    public readonly string $status;

    /**
     * @var array<string, mixed>
     */
    public readonly array $payload;

    /**
     * @param Import $import
     */
    public function __construct(Import $import)
    {
        $this->importId = $import->id;
        $this->status = $import->status;
        $this->payload = [
            'id' => $import->id,
            'provider' => $import->provider,
            'status' => $import->status,
            'summary' => $import->summary,
            'error' => $import->error,
            'started_at' => $import->started_at?->toIso8601String(),
            'finished_at' => $import->finished_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel("import.{$this->importId}")];
    }

    /**
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'ImportUpdated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['import' => $this->payload];
    }
}
