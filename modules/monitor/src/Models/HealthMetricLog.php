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

    public const REQUESTED = 1;
    public const TERMINATED = 1;
    public const UPDATED_AT = null;
    protected $fillable = [
        'user_id',
        'created_at',
        'duration',
        'type',
        'tracking_type',
        'metricable_id',
        'metricable_type',
        'status_code',
        'terminated',
        'requested',
        'meta'
    ];
    protected $casts = [
        'type' => MetricTypeEnum::class,
    ];

    public function scopeRequested($query)
    {
        return $query->where('requested', self::REQUESTED);
    }

    public function scopeTerminated($query)
    {
        return $query->where('terminated', self::TERMINATED);
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return $this->where('created_at', '<=', now()->subDay());
    }
}
