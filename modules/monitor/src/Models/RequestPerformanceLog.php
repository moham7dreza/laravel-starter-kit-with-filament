<?php

namespace Modules\Monitor\Models;

use App\Enums\RequestTypeEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaracraftTech\LaravelDateScopes\DateScopes;

class RequestPerformanceLog extends Model
{
    use Prunable, DateScopes, SoftDeletes;

    protected $fillable = [
        'type',
        'duration',
        'query_duration',
        'uri',
        'domain',
        'path',
        'ip',
        'user_id',
    ];

    protected $casts = ['type' => RequestTypeEnum::class];

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::query()->where('created_at', '<=', now()->subDays(14));
    }

    //********************************************** scopes

    public function scopeHighestDuration($query, int $count = 1000): Builder
    {
        return $query->where('duration', '>=', $count);
    }

    public function scopeHighestQueryDuration($query, int $count = 1000): Builder
    {
        return $query->where('query_duration', '>=', $count);
    }

    public function scopeWeb($query)
    {
        return $query->where('type', RequestTypeEnum::web->value);
    }

    public function scopeApi($query)
    {
        return $query->where('type', RequestTypeEnum::api->value);
    }

    //********************************************** methods

    public function getRequestType(): string
    {
        return RequestTypeEnum::typesWithValues()[$this->type->value] ?? '-';
    }

    public function getColor(string $column_name): string
    {
        return match (true) {
            $this->$column_name > 1000 => 'danger',
            $this->$column_name > 500 && $this->$column_name < 1000 => 'warning',
            default => 'success',
        };
    }

    //********************************************** relations

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
