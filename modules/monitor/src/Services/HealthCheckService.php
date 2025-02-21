<?php

namespace Modules\Monitor\Services;


use Illuminate\Support\Facades\Redis;
use Modules\Monitor\Enums\MetricTypeEnum;
use Modules\Monitor\Enums\SmsMessageTypeEnum;
use Modules\Monitor\Enums\SmsProviderEnum;
use Modules\Monitor\Metric\Productive;
use Modules\Monitor\Metrics\Productive;
use Modules\Monitor\Metrics\SMS;
use Modules\Monitor\Models\SmsLog;
use MongoDB\Client as MongoClient;
use Prometheus\CollectorRegistry;

class HealthCheckService
{
    private static int $noResultSymbol = -1;

    public function checkMySQL(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkRedis(): bool
    {
        try {
            Redis::connection()->ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkMongoDB(): bool
    {
        try {
            $client = (new MongoClient(config('database.connections.mongodb.dsn'), [
                "socketTimeoutMS" => 1000 * 3600 * 2 //2hour
            ]));
            $client->listDatabases();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkHorizon(): bool
    {
        try {
            \Artisan::call('horizon:status');
            $output = \Artisan::output();
            return str_contains($output, 'running');
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkMailServer(): bool
    {
        try {
            $default = config('mail.default', 'smtp');

            $startTime = microtime(true);

            $connection = @fsockopen(
                hostname: config("mail.mailers.$default.host"),
                port: config("mail.mailers.$default.port"),
                error_code: $errno,
                error_message: $errstr,
                timeout: 10,
            );

            $endTime = microtime(true);

            $responseTime = round(($endTime - $startTime) * 1000);
            if ($responseTime > 5000) {
                return false;
            }

            return $connection !== false;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    public function checkScheduler(): bool
    {
        try {
            $tasks = [
                'test' => cache('schedule-last-execution')
            ];

            $healthy = true;

            foreach ($tasks as $task => $lastExecution) {
                if (!$lastExecution || $lastExecution->lt(now()->subMinutes(2))) {
                    $healthy = false;
                    break;
                }
            }

            return $healthy;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param mixed $registry
     * @return void
     */
    public function registerInternalServiceMetrics(mixed $registry): void
    {
        $prefix = 'internal_services';
        $gauge = $registry->getOrRegisterGauge('', "{$prefix}_mysql", 'MySQL status', []);
        $gauge->set($this->checkMySQL() ? 1 : 0);
        $gauge = $registry->getOrRegisterGauge('', "{$prefix}_redis", 'Redis status', []);
        $gauge->set($this->checkRedis() ? 1 : 0);
        $gauge = $registry->getOrRegisterGauge('', "{$prefix}_mongodb", 'MongoDB status', []);
        $gauge->set($this->checkMongoDB() ? 1 : 0);
        $gauge = $registry->getOrRegisterGauge('', "{$prefix}_horizon", 'Horizon status', []);
        $gauge->set($this->checkHorizon() ? 1 : 0);
        $gauge = $registry->getOrRegisterGauge('', "{$prefix}_mail", 'Mail server status', []);
        $gauge->set($this->checkMailServer() ? 1 : 0);
        $gauge = $registry->getOrRegisterGauge('', "{$prefix}_scheduler", 'Scheduler status', []);
        $gauge->set($this->checkScheduler() ? 1 : 0);
    }

    /**
     * @param mixed $registry
     * @return void
     */
    public function registerProductiveMetrics(CollectorRegistry $registry): void
    {
        $result = app(Productive::class)->getMetrics();

        // *** Productive routes metrics ***
        foreach (config('metrics.export.productive') as $metricType => $gaugeName) {

            $metricType = MetricTypeEnum::from($metricType);
            $metricName = $metricType->name;

            $helperText = "Percentage of successful requests for ";
            $helperText .= ucwords(str_replace('_', ' ', $metricName));
            $helperText .= ' api route, excluding error status codes ';
            $helperText .= implode(', ', $metricType->failedStatuses());

            $gauge = $registry->getOrRegisterGauge('', $gaugeName, $helperText, []);

            if ($metricName === MetricTypeEnum::verify_otp->name) {

                $duration = config('metrics.cache-ttl');
                $prefix = "api_endpoints_internal_";

                $gauge->set($result[$metricName][Productive::VERIFY_OTP_ROUTE_RATE] ?? self::$noResultSymbol);

                // USER_OTP_VERIFICATION_RATE
                $helperText = "Rate of successful OTP verifications as a percentage of OTP login requests in the past $duration seconds";
                $gauge = $registry->getOrRegisterGauge('', $prefix . Productive::USER_OTP_VERIFICATION_RATE, $helperText, []);
                $gauge->set($result[$metricName][Productive::USER_OTP_VERIFICATION_RATE] ?? self::$noResultSymbol);

                // AVERAGE_OTP_SMS_RECEIVED_PER_USER
                $authMetricsDuration = config('auth.login-otp-period') + /*offset*/
                    config('metrics.cache-ttl');
                $helperText = "Average count of OTP verification SMS messages received by each user within $authMetricsDuration seconds";
                $gauge = $registry->getOrRegisterGauge('', $prefix . Productive::AVERAGE_OTP_SMS_RECEIVED_PER_USER, $helperText, []);
                $gauge->set($result[$metricName][Productive::AVERAGE_OTP_SMS_RECEIVED_PER_USER] ?? self::$noResultSymbol);

                // AVERAGE_LOGIN_TO_VERIFICATION_TIME
                $helperText = "Average time in seconds between OTP login request and verification completion for each user within $authMetricsDuration seconds";
                $gauge = $registry->getOrRegisterGauge('', $prefix . Productive::AVERAGE_LOGIN_TO_VERIFICATION_TIME, $helperText, []);
                $gauge->set($result[$metricName][Productive::AVERAGE_LOGIN_TO_VERIFICATION_TIME] ?? self::$noResultSymbol);
            } else {
                $gauge->set($result[$metricName] ?? self::$noResultSymbol);
            }
        }

        // *** payment gateways metrics ***
        if (request('gateways') ?? config('metrics.payment-enabled')) {
            foreach (config('metrics.gateways') as $gateway) {

                $gaugeName = config('metrics.export.payment_gateway') . "_{$gateway}_gateway";
                $gauge = $registry->getOrRegisterGauge('', $gaugeName, "gateway $gateway status", []);
                $gauge->set($result[$gateway] ?? self::$noResultSymbol);
            }
        }
    }

    /**
     * @param mixed $registry
     * @return void
     */
    public function registerSmsMetrics(mixed $registry): void
    {
        $metrics = app(SMS::class)->getMetrics();
        foreach (SmsProviderEnum::values() as $provider) {

            $smsProvider = $metrics->firstWhere('_id', $provider);

            $sentRate = $smsProvider->sent_rate ?? self::$noResultSymbol;
            $deliveryRate = $smsProvider->delivery_rate ?? self::$noResultSymbol;
            $averageDeliveryTime = $smsProvider->avg_delivery_time ?? self::$noResultSymbol;

            $gauge = $registry->getOrRegisterGauge('', config('metrics.export.sms') . "_{$provider}_sent_rate", "{$provider} sent rate", []);
            $gauge->set($sentRate);

            $gauge = $registry->getOrRegisterGauge('', config('metrics.export.sms') . "_{$provider}_delivery_rate", "{$provider} delivery rate", []);
            $gauge->set($deliveryRate);

            $gauge = $registry->getOrRegisterGauge('', config('metrics.export.sms') . "_{$provider}_average_delivery_time", "{$provider} average delivery time in seconds", []);
            $gauge->set($averageDeliveryTime);

            $this->setGaugeForMessageType($smsProvider, $registry, $provider, SmsMessageTypeEnum::LOGIN_OTP);
        }
    }

    public function setGaugeForMessageType(SMSLog|null $SMSLog, mixed $registry, mixed $provider, SmsMessageTypeEnum $messageType): void
    {
        $messageType = $messageType->value;
        $messageTypeText = ucwords(str_replace('_', ' ', $messageType));

        $sentRateForLoginOtpParam = "sent_rate_for_{$messageType}";
        $deliveryRateForLoginOtpParam = "delivery_rate_for_{$messageType}";

        $sentRateForLoginOtp = $SMSLog?->$sentRateForLoginOtpParam ?? self::$noResultSymbol;
        $deliveryRateForLoginOtp = $SMSLog?->$deliveryRateForLoginOtpParam ?? self::$noResultSymbol;

        $gauge = $registry->getOrRegisterGauge('', config('metrics.export.sms') . "_{$provider}_sent_rate_for_{$messageType}", "{$provider} sent rate for $messageTypeText", []);
        $gauge->set($sentRateForLoginOtp);

        $gauge = $registry->getOrRegisterGauge('', config('metrics.export.sms') . "_{$provider}_delivery_rate_for_{$messageType}", "{$provider} delivery rate for $messageTypeText", []);
        $gauge->set($deliveryRateForLoginOtp);
    }
}
