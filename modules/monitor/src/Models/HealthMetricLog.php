<?php

namespace Modules\Monitor\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Modules\Monitor\Enums\MetricTypeEnum;

class HealthMetricLog extends Model
{
    use HasFactory;
    use Prunable;

    protected $fillable = [
        'user_id',
        'created_at',
        'duration',
        'type',
        'status_code',
        'meta',
        'data',
    ];
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'type' => MetricTypeEnum::class,
            'data' => 'json'
        ];
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return $this->where('created_at', '<=', now()->subDay());
    }
}
