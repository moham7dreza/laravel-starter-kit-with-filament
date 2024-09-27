<?php

namespace App\DataContracts;

class JobChunkerDTO
{
    public string $job;
    public string $queue;
    public string $sql;
    public string $model;
    public array $bindings;
    public bool $logging;
    public int $batchSize;
}
