<?php

namespace App\DataContracts;

use Closure;
use Illuminate\Console\OutputStyle;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Symfony\Component\Stopwatch\Stopwatch;

class JobChunkerDTO
{
    public function __construct(
        public readonly string                       $job,
        public readonly string                       $queue,
        public readonly bool                         $logging,
        public readonly int                          $batchSize,
        public readonly bool                         $shouldQueue,
        public readonly bool                         $hydrateResult,
        public readonly EloquentBuilder|QueryBuilder $query,
        public readonly OutputStyle                  $outputStyle,
        public readonly string                       $signature,
        public readonly Closure                     $itemHandler,
        public Stopwatch                             $stopwatch = new Stopwatch(),
        public array                                 $idsRange = [],
    )
    {
        //
    }
}
