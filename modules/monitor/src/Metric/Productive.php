<?php

namespace Modules\Monitor\Metric;

use Cache;
use Modules\Monitor\Enums\MetricTypeEnum;

class Productive extends AbstractMetricCalculator
{
    public function getMetrics()
    {
        return Cache::remember('productive_metrics', config('metrics.cache-ttl'), function () {
            // cache last updated time
//            cache()->remember('productive_metrics_last_update',
//                config('metrics.cache-ttl'),
//                fn() => now());
            return $this->getMetricsForTypes()->mapWithKeys(function ($item) {
                if (in_array((int)$item->type_label, MetricTypeEnum::typesWithOutPayment(), true)) {
                    if ((int)$item->type_label === MetricTypeEnum::verify_otp->value) {
                        return [
                            MetricTypeEnum::from($item->type_label)->name => [
                                'total_rate' => $item->rate,
                                'otp_verification_rate' => $item->otp_verification_rate
                            ]
                        ];
                    }
                    return [MetricTypeEnum::from($item->type_label)->name => $item->rate ?? 0];
                }

                return [$item->type_label => $item->rate ?? 0];
            })->toArray();
        });
    }

}
