<?php

namespace Modules\Monitor\Providers;

use Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks;
use Spatie\Health\Facades\Health;

class MonitorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->getChecks();
    }

    private function getChecks(): void
    {
        Health::checks([
            Checks\OptimizedAppCheck::new(),
            Checks\DebugModeCheck::new(),
            Checks\EnvironmentCheck::new(),
            Checks\UsedDiskSpaceCheck::new(),
            Checks\DatabaseCheck::new(),
            Checks\CacheCheck::new(),
            Checks\DatabaseSizeCheck::new(),
            Checks\HorizonCheck::new(),
            Checks\QueueCheck::new(),
            Checks\ScheduleCheck::new(),
            Checks\PingCheck::new()->url('https://toprate.ir'),
            Checks\RedisCheck::new(),
            Checks\RedisMemoryUsageCheck::new(),
            Checks\BackupsCheck::new()->locatedAt('/path/to/backups/*.zip'),
        ]);
    }

    /**
     * Register the Monitoring gate.
     *
     * This gate determines who can access Monitoring pages in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewHealth', function ($user) {
            return isEnvLocal() || in_array($user->id, config('debug.backends', []), true);
        });
    }
}
