<?php

namespace Modules\Monitor\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class CommandPerformanceLog extends Model
{
    use Prunable;

    public const UPDATED_AT = null;
    protected $guarded = [];

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(14));
    }

    public function scopeHighestQueryTime($query, int $count = 1000): Builder
    {
        return $query->where('query_time', '>=', $count);
    }
}
