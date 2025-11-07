<?php

namespace FF\Debug;

use FF\Log\Logger;

/**
 * DebugBar - Debug Toolbar
 * 
 * Provides debugging information during development including
 * query logs, request/response data, timing, and memory usage.
 */
class DebugBar
{
    /**
     * Whether debug bar is enabled
     * 
     * @var bool
     */
    protected bool $enabled = false;

    /**
     * Debug data collector
     * 
     * @var array
     */
    protected array $data = [
        'queries' => [],
        'time' => [],
        'memory' => [],
        'middleware' => [],
        'variables' => [],
    ];

    /**
     * Profiler instance
     * 
     * @var Profiler|null
     */
    protected ?Profiler $profiler = null;

    /**
     * Start time for measuring
     * 
     * @var float
     */
    protected float $startTime;

    /**
     * The logger instance
     * 
     * @var Logger|null
     */
    protected ?Logger $logger = null;

    /**
     * Create a new DebugBar instance
     * 
     * @param bool $enabled Whether to enable the debug bar
     */
    public function __construct(bool $enabled = false)
    {
        $this->enabled = $enabled;
        $this->startTime = microtime(true);
        if ($enabled) {
            $this->profiler = new Profiler();
        }
    }

    /**
     * Get profiler instance
     */
    public function getProfiler(): ?Profiler
    {
        return $this->profiler;
    }

    /**
     * Enable the debug bar
     * 
     * @return self
     */
    public function enable(): self
    {
        $this->enabled = true;
        return $this;
    }

    /**
     * Disable the debug bar
     * 
     * @return self
     */
    public function disable(): self
    {
        $this->enabled = false;
        return $this;
    }

    /**
     * Check if debug bar is enabled
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Log a database query
     * 
     * @param string $sql The SQL query
     * @param array $bindings Query bindings
     * @param float $time Execution time in milliseconds
     * @return void
     */
    public function logQuery(string $sql, array $bindings = [], float $time = 0): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->data['queries'][] = [
            'sql' => $sql,
            'bindings' => $bindings,
            'time' => $time,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get all logged queries
     * 
     * @return array
     */
    public function getQueries(): array
    {
        return $this->data['queries'];
    }

    /**
     * Get query count
     * 
     * @return int
     */
    public function getQueryCount(): int
    {
        return count($this->data['queries']);
    }

    /**
     * Get total query time
     * 
     * @return float Total time in milliseconds
     */
    public function getTotalQueryTime(): float
    {
        $total = 0;
        foreach ($this->data['queries'] as $query) {
            $total += $query['time'];
        }
        return $total;
    }

    /**
     * Record elapsed time for an operation
     * 
     * @param string $name The operation name
     * @param float $duration Duration in milliseconds
     * @return void
     */
    public function time(string $name, float $duration): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->data['time'][] = [
            'name' => $name,
            'duration' => $duration,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get total execution time since start
     * 
     * @return float Time in milliseconds
     */
    public function getTotalTime(): float
    {
        return (microtime(true) - $this->startTime) * 1000;
    }

    /**
     * Record memory usage
     * 
     * @param string $label The label for this measurement
     * @return void
     */
    public function recordMemory(string $label = 'checkpoint'): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->data['memory'][] = [
            'label' => $label,
            'usage' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get memory measurements
     * 
     * @return array
     */
    public function getMemoryMeasurements(): array
    {
        return $this->data['memory'];
    }

    /**
     * Get peak memory usage
     * 
     * @return float Memory in bytes
     */
    public function getPeakMemory(): float
    {
        return memory_get_peak_usage(true);
    }

    /**
     * Record middleware execution
     * 
     * @param string $name Middleware name
     * @param float $duration Duration in milliseconds
     * @return void
     */
    public function recordMiddleware(string $name, float $duration): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->data['middleware'][] = [
            'name' => $name,
            'duration' => $duration,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        if ($this->profiler) {
            $this->profiler->recordMiddleware($name, $duration);
        }
    }

    /**
     * Get middleware metrics
     * 
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->data['middleware'];
    }

    /**
     * Render the debug bar HTML with interactive modal
     * 
     * @return string HTML
     */
    public function render(): string
    {
        if (!$this->enabled) {
            return '';
        }

        $queryCount = $this->getQueryCount();
        $queryTime = Profiler::formatMs($this->getTotalQueryTime());
        $totalTime = Profiler::formatMs($this->getTotalTime());
        $memory = Profiler::formatBytes($this->getPeakMemory());
        $middlewareCount = count($this->data['middleware']);
        
        // Build queries JSON for JavaScript
        $queriesJson = json_encode($this->data['queries']);

        $html = '<div id="ff-debug-bar" style="
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #1e1e1e;
    color: #e0e0e0;
    padding: 12px 20px;
    border-top: 3px solid #d32f2f;
    font-family: \'Monaco\', monospace;
    font-size: 12px;
    z-index: 10000;
    display: flex;
    justify-content: space-between;
    gap: 30px;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
">
    <div>⏱️ Request: <strong>' . $totalTime . '</strong></div>
    <div>💾 Memory: <strong>' . $memory . '</strong></div>
    <div onclick="document.getElementById(\'ff-debug-modal\').style.display=\'flex\'" style="cursor: pointer; user-select: none; padding: 5px; border-radius: 3px; background: rgba(211, 47, 47, 0.2);">🗄️ Queries: <strong>' . $queryCount . '</strong> (<strong>' . $queryTime . '</strong>)</div>
    <div>⚙️ Middleware: <strong>' . $middlewareCount . '</strong></div>
    <div>🔥 FF Debug Bar v1.2</div>
</div>

<!-- Debug Modal -->
<div id="ff-debug-modal" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.8);
    z-index: 10001;
    justify-content: center;
    align-items: center;
    padding: 20px;
    font-family: \'Monaco\', monospace;
" onclick="if(event.target === this) this.style.display=\'none\'">
    <div style="
        background: #1e1e1e;
        color: #e0e0e0;
        max-width: 900px;
        max-height: 90vh;
        overflow: auto;
        border-radius: 8px;
        padding: 20px;
        border: 2px solid #d32f2f;
    ">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #333; padding-bottom: 10px;">
            <h2 style="margin: 0; color: #d32f2f; font-size: 18px;">Database Queries (' . $queryCount . ')</h2>
            <button onclick="document.getElementById(\'ff-debug-modal\').style.display=\'none\'" style="
                background: #d32f2f;
                color: white;
                border: none;
                padding: 8px 15px;
                border-radius: 4px;
                cursor: pointer;
                font-family: monospace;
            ">Close ×</button>
        </div>
        <div id="ff-queries-list"></div>
    </div>
</div>

<script>
(function() {
    const queries = ' . $queriesJson . ';
    const listEl = document.getElementById("ff-queries-list");
    
    if (!queries || queries.length === 0) {
        listEl.innerHTML = "<p style=\"color: #888; text-align: center; padding: 20px;\">No queries executed</p>";
        return;
    }
    
    queries.forEach((query, index) => {
        const div = document.createElement("div");
        div.style.cssText = "margin-bottom: 20px; border-left: 3px solid #d32f2f; padding-left: 15px; padding-top: 10px; padding-bottom: 10px; background: rgba(211, 47, 47, 0.05); border-radius: 3px;";
        
        const bindingsHtml = query.bindings && Object.keys(query.bindings).length > 0 
            ? \'<strong style="color: #4ec9b0;">Bindings:</strong> <pre style="background: #0d1117; padding: 10px; border-radius: 3px; overflow-x: auto; margin-top: 5px;">\' + JSON.stringify(query.bindings, null, 2) + \'</pre>\'
            : "";
        
        div.innerHTML = `
            <div style="margin-bottom: 10px;">
                <strong style="color: #4ec9b0;">Query #${index + 1}</strong>
                <span style="color: #888; float: right; font-size: 11px;">${query.timestamp} - ${query.time.toFixed(2)} ms</span>
            </div>
            <pre style="
                background: #0d1117;
                padding: 10px;
                border-radius: 3px;
                overflow-x: auto;
                margin: 0;
                color: #d4d4d4;
                border: 1px solid #30363d;
                font-size: 12px;
                line-height: 1.5;
            ">${escapeHtml(query.sql)}</pre>
            ${bindingsHtml}
        `;
        listEl.appendChild(div);
    });
    
    function escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }
})();
</script>';

        return $html;
    }
}
