<?php

declare(strict_types=1);

namespace BOA\Results;

use BVP\Scraper\Scraper;
use Carbon\CarbonImmutable as Carbon;
use DateTimeInterface;

/**
 * @author shimomo
 */
final class Synchronizer
{
    /**
     * @param \DateTimeInterface|string $date
     * @return array<array-key, mixed>
     */
    public static function sync(DateTimeInterface|string $date = 'today'): array
    {
        $date = Carbon::parse($date, 'Asia/Tokyo');

        /** @var array<array-key, array<array-key, array<array-key, array{boats: array<mixed>}>>> $results */
        $results = Scraper::scrapeResults($date);

        return self::normalize($results);
    }

    /**
     * @param array<array-key, array<array-key, array<array-key, array{boats: array<mixed>}>>> $results
     * @return array<array-key, mixed>
     */
    private static function normalize(array $results): array
    {
        $newResults = [];

        foreach (array_values($results) as $data) {
            foreach (array_values($data) as $result) {
                $result['boats'] = isset($result['boats'])
                    ? array_values($result['boats'])
                    : [];

                $newResults[] = $result;
            }
        }

        return $newResults;
    }
}
