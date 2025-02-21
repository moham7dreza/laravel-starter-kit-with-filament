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
            'status_code' => $this->faker->randomElement([\HttpResponse::HTTP_OK, \HttpResponse::HTTP_CREATED, \HttpResponse::HTTP_FORBIDDEN]),
            'meta' => match ($type) {
                MetricTypeEnum::login_otp, MetricTypeEnum::verify_otp => fake()->randomElement($this->mobiles),
                default => null,
            },
        ];
    }

    public function success(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status_code' => \HttpResponse::HTTP_OK,
            ];
        });
    }

    public function timeout(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status_code' => \HttpResponse::HTTP_REQUEST_TIMEOUT,
            ];
        });
    }

    public function failed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status_code' => \HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        });
    }
}
