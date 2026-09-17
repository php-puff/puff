<?php
declare(strict_types=1);
/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

if (!\extension_loaded('curl')) {
    throw new RuntimeException('The curl extension is required.');
}

$options = \getopt('', ['url:', 'requests:', 'concurrency:', 'timeout:', 'report:']);
$url = (string) ($options['url'] ?? 'http://localhost:8620/api/status');
$requests = (int) ($options['requests'] ?? 1000);
$concurrency = (int) ($options['concurrency'] ?? 50);
$timeout = (float) ($options['timeout'] ?? 5.0);
$reportDirectory = (string) ($options['report'] ?? __DIR__ . '/reports');

if ($requests < 1 || $concurrency < 1 || $timeout <= 0) {
    throw new InvalidArgumentException('requests, concurrency and timeout must be greater than zero.');
}
$concurrency = \min($concurrency, $requests);

/** @return list<array{status: int, time: float, bytes: int, error: string}> */
function requestBatch(string $url, int $count, float $timeout): array
{
    static $multi = null;
    static $pool = [];
    $multi ??= \curl_multi_init();
    $handles = [];
    for ($index = 0; $index < $count; ++$index) {
        if (!isset($pool[$index])) {
            $pool[$index] = \curl_init($url);
            \curl_setopt_array($pool[$index], [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT_MS => (int) ($timeout * 1000),
                CURLOPT_TIMEOUT_MS => (int) ($timeout * 1000),
                CURLOPT_HTTPHEADER => ['Accept: application/json', 'Connection: keep-alive'],
                CURLOPT_FORBID_REUSE => false,
                CURLOPT_FRESH_CONNECT => false,
                CURLOPT_TCP_KEEPALIVE => 1,
            ]);
        }
        $handle = $pool[$index];
        $handles[] = $handle;
        \curl_multi_add_handle($multi, $handle);
    }

    do {
        $status = \curl_multi_exec($multi, $running);
        if ($running > 0) {
            $selected = \curl_multi_select($multi, 1.0);
            if ($selected === -1) {
                \usleep(1000);
            }
        }
    } while ($running > 0 && $status === CURLM_OK);

    $multiErrors = [];
    while (($message = \curl_multi_info_read($multi)) !== false) {
        $multiErrors[\spl_object_id($message['handle'])] = (int) $message['result'];
    }

    $results = [];
    foreach ($handles as $handle) {
        $multiError = $multiErrors[\spl_object_id($handle)] ?? CURLE_OK;
        $error = \curl_error($handle);
        if ($error === '' && $multiError !== CURLE_OK) {
            $error = \curl_strerror($multiError);
        }
        $results[] = [
            'status' => (int) \curl_getinfo($handle, CURLINFO_RESPONSE_CODE),
            'time' => (float) \curl_getinfo($handle, CURLINFO_TOTAL_TIME),
            'bytes' => (int) \curl_getinfo($handle, CURLINFO_SIZE_DOWNLOAD),
            'error' => $error,
        ];
        \curl_multi_remove_handle($multi, $handle);
    }
    return $results;
}

function percentile(array $sorted, float $percentile): float
{
    if ($sorted === []) {
        return 0.0;
    }
    $index = (int) \ceil($percentile * \count($sorted)) - 1;
    return (float) $sorted[\max(0, \min($index, \count($sorted) - 1))];
}

// Warm the route, autoloader and worker-local caches without including it in metrics.
requestBatch($url, \min(10, $concurrency), $timeout);

$startedAt = \microtime(true);
$latencies = [];
$statuses = [];
$errors = [];
$bytes = 0;
$successful = 0;
for ($completed = 0; $completed < $requests; $completed += $concurrency) {
    foreach (requestBatch($url, \min($concurrency, $requests - $completed), $timeout) as $result) {
        $latencies[] = $result['time'] * 1000;
        $statuses[(string) $result['status']] = ($statuses[(string) $result['status']] ?? 0) + 1;
        if ($result['error'] !== '') {
            $errors[$result['error']] = ($errors[$result['error']] ?? 0) + 1;
        }
        if ($result['error'] === '' && $result['status'] >= 200 && $result['status'] < 400) {
            ++$successful;
        }
        $bytes += $result['bytes'];
    }
}
$duration = \microtime(true) - $startedAt;

\sort($latencies, SORT_NUMERIC);
\ksort($statuses, SORT_NATURAL);

$report = [
    'generated_at' => \date(DATE_ATOM),
    'url' => $url,
    'requests' => $requests,
    'concurrency' => $concurrency,
    'successful' => $successful,
    'failed' => $requests - $successful,
    'duration_seconds' => \round($duration, 4),
    'requests_per_second' => \round($requests / $duration, 2),
    'latency_ms' => [
        'min' => \round($latencies[0] ?? 0, 3),
        'average' => \round(\array_sum($latencies) / \max(1, \count($latencies)), 3),
        'p50' => \round(percentile($latencies, 0.50), 3),
        'p95' => \round(percentile($latencies, 0.95), 3),
        'p99' => \round(percentile($latencies, 0.99), 3),
        'max' => \round($latencies[\array_key_last($latencies)] ?? 0, 3),
    ],
    'transferred_bytes' => $bytes,
    'http_statuses' => $statuses,
    'errors' => $errors,
];

if (!\is_dir($reportDirectory) && !\mkdir($reportDirectory, 0777, true) && !\is_dir($reportDirectory)) {
    throw new RuntimeException("Unable to create report directory [{$reportDirectory}].");
}
$name = 'http-benchmark-' . \date('Ymd-His');
$jsonFile = $reportDirectory . '/' . $name . '.json';
$markdownFile = $reportDirectory . '/' . $name . '.md';
\file_put_contents($jsonFile, \json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

$latency = $report['latency_ms'];
$statusJson = \json_encode($statuses, JSON_PRETTY_PRINT);
$errorJson = \json_encode($errors, JSON_PRETTY_PRINT);
$markdown = <<<MD
# Puff HTTP Benchmark

| Metric | Value |
|---|---:|
| Generated | {$report['generated_at']} |
| URL | `{$report['url']}` |
| Requests | {$report['requests']} |
| Concurrency | {$report['concurrency']} |
| Successful | {$report['successful']} |
| Failed | {$report['failed']} |
| Duration | {$report['duration_seconds']} s |
| Throughput | {$report['requests_per_second']} req/s |
| Transferred | {$report['transferred_bytes']} bytes |

## Latency

| Min | Average | P50 | P95 | P99 | Max |
|---:|---:|---:|---:|---:|---:|
| {$latency['min']} ms | {$latency['average']} ms | {$latency['p50']} ms | {$latency['p95']} ms | {$latency['p99']} ms | {$latency['max']} ms |

## HTTP Statuses

```json
{$statusJson}
```

## Errors

```json
{$errorJson}
```
MD;
\file_put_contents($markdownFile, $markdown . PHP_EOL);

\fwrite(STDOUT, $markdown . PHP_EOL . PHP_EOL . "JSON: {$jsonFile}" . PHP_EOL . "Markdown: {$markdownFile}" . PHP_EOL);
exit($successful === $requests ? 0 : 1);
