<?php

namespace App\Services;

use App\Models\Article;
use App\Models\NewsSource;
use Illuminate\Support\Facades\DB;

class NewsAggregatorService
{
    public function __construct(
        private NewsApiService $newsApiService,
        private GuardianService $guardianService
    ) {}

    public function aggregateArticles(): void
    {
        $sources = NewsSource::where('is_active', true)->get();

        foreach ($sources as $source) {
            $articles = match ($source->api_service) {
                'newsapi' => $this->newsApiService->fetchArticles($source),
                'guardian' => $this->guardianService->fetchArticles($source),
                default => [],
            };

            $this->storeArticles($articles);
        }
    }

    private function storeArticles(array $articles): void
    {
        foreach ($articles as $articleData) {
            DB::transaction(function () use ($articleData) {
                Article::updateOrCreate(
                    [
                        'source_id' => $articleData['source_id'],
                        'external_id' => $articleData['external_id'],
                    ],
                    $articleData
                );
            });
        }
    }
}
