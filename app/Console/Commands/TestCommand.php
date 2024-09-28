<?php

namespace App\Console\Commands;

use App\DataContracts\JobChunkerDTO;
use App\Enums\QueueEnum;
use App\Jobs\TestJob;
use App\Models\User;
use App\Traits\JobChunkerTrait;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    use JobChunkerTrait;

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

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = User::query()->whereNotNull('email');

        $handler = function ($item) {
            sleep(1);
        };

        $DTO = new JobChunkerDTO(
            job: TestJob::class,
            queue: QueueEnum::default->value,
            logging: true,
            batchSize: 100,
            shouldQueue: $this->option('queue') ?? false,
            hydrateResult: false,
            query: $query,
            outputStyle: $this->output,
            signature: $this->signature,
            itemHandler: $handler,
        );

        return $this->chunkQueryToJobs($DTO);
    }
}
