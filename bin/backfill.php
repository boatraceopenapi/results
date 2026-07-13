<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use BOA\Results\Storage;
use BOA\Results\Synchronizer;
use Carbon\CarbonImmutable as Carbon;

$version = $argv[1] ?? 'v3';
$date = $argv[2] ?? 'today';

$date = Carbon::parse($date, 'Asia/Tokyo');
$dateY = $date->format('Y');
$dateYmd = $date->format('Ymd');

$results = Synchronizer::sync($date);

if ($results === []) {
    fwrite(STDOUT, "NO_DATA {$dateYmd}\n");
    exit(2);
}

Storage::save("docs/{$version}/{$dateY}/{$dateYmd}.json", ['results' => $results]);
echo "OK {$dateYmd}\n";
exit(0);
