#!/usr/bin/env php
<?php

$url = 'http://localhost:8000/api/status';
$count = 0;
$limited = 0;

echo "Testing Rate Limit...\n";
echo "Making 65 requests to $url\n\n";

for ($i = 1; $i <= 65; $i++) {
    $response = file_get_contents($url, false, stream_context_create([
        'http' => [
            'method' => 'GET',
            'ignore_errors' => true
        ]
    ]));
    
    $data = json_decode($response, true);
    
    if (isset($data['status']) && $data['status'] === 'ok') {
        $count++;
        echo "✅ Request $i: OK\n";
    } elseif (isset($data['error']) && $data['error'] === 'Rate limit exceeded') {
        $limited++;
        echo "❌ Request $i: Rate limited (retry after {$data['retry_after']}s)\n";
    } else {
        echo "❓ Request $i: Unexpected response\n";
    }
    
    // Small delay to avoid overwhelming
    usleep(10000); // 10ms
}

echo "\n📊 Results:\n";
echo "Successful: $count\n";
echo "Rate limited: $limited\n";
echo "Total: " . ($count + $limited) . "\n";
