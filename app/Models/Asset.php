<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $serial_code
 * @property int $employee_id
 * @property string $device_name
 * @property string $provider
 * @property array<string, mixed>|null $specs
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read Employee $employee
 */
class Asset extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'serial_code',
        'employee_id',
        'device_name',
        'provider',
        'specs',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'specs' => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
