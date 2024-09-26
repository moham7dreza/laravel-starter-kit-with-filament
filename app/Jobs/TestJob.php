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
            dump('inside handler');
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
