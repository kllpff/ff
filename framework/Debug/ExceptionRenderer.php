<?php

namespace FF\Debug;

use Throwable;

/**
 * ExceptionRenderer - Pretty Exception Display
 * 
 * Renders exceptions with stack trace, code context, and request info
 */
class ExceptionRenderer
{
    protected Throwable $exception;
    protected bool $debugMode;
    protected ?Profiler $profiler;

    public function __construct(Throwable $exception, bool $debugMode = false, ?Profiler $profiler = null)
    {
        $this->exception = $exception;
        $this->debugMode = $debugMode;
        $this->profiler = $profiler;
    }

    /**
     * Render exception as HTML
     */
    public function render(): string
    {
        if (!$this->debugMode) {
            return $this->renderProduction();
        }

        return $this->renderDebug();
    }

    /**
     * Render debug view with full details
     */
    protected function renderDebug(): string
    {
        $exception = $this->exception;
        $file = $exception->getFile();
        $line = $exception->getLine();
        $code = $this->getCodeContext($file, $line);

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Exception - {$this->escape($exception->getMessage())}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; background: #1e1e1e; color: #d4d4d4; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header p { font-size: 14px; opacity: 0.9; }
        .section { background: #252526; border: 1px solid #3e3e42; border-radius: 5px; margin-bottom: 20px; }
        .section-title { background: #2d2d30; padding: 12px 15px; border-bottom: 1px solid #3e3e42; font-weight: bold; }
        .section-content { padding: 15px; }
        .code-line { display: flex; }
        .code-number { width: 60px; text-align: right; padding-right: 15px; color: #6a9955; user-select: none; flex-shrink: 0; }
        .code-line.error { background: #7f0f1c; }
        .code-line.error .code-number { background: #7f0f1c; }
        .code-content { flex: 1; white-space: pre-wrap; word-wrap: break-word; }
        .stack-trace { }
        .trace-item { padding: 10px 0; border-bottom: 1px solid #3e3e42; }
        .trace-item:last-child { border-bottom: none; }
        .trace-file { color: #4ec9b0; font-size: 12px; }
        .trace-function { color: #dcdcaa; margin-top: 3px; }
        .metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .metric-box { background: #2d2d30; padding: 15px; border-left: 3px solid #007acc; border-radius: 3px; }
        .metric-value { font-size: 20px; font-weight: bold; color: #4ec9b0; }
        .metric-label { font-size: 12px; color: #858585; margin-top: 5px; }
        .query-table { width: 100%; border-collapse: collapse; }
        .query-table th { text-align: left; padding: 10px; background: #2d2d30; border-bottom: 1px solid #3e3e42; font-weight: bold; }
        .query-table td { padding: 10px; border-bottom: 1px solid #3e3e42; }
        .query-slow { background: rgba(255, 107, 107, 0.1); }
        .query-sql { color: #ce9178; word-wrap: break-word; }
        .query-time { color: #4ec9b0; text-align: right; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$this->escape(class_basename($exception))}</h1>
            <p>{$this->escape($exception->getMessage())}</p>
        </div>

        {$this->renderCodeContext($code, $file, $line)}

        {$this->renderStackTrace()}

        {$this->renderProfiler()}

        {$this->renderRequest()}
    </div>
</body>
</html>
HTML;
    }

    /**
     * Render code context
     */
    protected function renderCodeContext(array $code, string $file, int $line): string
    {
        $lines = '';
        foreach ($code as $lineNum => $content) {
            $isError = $lineNum === $line;
            $class = $isError ? 'error' : '';
            $lines .= "<div class='code-line $class'>";
            $lines .= "<div class='code-number'>$lineNum</div>";
            $lines .= "<div class='code-content'>" . $this->escape($content) . "</div>";
            $lines .= "</div>";
        }

        return <<<HTML
        <div class="section">
            <div class="section-title">Code Context</div>
            <div class="section-content">
                <div class="trace-file">$file : $line</div>
                <div style="margin-top: 15px;">$lines</div>
            </div>
        </div>
HTML;
    }

    /**
     * Render stack trace
     */
    protected function renderStackTrace(): string
    {
        $trace = $this->exception->getTrace();
        $items = '';

        foreach ($trace as $index => $item) {
            $file = $item['file'] ?? 'unknown';
            $line = $item['line'] ?? 0;
            $class = $item['class'] ?? '';
            $function = $item['function'] ?? 'unknown';
            $type = $item['type'] ?? '::';

            $call = $class ? "{$class}{$type}{$function}()" : "{$function}()";

            $items .= <<<HTML
            <div class="trace-item">
                <div class="trace-file">#{$index} {$this->escape($file)} : {$line}</div>
                <div class="trace-function">{$this->escape($call)}</div>
            </div>
HTML;
        }

        return <<<HTML
        <div class="section">
            <div class="section-title">Stack Trace</div>
            <div class="section-content stack-trace">$items</div>
        </div>
HTML;
    }

    /**
     * Render profiler metrics
     */
    protected function renderProfiler(): string
    {
        if (!$this->profiler) {
            return '';
        }

        $duration = Profiler::formatMs($this->profiler->getTotalDuration());
        $memory = Profiler::formatBytes($this->profiler->getMemoryDelta());
        $peak = Profiler::formatBytes($this->profiler->getPeakMemory());
        $queries = $this->profiler->getQueryCount();
        $queryTime = Profiler::formatMs($this->profiler->getTotalQueryTime());

        $html = <<<HTML
        <div class="section">
            <div class="section-title">Performance Metrics</div>
            <div class="section-content">
                <div class="metrics">
                    <div class="metric-box">
                        <div class="metric-value">$duration</div>
                        <div class="metric-label">Request Duration</div>
                    </div>
                    <div class="metric-box">
                        <div class="metric-value">$peak</div>
                        <div class="metric-label">Peak Memory</div>
                    </div>
                    <div class="metric-box">
                        <div class="metric-value">$memory</div>
                        <div class="metric-label">Memory Delta</div>
                    </div>
                    <div class="metric-box">
                        <div class="metric-value">$queries</div>
                        <div class="metric-label">Database Queries</div>
                    </div>
                </div>
HTML;

        if ($queries > 0) {
            $html .= $this->renderQueries();
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * Render queries
     */
    protected function renderQueries(): string
    {
        $queries = $this->profiler->getQueries();
        $rows = '';

        foreach ($queries as $query) {
            $sql = $this->escape($query['sql']);
            $duration = Profiler::formatMs($query['duration']);
            $slow = $query['duration'] > 100 ? 'query-slow' : '';

            $rows .= <<<HTML
            <tr class="$slow">
                <td class="query-sql">$sql</td>
                <td class="query-time">$duration</td>
            </tr>
HTML;
        }

        return <<<HTML
        <div style="margin-top: 20px;">
            <h3 style="margin-bottom: 10px;">Database Queries</h3>
            <table class="query-table">
                <thead>
                    <tr>
                        <th>Query</th>
                        <th style="width: 80px;">Time</th>
                    </tr>
                </thead>
                <tbody>
                    $rows
                </tbody>
            </table>
        </div>
HTML;
    }

    /**
     * Render request info
     */
    protected function renderRequest(): string
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        return <<<HTML
        <div class="section">
            <div class="section-title">Request Information</div>
            <div class="section-content">
                <div style="display: grid; grid-template-columns: 150px 1fr; gap: 20px;">
                    <div><strong>Method:</strong></div>
                    <div>{$this->escape($method)}</div>
                    <div><strong>URI:</strong></div>
                    <div>{$this->escape($uri)}</div>
                    <div><strong>IP Address:</strong></div>
                    <div>{$this->escape($ip)}</div>
                    <div><strong>Timestamp:</strong></div>
                    <div>{$this->escape(date('Y-m-d H:i:s'))}</div>
                </div>
            </div>
        </div>
HTML;
    }

    /**
     * Render production view
     */
    protected function renderProduction(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error</title>
    <style>
        * { margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; }
        .container { max-width: 500px; margin: 100px auto; padding: 20px; }
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #dc3545; margin-bottom: 10px; }
        p { color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="box">
            <h1>Oops! Something went wrong</h1>
            <p>We're sorry, but something unexpected happened. Please try again later or contact support.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get code context lines
     */
    protected function getCodeContext(string $file, int $line, int $context = 7): array
    {
        if (!file_exists($file)) {
            return [];
        }

        $lines = file($file);
        if (!$lines) {
            return [];
        }

        $start = max(0, $line - $context - 1);
        $end = min(count($lines), $line + $context);

        $result = [];
        for ($i = $start; $i < $end; $i++) {
            $result[$i + 1] = rtrim($lines[$i]);
        }

        return $result;
    }

    /**
     * Escape HTML
     */
    protected function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Get class base name
 */
function class_basename($class): string
{
    $class = is_object($class) ? get_class($class) : $class;
    return basename(str_replace('\\', '/', $class));
}
