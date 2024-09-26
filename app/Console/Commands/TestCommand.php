<?php

namespace App\Console\Commands;

use App\Enums\QueueEnum;
use App\Jobs\TestJob;
use App\Models\User;

class TestCommand extends AbstractChunkerCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-command {--queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct()
    {
        $query = User::query()->whereNotNull('email');

        $job = app(TestJob::class)
            ->setQuery($query->toSql())
            ->setBindings($query->getBindings())
            ->setModel(User::class)
            ->onQueue(QueueEnum::default->value)
            ->prepareMainQuery()
            ->setLogging(false);

        parent::__construct($job, 100);
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->handleCommand();
    }
}
