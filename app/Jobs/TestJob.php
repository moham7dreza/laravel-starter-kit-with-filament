<?php

namespace App\Jobs;

class TestJob extends AbstractChunkerJob
{
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $handler = function ($item) {
            sleep(1);
        };

        $this->handleJob($handler);
    }

    public function tags(): array
    {
        return [
            "test-job"
        ];
    }
}
