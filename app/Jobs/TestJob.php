<?php

namespace App\Jobs;

use App\DataContracts\JobChunkerDTO;
use App\Traits\JobChunkerTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TestJob implements ShouldQueue
{
    use Queueable;
    use JobChunkerTrait;

    public function __construct(
        public JobChunkerDTO $DTO,
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->handleChunkedJob($this->DTO);
    }

//    public function tags(): array
//    {
//        return [
//            "test-job"
//        ];
//    }
}
