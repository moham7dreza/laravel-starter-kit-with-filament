<?php

namespace Modules\Monitor\Services;

use Artisan;
use DB;
use Elastic\Elasticsearch\Client;
use Exception;
use Illuminate\Support\Facades\Redis;
use Minio\MinioClient;
use Modules\Monitor\Enums\MetricTypeEnum;
use Modules\Monitor\Metric\Productive;
use MongoDB\Client as MongoClient;
use Sentry\State\Hub;

class HealthCheckService
{
    private static int $noResultSymbol = -1;

    public function checkWebSocketServer(): bool
    {
        $url = 'ws://your-websocket-server-url';
        $client = new \WebSocket\Client($url);
        try {
            $client->send('ping');
            return true;
        } catch (Exception $e) {
            return false;
        } finally {
            $client->close();
        }
    }

    /**
     * @param mixed $registry
     * @return void
     */
    public function registerInternalServiceMetrics(mixed $registry): void
    {

        $gauge = $registry->getOrRegisterGauge('', 'internal_services_mysql', 'MySQL status', []);
        $gauge->set($this->checkMySQL() ? 1 : 0);
        $gauge = $registry->getOrRegisterGauge('', 'internal_services_redis', 'Redis status', []);
        $gauge->set($this->checkRedis() ? 1 : 0);
        $gauge = $registry->getOrRegisterGauge('', 'internal_services_elasticsearch', 'Elasticsearch status', []);
        $gauge->set($this->checkElasticsearch() ? 1 : 0);
        $gauge = $registry->getOrRegisterGauge('', 'internal_services_mongodb', 'MongoDB status', []);
        $gauge->set($this->checkMongoDB() ? 1 : 0);
        $gauge = $registry->getOrRegisterGauge('', 'internal_services_minio', 'Minio status', []);
        $gauge->set($this->checkMinio() ? 1 : 0);
        $gauge = $registry->getOrRegisterGauge('', 'internal_services_horizon', 'Horizon status', []);
        $gauge->set($this->checkHorizon() ? 1 : 0);
//        $gauge = $registry->getOrRegisterGauge('', 'internal_services_websocket', 'WebSocket server status', []);
//        $gauge->set($this->checkWebSocketServer() ? 1 : 0);
        $gauge = $registry->getOrRegisterGauge('', 'internal_services_mail', 'Mail server status', []);
        $gauge->set($this->checkMailServer() ? 1 : 0);
        $gauge = $registry->getOrRegisterGauge('', 'internal_services_scheduler', 'Scheduler status', []);
        $gauge->set($this->checkScheduler() ? 1 : 0);
        $gauge = $registry->getOrRegisterGauge('', 'internal_services_sentry', 'Sentry status', []);
        $gauge->set($this->checkSentry() ? 1 : 0);
        $gauge = $registry->getOrRegisterGauge('', 'internal_services_log_server', 'Log server status', []);
        $gauge->set($this->checkLogServer() ? 1 : 0);
    }

    public function checkMySQL(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function checkRedis(): bool
    {
        try {
            Redis::connection()->ping();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function checkElasticsearch(): bool
    {
        try {
            $health = app(Client::class)->cluster()->health();
            return $health['status'] === 'green' || $health['status'] === 'yellow';
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            return false;
        }
    }

    public function checkMinio(): bool
    {
        try {
//            \Storage::cloud()->getConfig();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function checkHorizon(): bool
    {
        try {
            Artisan::call('horizon:status');
            $output = Artisan::output();
            return str_contains($output, 'running');
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            return false;
        }
    }

    public function checkSentry(): bool
    {
        try {
            $hub = app(Hub::class);
            $hub->captureMessage('Health check');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function checkLogServer(): bool
    {
        try {
            $message = 'Log server check message';
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param mixed $registry
     * @return void
     */
    public function registerProductiveMetrics(mixed $registry): void
    {
//        $cachedResult = cache('productive_metrics');

        try {
            $result = app(Productive::class)->getMetrics();
        } catch (Exception $e) {
            report($e->getMessage());
            return;
        }

        // *** Productive routes metrics ***
        foreach (config('metrics.export.productive') as $metricType => $gaugeName) {
            $gauge = $registry->getOrRegisterGauge('', $gaugeName, ucwords(str_replace('_', ' ', $metricType)) . ' status', []);

            if ($metricType === MetricTypeEnum::verify_otp->name) {
                $gauge->set(round($result[$metricType]['total_rate'] ?? self::$noResultSymbol, 2));
                $gauge = $registry->getOrRegisterGauge('', $gaugeName . '_from_login_rate', ucwords(str_replace('_', ' ', $metricType)) . ' status', []);
                $gauge->set(round($result[$metricType]['otp_verification_rate'] ?? self::$noResultSymbol, 2));
            } else {
                $gauge->set(round($result[$metricType] ?? self::$noResultSymbol, 2));
            }
        }

        // *** payment gateways metrics ***
        if (request('gateways') ?? config('metrics.gateways_enabled')) {
            foreach ([] as $pwg => $title) {
                if (in_array($pwg, config('metrics.gateways'), true)) {
                    $title = "";
                    $gaugeName = config('metrics.export.payment_gateway') . "_{$title}_gateway";
                    $gauge = $registry->getOrRegisterGauge('', $gaugeName, "gateway $title status", []);
                    $gauge->set(round($result["pgw-$pwg"] ?? self::$noResultSymbol, 2));
                }
            }
        }
    }
}
