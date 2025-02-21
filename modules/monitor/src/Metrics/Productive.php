<?php

namespace Modules\Monitor\Metrics;

use Illuminate\Support\Collection;
use Modules\Monitor\Enums\MetricTypeEnum;

class Productive
{
    public const LAST_SEND_ID_CACHE_KEY = 'productive-metrics-last-send-id';
    public const METRICS_CACHE_KEY = 'productive-metrics';

    // verify otp metrics title
    public const AVERAGE_LOGIN_TO_VERIFICATION_TIME = 'average_login_to_verification_time';
    public const AVERAGE_OTP_SMS_RECEIVED_PER_USER = 'average_otp_sms_received_per_user';
    public const VERIFY_OTP_ROUTE_RATE = 'verify_otp_route_rate';
    public const USER_OTP_VERIFICATION_RATE = 'user_otp_verification_rate';

    public function getMetrics(): Collection
    {
        try {
            $result = $this->prepareResult();

            $cachedMetrics = cache()->get(self::METRICS_CACHE_KEY, $result);

            $updatedMetrics = $this->updateCollection(current: $cachedMetrics, new: $result);

            cache()->set(self::METRICS_CACHE_KEY, $updatedMetrics);

            return $updatedMetrics;

        } catch (\Exception $exception) {
            report($exception);
            return collect();
        }
    }

    /*
     * replace changed and new values from a new result to a cached result
     * **/
    private function updateCollection(Collection $current, Collection $new): Collection
    {
        return $new->mapWithKeys(function ($newValue, $key) use ($current) {
            if ($current->has($key)) {
                $currentValue = $current->get($key);
                // If both current and new values are arrays, recursively update them.
                if (is_array($currentValue) && is_array($newValue)) {
                    return [
                        $key => $this->updateCollection(collect($currentValue), collect($newValue))->toArray(),
                    ];
                }
                // If the new value is null, keep the current value
                return [$key => $newValue === null ? $currentValue : $newValue];
            }
            // If the current doesn't have the key, use the new value.
            return [$key => $newValue];
        })->merge($current->filter(fn($value, $key) => !$new->has($key)));
    }


    private function getResult(): Collection
    {
        // get types without payment type -> because payment type is calculated with tracking type
        $types = MetricTypeEnum::typesWithOutPayment();

        $query = $this->buildRawQuery($types);

        $results = collect(\DB::select($query));

        $maxId = $results->max('max_id');
        if ($maxId && config('metrics.last-send-id-enabled')) {
            cache()->put(self::LAST_SEND_ID_CACHE_KEY, $maxId);
        }

        return $results;
    }

    private function buildRawQuery(array $types): string
    {
        $paymentType = MetricTypeEnum::payment->value;

        $gateways = config('metrics.gateways');

        $lastSendId = cache(self::LAST_SEND_ID_CACHE_KEY);

        $duration = config('metrics.monitoring-period');

        $typesList = $this->extractWhereInValues($types);

        $lastSendIdCondition = $lastSendId ? "AND id > $lastSendId" : "AND created_at >= NOW() - INTERVAL $duration MINUTE";

        $gatewayTitles = $this->extractWhereInValues($gateways);

        $selectOtpVerification = $this->getSelectOtpVerification();

        $selectType = $this->getSelectTypes($gateways, $paymentType);

        $selectRate = $this->getSelectRate();

        // *** MAIN QUERY
        return "
            SELECT $selectType,
                MAX(id) as max_id,
                (SUM($selectRate) / COUNT(*)) * 100 AS rate,
                $selectOtpVerification
            FROM health_metric_logs
            WHERE (type IN ($typesList) or (type = $paymentType and meta in ($gatewayTitles) )) $lastSendIdCondition
            GROUP BY type_label, `type`
            ";
    }

    /**
     * @param int $duration
     * @param string $tableName
     * @return string
     */
    private function timeLimitedRecordsQuery(int $duration, string $tableName): string
    {
        return "AND $tableName.created_at >= NOW() - INTERVAL $duration SECOND";
    }

    /**
     * @return string
     */
    public function getSelectOtpVerification(): string
    {
        // login otp and verify metrics monitoring depends on period between login otp and verify
        $authMetricsDuration = config('auth.login-otp-period') + /*offset*/
            config('metrics.cache-ttl'); // 90+15=115 seconds
        $loginOtpType = MetricTypeEnum::login_otp->value;
        $verifyOtpType = MetricTypeEnum::verify_otp->value;
        $loginOtpValidStatuses = $this->extractWhereInValues(MetricTypeEnum::login_otp->validStatuses());
        $verifyOtpValidStatuses = $this->extractWhereInValues(MetricTypeEnum::verify_otp->validStatuses());
        $loginTableName = 'loginOtpMobile';
        $verifyTableName = 'verifyOtpMobile';
        // we only need to check the duration of login to verify with additional offset and not need to use last send id
        $timeLimitedLoginOtp = $this->timeLimitedRecordsQuery($authMetricsDuration, $loginTableName);
        $timeLimitedVerifyOtp = $this->timeLimitedRecordsQuery($authMetricsDuration, $verifyTableName);

        // aliases
        // description: (sum unique ips where verified truly and logged in truly before / sum unique ips where logged in truly) * 100
        $userOtpVerificationRate = self::USER_OTP_VERIFICATION_RATE;
        // description: average of tries to verify from true login requests
        $averageOtpSmsReceivedPerUser = self::AVERAGE_OTP_SMS_RECEIVED_PER_USER;
        // description: average of time taken to verify from true login requests in seconds
        // average of time diff between last successful login to last successful verification
        $averageLoginToVerificationTime = self::AVERAGE_LOGIN_TO_VERIFICATION_TIME;

        return "
            CASE WHEN type = $verifyOtpType THEN
               (SELECT COUNT(DISTINCT meta)
                FROM health_metric_logs $verifyTableName
                WHERE $verifyTableName.status_code IN ($verifyOtpValidStatuses)
                AND $verifyTableName.type = $verifyOtpType $timeLimitedVerifyOtp
                AND $verifyTableName.meta IN (
                    SELECT DISTINCT meta FROM health_metric_logs $loginTableName
                        WHERE $loginTableName.status_code IN ($loginOtpValidStatuses)
                        AND $loginTableName.type = $loginOtpType $timeLimitedLoginOtp
                    )
               ) / NULLIF(
                    (SELECT COUNT(DISTINCT meta) FROM health_metric_logs $loginTableName
                        WHERE $loginTableName.status_code IN ($loginOtpValidStatuses)
                        AND $loginTableName.type = $loginOtpType $timeLimitedLoginOtp
                    ), 0
                ) * 100
            ELSE NULL END AS $userOtpVerificationRate,
            CASE WHEN type = $verifyOtpType THEN
                (SELECT AVG(meta_count)
                    FROM
                    (SELECT COUNT(meta) as meta_count
                        FROM health_metric_logs $verifyTableName
                        WHERE $verifyTableName.status_code IN ($verifyOtpValidStatuses)
                        AND $verifyTableName.type = $verifyOtpType $timeLimitedVerifyOtp
                        AND $verifyTableName.meta IN (
                            SELECT DISTINCT meta FROM health_metric_logs $loginTableName
                                WHERE $loginTableName.status_code IN ($loginOtpValidStatuses)
                                AND $loginTableName.type = $loginOtpType $timeLimitedLoginOtp
                            )
                    GROUP BY meta) as subquery
                )
            ELSE NULL END AS $averageOtpSmsReceivedPerUser,
            CASE WHEN type = $verifyOtpType THEN
                (SELECT AVG(time_diff)
                    FROM (
                        SELECT $loginTableName.meta,
                        TIMESTAMPDIFF(SECOND, MAX($loginTableName.created_at), MAX($verifyTableName.created_at)) AS time_diff
                        FROM health_metric_logs AS $loginTableName
                        JOIN health_metric_logs AS $verifyTableName
                            ON $loginTableName.meta = $verifyTableName.meta
                        WHERE $loginTableName.type = $loginOtpType $timeLimitedLoginOtp
                        AND $loginTableName.status_code IN ($loginOtpValidStatuses)
                        AND $verifyTableName.type = $verifyOtpType $timeLimitedVerifyOtp
                        AND $verifyTableName.status_code IN ($verifyOtpValidStatuses)
                        AND $loginTableName.created_at < $verifyTableName.created_at
                        GROUP BY $loginTableName.meta
                    ) AS time_diffs
                )
            ELSE NULL END AS $averageLoginToVerificationTime";
    }

    /**
     * @param mixed $gateways
     * @param int $paymentType
     * @return string
     */
    private function getSelectTypes(mixed $gateways, int $paymentType): string
    {
        $selectType = "CASE ";
        foreach ($gateways as $pgw) {
            $selectType .= "WHEN type = $paymentType AND meta = '$pgw' THEN '$pgw' ";
        }
        $selectType .= "ELSE CAST(type AS CHAR) END AS type_label";
        return $selectType;
    }

    /**
     * @return string
     */
    private function getSelectRate(): string
    {
        $selectRate = "CASE ";
        foreach (MetricTypeEnum::cases() as $type) {
            $failedStatuses = $this->extractWhereInValues($type->failedStatuses());
            $selectRate .= "WHEN type = $type->value AND status_code NOT IN ($failedStatuses) THEN 1 ";
        }
        $selectRate .= "ELSE 0 END";
        return $selectRate;
    }

    private function extractWhereInValues($items): string
    {
        return implode(',', array_map(static fn($item) => "'$item'", $items));
    }

    /**
     * @return Collection
     */
    public function prepareResult(): Collection
    {
        return $this->getResult()->mapWithKeys(function ($item) {
            $typeLabel = (int)$item->type_label;
            $rate = round((float)$item->rate, 1) ?? 0;

            // Handle cases with no payment
            if (in_array($typeLabel, MetricTypeEnum::typesWithOutPayment(), true)) {
                $typeName = MetricTypeEnum::from($typeLabel)->name;

                // Handle OTP verification rate
                if ($typeLabel === MetricTypeEnum::verify_otp->value) {
                    return [
                        $typeName => [
                            self::VERIFY_OTP_ROUTE_RATE => $rate,
                            self::USER_OTP_VERIFICATION_RATE => round((float)$item->{self::USER_OTP_VERIFICATION_RATE}, 1),
                            self::AVERAGE_OTP_SMS_RECEIVED_PER_USER => round((float)$item->{self::AVERAGE_OTP_SMS_RECEIVED_PER_USER}, 1),
                            self::AVERAGE_LOGIN_TO_VERIFICATION_TIME => round((float)$item->{self::AVERAGE_LOGIN_TO_VERIFICATION_TIME}, 1),
                        ]
                    ];
                }

                return [$typeName => $rate];
            }

            return [$item->type_label => $rate];
        });
    }
}
