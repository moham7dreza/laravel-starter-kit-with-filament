<?php

namespace Modules\Monitor\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Monitor\Enums\MetricTypeEnum;
use Modules\Monitor\Models\HealthMetricLog;

class HealthMetricLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (MetricTypeEnum::values() as $metric) {
            HealthMetricLog::factory()->create(['type' => $metric]);
        }
    }
}
