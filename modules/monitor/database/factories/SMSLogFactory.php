<?php

namespace Modules\Monitor\Database\Factories;

use App\Helpers\Date\TimeUtility;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Monitor\Enums\SmsProviderEnum;
use Modules\Monitor\Enums\SmsStatusEnum;
use Modules\Monitor\Enums\SmsTypeEnum;
use Modules\Monitor\Models\SmsLog;

class SMSLogFactory extends Factory
{
    protected $model = SmsLog::class;

    public function definition(): array
    {
        return [
            'connector' => SmsProviderEnum::random(),
            'delivered_at' => TimeUtility::convertCarbonToMongoUTCDateTime(now()->addSeconds(30)),
            'sent_at' => TimeUtility::getCurrentMongoDateTime(),
            'message' => $this->faker->realText,
            'message_id' => $this->faker->numberBetween(10000, 99999),
            'sender_number' => $this->faker->numberBetween(10000, 99999),
            'status' => SmsStatusEnum::random(),
            'to' => $this->faker->numberBetween(10000, 99999),
            'type' => SmsTypeEnum::random(),
            'message_type' => null,
        ];
    }

    public function failed($provider): static
    {
        return $this->state(['connector' => $provider, 'type' => SmsTypeEnum::SEND->value, 'status' => SmsStatusEnum::FAILED->value, 'delivered_at' => null]);
    }

    public function sent($provider): static
    {
        return $this->state(['connector' => $provider, 'type' => SmsTypeEnum::SEND->value, 'status' => SmsStatusEnum::SENT->value, 'delivered_at' => null]);
    }

    public function delivered($provider, $time): static
    {
        return $this->state(['connector' => $provider, 'type' => SmsTypeEnum::SEND->value, 'status' => SmsStatusEnum::DELIVERED->value, 'delivered_at' => TimeUtility::convertCarbonToMongoUTCDateTime($time)]);
    }

    public function received($provider): static
    {
        return $this->state(['connector' => $provider, 'type' => SmsTypeEnum::RECEIVE->value, 'status' => null, 'delivered_at' => null]);
    }

    public function messageType($type): static
    {
        return $this->state(['message_type' => $type]);
    }
}
