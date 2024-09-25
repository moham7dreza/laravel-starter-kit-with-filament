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
        $job = app(TestJob::class)->setQuery(User::query())->onQueue(QueueEnum::default->value);

        parent::__construct($job, 100, true);
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->handleCommand();
    }
}
