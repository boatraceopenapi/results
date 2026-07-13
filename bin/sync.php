<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use BOA\Results\Storage;
use BOA\Results\Synchronizer;
use Carbon\CarbonImmutable as Carbon;

$version = $argv[1] ?? 'v3';

$today = Carbon::today('Asia/Tokyo');
$todayY = $today->format('Y');
$todayYmd = $today->format('Ymd');

$yesterday = $today->subDay();
$yesterdayY = $yesterday->format('Y');
$yesterdayYmd = $yesterday->format('Ymd');

$payload = [
    'today' => ['results' => []],
    'yesterday' => ['results' => []],
];

if ($version === 'v2' || $version === 'v3') {
    $payload['today']['results'] = Synchronizer::sync($today);
    $payload['yesterday']['results'] = Synchronizer::sync($yesterday);
}

if ($payload['today']['results'] !== []) {
    Storage::save("docs/{$version}/{$todayY}/{$todayYmd}.json", $payload['today']);
    Storage::save("docs/{$version}/today.json", $payload['today']);
}

if ($payload['yesterday']['results'] !== []) {
    Storage::save("docs/{$version}/{$yesterdayY}/{$yesterdayYmd}.json", $payload['yesterday']);
    Storage::save("docs/{$version}/yesterday.json", $payload['yesterday']);
}
