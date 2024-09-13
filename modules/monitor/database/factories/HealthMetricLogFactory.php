<?php

namespace Modules\Monitor\Database\Factories;

use App\Models\User;
use HttpResponse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Monitor\Enums\MetricTypeEnum;
use Modules\Monitor\Models\HealthMetricLog;

class HealthMetricLogFactory extends Factory
{
    protected $model = HealthMetricLog::class;

    private array $mobiles = [
        '09121234567',
        '09121234568',
    ];

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'duration' => $this->faker->numberBetween(100, 1000),
            'type' => $type = MetricTypeEnum::random(),
            'tracking_type' => $this->faker->randomElement([]),
            //            'metricable_id' => Payment::factory(),
            //            'metricable_type' => Payment::class,
            'status_code' => $this->faker->randomElement([HttpResponse::HTTP_OK, HttpResponse::HTTP_CREATED, HttpResponse::HTTP_FORBIDDEN]),
            'requested' => true,
            'terminated' => $this->faker->boolean,
            'meta' => in_array($type, [MetricTypeEnum::login_otp, MetricTypeEnum::verify_otp], true)
                ? fake()->randomElement($this->mobiles) : null,
        ];
    }
}
