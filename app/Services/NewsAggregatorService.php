<?php

namespace App\Services;

use App\Models\Article;
use App\Models\NewsSource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewsAggregatorService
{
    public function __construct(
        private NewsApiService $newsApiService,
        private GuardianService $guardianService,
        private NyTimesService $nyTimesService
    ) {}

    public function aggregateArticles(): void
    {
        $sources = NewsSource::where('is_active', true)->get();

        foreach ($sources as $source) {
            Log::info("Fetching articles for source: {$source->name}");

            $articles = match ($source->api_service) {
                'newsapi' => $this->newsApiService->fetchArticles($source),
                'guardian' => $this->guardianService->fetchArticles($source),
                'nytimes' => $this->nyTimesService->fetchArticles($source),
                default => [],
            };

            $storedCount = $this->storeArticles($articles);
            Log::info("Stored {$storedCount} articles for source: {$source->name}");
        }
    }

    private function storeArticles(array $articles): int
    {
        $storedCount = 0;

        foreach ($articles as $articleData) {
            try {
                DB::transaction(function () use ($articleData, &$storedCount) {
                    // Validate required fields
                    if (empty($articleData['news_source_id']) || empty($articleData['external_id'])) {
                        Log::warning('Article missing required fields', $articleData);
                        return;
                    }

                    $result = Article::updateOrCreate(
                        [
                            'news_source_id' => $articleData['news_source_id'],
                            'external_id' => $articleData['external_id'],
                        ],
                        $articleData
                    );

                    if ($result->wasRecentlyCreated) {
                        $storedCount++;
                    }
                });
            } catch (\Exception $e) {
                Log::error('Failed to store article', [
                    'article_data' => $articleData,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $storedCount;
    }

    /**
     * Get duplicate detection report
     */
    public function getDuplicateReport(): array
    {
        return DB::table('articles')
            ->select('external_id', DB::raw('COUNT(*) as count'))
            ->groupBy('external_id')
            ->having('count', '>', 1)
            ->get()
            ->toArray();
    }
}
