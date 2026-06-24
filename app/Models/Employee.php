<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $email
 * @property string|null $name
 * @property string|null $phone
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read Collection<int, Asset> $assets
 * @property-read int|null $assets_count
 */
class Employee extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'email',
        'name',
        'phone',
    ];

    /**
     * @return HasMany
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
