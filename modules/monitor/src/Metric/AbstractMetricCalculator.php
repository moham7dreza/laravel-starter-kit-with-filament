<?php

namespace Modules\Monitor\Metric;

use Cache;
use DB;
use HttpResponse;
use Illuminate\Support\Collection;
use Modules\Monitor\Enums\MetricTypeEnum;

abstract class AbstractMetricCalculator
{
    protected function getMetricsForTypes(): Collection
    {
        // get types without payment type -> because payment type is calculated with tracking type
        $types = MetricTypeEnum::typesWithOutPayment();
        $cacheKey = 'all_metrics_cache_key';
        $query = $this->buildRawQuery($types, $cacheKey);

        $results = collect(DB::select($query));

        $maxId = $results->max('max_id');
        $this->cacheLastSendId($cacheKey, $maxId);

        return $results;
    }

    private function buildRawQuery(array $types, $cacheKey): string
    {
        $typeList = implode(',', array_map(static fn($type) => "'$type'", $types));
        // *** add select for types from 1 to 5 and type 6 with various tracking types
        $selectType = "CASE ";
        $paymentType = MetricTypeEnum::payment->value;
        $trackingTypes = array_keys([]);
        foreach ($trackingTypes as $pgw) {
            $selectType .= "WHEN type = $paymentType AND tracking_type = $pgw THEN 'pgw-$pgw' ";
        }
        $selectType .= "ELSE CAST(type AS CHAR) END AS type_label";
        // ***
        $statuses = implode(',', [HttpResponse::HTTP_OK, HttpResponse::HTTP_CREATED]);
        $lastSendId = cache($cacheKey);
        $duration = config('metrics.monitoring-period');
        // Handle the case when $lastSendId is null
        $lastSendIdCondition = $lastSendId ? "AND id > $lastSendId" : "AND created_at >= NOW() - INTERVAL $duration MINUTE";
        $lastSendIdConditionOnLoginOtp = $this->getLastSendIdForTable($lastSendId, $duration, 'loginOtpMobile');
        $lastSendIdConditionOnVerifyOtp = $this->getLastSendIdForTable($lastSendId, $duration, 'verifyOtpMobile');

        $trackingTypes = implode(',', array_map(static fn($type) => "'$type'", $trackingTypes));

        $loginOtpType = MetricTypeEnum::login_otp->value;
        $verifyOtpType = MetricTypeEnum::verify_otp->value;

        return "
            SELECT $selectType,
               COUNT(*) as count,
                MAX(id) as max_id,
                 SUM(requested = 1) as requested_count,
                  SUM(`terminated` = 1) as terminated_count,
                    (SUM(CASE WHEN status_code IN ($statuses) THEN `terminated` = 1 ELSE 0 END) /
                        NULLIF(SUM(requested = 1), 0)) * 100 AS rate,
           CASE
           WHEN type = $verifyOtpType THEN
               (SELECT COUNT(*)
                FROM health_metric_logs verifyOtpMobile
                WHERE verifyOtpMobile.type = $verifyOtpType $lastSendIdConditionOnVerifyOtp
                AND verifyOtpMobile.meta IN (
                    SELECT meta FROM health_metric_logs loginOtpMobile
                        WHERE loginOtpMobile.type = $loginOtpType $lastSendIdConditionOnLoginOtp
                    )
               ) / NULLIF(
                    (SELECT COUNT(*) FROM health_metric_logs loginOtpMobile
                        WHERE loginOtpMobile.type = $loginOtpType $lastSendIdConditionOnLoginOtp
                    ), 0
                ) * 100
           ELSE
               NULL
       END AS otp_verification_rate
        FROM health_metric_logs
        WHERE (type IN ($typeList) or (type = $paymentType and tracking_type in ($trackingTypes) )) $lastSendIdCondition
        GROUP BY type_label, `type`
    ";
    }

    /**
     * @param mixed $lastSendId
     * @param mixed $duration
     * @param string $tableName
     * @return string
     */
    public function getLastSendIdForTable(mixed $lastSendId, mixed $duration, string $tableName): string
    {
        return $lastSendId ? "AND $tableName.id > $lastSendId" : "AND $tableName.created_at >= NOW() - INTERVAL $duration MINUTE";
    }

    private function cacheLastSendId(string $cacheKey, int|null $maxId): void
    {
        Cache::remember($cacheKey, config('metrics.cache-ttl'), fn() => $maxId);
    }

    private function getProvider()
    {
        return config('metrics.provider');
    }
}
