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
        $commands = collect($this->getApplication()?->all());

        $commandsTitles = $commands->keys()
            ->reject(fn(string $command) => $command === $this->signature)
            ->values()
            ->toArray();

        $commandName = suggest(
            'Search for a command',
            options: $commandsTitles,
            required: true,
            hint: 'Type parts of a command name to search for'
        );

        if (!$commands->keys()->contains($commandName)) {
            $this->error("Command not found.");
            return Command::FAILURE;
        }

        // Get arguments and options definitions
        $definition = $commands->get($commandName)->getDefinition();
        $arguments = $definition->getArguments();
        $options = $definition->getOptions();

        // Prepare placeholder text showing all args and options
        $argsList = implode(' ', array_map(static fn($arg) => $arg->getName(), $arguments));
        $optionsList = implode(' ', array_map(static fn($opt) => '--' . $opt->getName(), $options));
        $placeholderText = trim("$argsList $optionsList");

        // Prompt the user for argument values based on the placeholder
        $argsInput = text(
            label: 'Write arguments:',
            placeholder: $placeholderText,
            hint: 'This will be sent as command arguments and options',
        );

        // Parse user input and map to arguments and options
        $userValues = explode(' ', $argsInput);
        $commandParameters = [];

        // Map arguments first
        $argNames = array_keys($arguments);
        foreach ($argNames as $index => $argName) {
            $commandParameters[$argName] = $userValues[$index] ?? null;
        }

        // Map options based on remaining values
        foreach (array_keys($options) as $index => $optName) {
            $commandParameters['--' . $optName] = $userValues[count($argNames) + $index] ?? null;
        }

        // Call the command with the mapped arguments and options
        $this->call($commandName, $commandParameters);

        return Command::SUCCESS;
    }
}
