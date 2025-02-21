<?php

namespace Modules\Monitor\Metrics;


use App\Helpers\Date\TimeUtility;
use Illuminate\Support\Collection;
use Modules\Monitor\Enums\SmsMessageTypeEnum;
use Modules\Monitor\Enums\SmsStatusEnum;
use Modules\Monitor\Enums\SmsTypeEnum;
use Modules\Monitor\Models\SmsLog;
use MongoDB\BSON\ObjectId;

class SMS
{
    public const LAST_SEND_ID_CACHE_KEY = 'sms-metrics-last-send-id';

    public function getMetrics(): Collection
    {
        try {
            /* @var $results Collection */
            $results = SMSLog::query()->raw(function ($collection) {
                return $collection->aggregate(self::mongoPipeline());
            });

            $maxId = (string)$results->max('max_id');

            if ($maxId && config('metrics.sms-last-send-id-enabled')) {
                cache()->put(self::LAST_SEND_ID_CACHE_KEY, $maxId);
            }

            return $results;

        } catch (\Exception $e) {
            report($e);
            return collect();
        }
    }

    public static function mongoPipeline(): array
    {
        $pipeline = [
            [
                '$match' => [
                    'type' => SmsTypeEnum::SEND->value,
                ],
            ],
            [
                '$group' => [
                    '_id' => '$connector',
                    'max_id' => ['$max' => '$_id'],
                    'total' => ['$sum' => 1],
                    'total_sent' => [
                        '$sum' => [
                            '$cond' => [['$in' => ['$status', [SmsStatusEnum::DELIVERED->value, SmsStatusEnum::SENT->value]]], 1, 0],
                        ],
                    ],
                    'total_delivered' => [
                        '$sum' => [
                            '$cond' => [['$eq' => ['$status', SmsStatusEnum::DELIVERED->value]], 1, 0],
                        ],
                    ],
                    'total_delivery_time' => [
                        '$avg' => ['$divide' => [['$subtract' => ['$delivered_at', '$sent_at']], 1000]], // seconds
                    ],
                ],
            ],
            [
                '$project' => [
                    '_id' => 1,
                    'max_id' => 1,
                    'avg_delivery_time' => [
                        '$round' => ['$total_delivery_time', 1]
                    ],
                    'sent_rate' => [
                        '$round' => [['$multiply' => [['$divide' => ['$total_sent', '$total']], 100]], 1]
                    ],
                    'delivery_rate' => [
                        '$cond' => [
                            ['$gt' => ['$total_sent', 0]],
                            ['$round' => [['$multiply' => [['$divide' => ['$total_delivered', '$total_sent']], 100]], 1]],
                            null,
                        ]
                    ],
                ],
            ],
        ];
        $maxId = cache(self::LAST_SEND_ID_CACHE_KEY);
        if ($maxId) {
            $pipeline[0]['$match']['_id'] = [
                '$gt' => new ObjectId($maxId),
            ];
        } else {
            $pipeline[0]['$match']['_id'] = [
                '$gt' => TimeUtility::convertCarbonToMongoObjectId(now()->subMinutes(config('metrics.sms-monitoring-period'))),
            ];
        }

        self::addMessageTypeConditionToMongoPipeline($pipeline, SmsMessageTypeEnum::LOGIN_OTP);

        return $pipeline;
    }

    private static function addMessageTypeConditionToMongoPipeline(array &$pipeline, SmsMessageTypeEnum $messageType): void
    {
        $messageType = $messageType->value;
        $pipeline[1]['$group']["total_send_for_{$messageType}"] = [
            '$sum' => [
                '$cond' => [
                    ['$and' => [
                        ['$ifNull' => ['$message_type', false]],
                        ['$eq' => ['$message_type', $messageType]]
                    ]],
                    1,
                    0,
                ],
            ],
        ];
        $pipeline[1]['$group']["total_sent_for_{$messageType}"] = [
            '$sum' => [
                '$cond' => [
                    ['$and' => [
                        ['$ifNull' => ['$message_type', false]],
                        ['$eq' => ['$message_type', $messageType]],
                        ['$in' => ['$status', [SmsStatusEnum::DELIVERED->value, SmsStatusEnum::SENT->value]]]
                    ]],
                    1,
                    0,
                ],
            ],
        ];
        $pipeline[1]['$group']["total_delivered_for_{$messageType}"] = [
            '$sum' => [
                '$cond' => [
                    ['$and' => [
                        ['$ifNull' => ['$message_type', false]],
                        ['$eq' => ['$message_type', $messageType]],
                        ['$eq' => ['$status', SmsStatusEnum::DELIVERED->value]],
                    ]],
                    1,
                    0,
                ],
            ],
        ];
        $pipeline[2]['$project']["sent_rate_for_{$messageType}"] = [
            '$cond' => [
                ['$gt' => ['$' . "total_send_for_{$messageType}", 0]],
                ['$round' => [['$multiply' => [['$divide' => ['$' . "total_sent_for_{$messageType}", '$' . "total_send_for_{$messageType}"]], 100]], 1]],
                null,
            ],
        ];
        $pipeline[2]['$project']["delivery_rate_for_{$messageType}"] = [
            '$cond' => [
                ['$gt' => ['$' . "total_sent_for_{$messageType}", 0]],
                ['$round' => [['$multiply' => [['$divide' => ['$' . "total_delivered_for_{$messageType}", '$' . "total_sent_for_{$messageType}"]], 100]], 1]],
                null,
            ],
        ];
    }
}
