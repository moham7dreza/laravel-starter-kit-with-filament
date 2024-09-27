<?php

namespace App\DataContracts;

class JobChunkerDTO
{
    private string $job;
    private string $queue;
    private string $sql;
    private string $model;
    private array $bindings;
    private bool $logging;
    private int $batchSize;
    private bool $shouldQueue;

    public function getJob(): string
    {
        return $this->job;
    }

    public function setJob(string $job): JobChunkerDTO
    {
        $this->job = $job;
        return $this;
    }

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function setQueue(string $queue): JobChunkerDTO
    {
        $this->queue = $queue;
        return $this;
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    public function setSql(string $sql): JobChunkerDTO
    {
        $this->sql = $sql;
        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): JobChunkerDTO
    {
        $this->model = $model;
        return $this;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function setBindings(array $bindings): JobChunkerDTO
    {
        $this->bindings = $bindings;
        return $this;
    }

    public function isLogging(): bool
    {
        return $this->logging;
    }

    public function setLogging(bool $logging): JobChunkerDTO
    {
        $this->logging = $logging;
        return $this;
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    public function setBatchSize(int $batchSize): JobChunkerDTO
    {
        $this->batchSize = $batchSize;
        return $this;
    }

    public function isShouldQueue(): bool
    {
        return $this->shouldQueue;
    }

    public function setShouldQueue(bool $shouldQueue): JobChunkerDTO
    {
        $this->shouldQueue = $shouldQueue;
        return $this;
    }
}
