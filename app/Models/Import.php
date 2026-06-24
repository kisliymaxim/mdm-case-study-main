<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $provider
 * @property string $status
 * @property array<string, mixed>|null $summary
 * @property string|null $error
 * @property CarbonInterface|null $started_at
 * @property CarbonInterface|null $finished_at
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
class Import extends Model
{
    use HasUlids;

    public const string STATUS_QUEUED = 'queued';
    public const string STATUS_RUNNING = 'running';
    public const string STATUS_SUCCEEDED = 'succeeded';
    public const string STATUS_FAILED = 'failed';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var string[]
     */
    protected $fillable = [
        'provider',
        'status',
        'summary',
        'error',
        'started_at',
        'finished_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'summary' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return in_array($this->status, [self::STATUS_SUCCEEDED, self::STATUS_FAILED], true);
    }
}
