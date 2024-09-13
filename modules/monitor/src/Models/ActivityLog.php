<?php

namespace Modules\Monitor\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_log';

    // methods

    public function description(): string
    {
        return Str::limit($this->description);
    }

    public function causerName(): string
    {
        return $this->causer_type::query()->findOrFail($this->causer_id)->name ?? 'ایجاد کننده ندارد.';
    }

    public function properties()
    {
        return json_decode($this->properties, true);
    }
}
