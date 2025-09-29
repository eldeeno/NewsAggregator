<?php

namespace App\Jobs;

use App\Models\NewsSource;
use App\Services\NewsApiService;
use App\Services\GuardianService;
use App\Services\NyTimesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FetchNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $sourceId;
    public $tries = 3;
    public $timeout = 300;

    public function __construct(int $sourceId)
    {
        $this->sourceId = $sourceId;
    }

    public function handle(
        NewsApiService $newsApiService,
        GuardianService $guardianService,
        NyTimesService $nyTimesService
    ): void {
        $source = NewsSource::find($this->sourceId);

        if (!$source || !$source->is_active) {
            Log::warning("Source not found or inactive: {$this->sourceId}");
            return;
        }

        // Check rate limit
        if ($this->isRateLimited($source)) {
            Log::warning("Rate limit reached for source: {$source->name}");
            $this->release(300); // Retry in 5 minutes
            return;
        }

        Log::info("Fetching articles from: {$source->name}");

        try {
            $articles = match ($source->api_service) {
                'newsapi' => $newsApiService->fetchArticles($source),
                'guardian' => $guardianService->fetchArticles($source),
                'nytimes' => $nyTimesService->fetchArticles($source),
                default => [],
            };

            $this->storeArticles($articles);
            $this->updateRateLimit($source);

            Log::info("Successfully processed {$source->name}");

        } catch (\Exception $e) {
            Log::error("Failed to fetch from {$source->name}: " . $e->getMessage());
            throw $e; // This will trigger retry
        }
    }

    private function isRateLimited(NewsSource $source): bool
    {
        $key = "rate_limit:{$source->api_service}";
        $lastFetch = Cache::get($key);

        if (!$lastFetch) return false;

        $rateLimits = [
            'newsapi' => 10,
            'guardian' => 1,
            'nytimes' => 5,
        ];

        $limitMinutes = $rateLimits[$source->api_service] ?? 5;
        return now()->diffInMinutes($lastFetch) < $limitMinutes;
    }

    private function updateRateLimit(NewsSource $source): void
    {
        $key = "rate_limit:{$source->api_service}";
        Cache::put($key, now(), now()->addHours(1));
    }

    private function storeArticles(array $articles): void
    {
        foreach ($articles as $articleData) {
            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($articleData) {
                    if (empty($articleData['news_source_id']) || empty($articleData['external_id'])) {
                        return;
                    }

                    \App\Models\Article::updateOrCreate(
                        [
                            'news_source_id' => $articleData['news_source_id'],
                            'external_id' => $articleData['external_id'],
                        ],
                        $articleData
                    );
                });
            } catch (\Exception $e) {
                Log::error('Failed to store article: ' . $e->getMessage());
            }
        }
    }
}
