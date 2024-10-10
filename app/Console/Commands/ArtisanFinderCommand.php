<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;

class ArtisanFinderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'find:art {args?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find artisan command with given name';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $commands = collect($this->getApplication()?->all())
            ->keys()
            ->reject(fn(string $command) => $command === $this->signature)
            ->values()
            ->toArray();

        $command = suggest(
            'Search for a command',
            options: $commands,
            required: true,
            hint: 'Type parts of a command name to search for'
        );

        $args = $this->argument('args')
            ? explode(' ', text(
                label: 'Write arguments:',
                placeholder: 'E.g. queue',
                hint: 'This will be sent as command arguments',
            ))
            : [];

        $this->call($command, $args);


        return Command::SUCCESS;
    }
}
