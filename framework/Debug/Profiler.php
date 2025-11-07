<?php

namespace FF\Debug;

/**
 * Profiler - Performance Profiling
 * 
 * Tracks execution time, memory usage, and collects performance metrics
 */
class Profiler
{
    /**
     * Start time
     */
    protected float $startTime;

    /**
     * Start memory
     */
    protected int $startMemory;

    /**
     * Measurements
     */
    protected array $measurements = [];

    /**
     * Query metrics
     */
    protected array $queries = [];

    /**
     * Middleware metrics
     */
    protected array $middleware = [];

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
    }

    /**
     * Mark a measurement
     */
    public function mark(string $name): void
    {
        $this->measurements[$name] = [
            'time' => microtime(true),
            'memory' => memory_get_usage(true),
        ];
    }

    /**
     * Measure duration between two marks
     */
    public function measure(string $name, string $startMark, string $endMark): void
    {
        if (!isset($this->measurements[$startMark]) || !isset($this->measurements[$endMark])) {
            return;
        }

        $start = $this->measurements[$startMark];
        $end = $this->measurements[$endMark];

        $this->measurements[$name] = [
            'duration' => ($end['time'] - $start['time']) * 1000,
            'memory' => $end['memory'] - $start['memory'],
        ];
    }

    /**
     * Record a query execution
     */
    public function recordQuery(string $sql, float $duration, int $rows = 0): void
    {
        $this->queries[] = [
            'sql' => $sql,
            'duration' => $duration * 1000,
            'rows' => $rows,
            'time' => microtime(true),
        ];
    }

    /**
     * Record middleware execution
     */
    public function recordMiddleware(string $name, float $duration): void
    {
        $this->middleware[] = [
            'name' => $name,
            'duration' => $duration * 1000,
        ];
    }

    /**
     * Get total request duration
     */
    public function getTotalDuration(): float
    {
        return (microtime(true) - $this->startTime) * 1000;
    }

    /**
     * Get peak memory usage
     */
    public function getPeakMemory(): int
    {
        return memory_get_peak_usage(true);
    }

    /**
     * Get current memory usage
     */
    public function getCurrentMemory(): int
    {
        return memory_get_usage(true);
    }

    /**
     * Get memory delta
     */
    public function getMemoryDelta(): int
    {
        return $this->getCurrentMemory() - $this->startMemory;
    }

    /**
     * Get all queries
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * Get query count
     */
    public function getQueryCount(): int
    {
        return count($this->queries);
    }

    /**
     * Get total query time
     */
    public function getTotalQueryTime(): float
    {
        $total = 0;
        foreach ($this->queries as $query) {
            $total += $query['duration'];
        }
        return $total;
    }

    /**
     * Get all middleware metrics
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Get all measurements
     */
    public function getMeasurements(): array
    {
        return $this->measurements;
    }

    /**
     * Format bytes
     */
    public static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Format milliseconds
     */
    public static function formatMs(float $ms): string
    {
        if ($ms < 1) {
            return round($ms * 1000, 2) . ' μs';
        }
        if ($ms < 1000) {
            return round($ms, 2) . ' ms';
        }
        return round($ms / 1000, 2) . ' s';
    }
}
