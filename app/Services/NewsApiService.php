<?php

namespace App\Services;

use App\Models\NewsSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Helpers\ArticleHelper;

class NewsApiService
{
    private string $baseUrl = 'https://newsapi.org/v2';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.newsapi.key');
    }

    public function fetchArticles(NewsSource $source): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/everything", [
                'apiKey' => $this->apiKey,
                'sources' => 'techcrunch,reuters',
                'pageSize' => 50,
                'sortBy' => 'publishedAt',
            ]);

            if ($response->successful()) {
                return $this->transformArticles($response->json('articles'), $source);
            }

            Log::error('NewsAPI Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('NewsAPI Exception: ' . $e->getMessage());
            return [];
        }
    }

    private function transformArticles(array $articles, NewsSource $source): array
    {
        return array_filter(array_map(function ($article) use ($source) {
            try {
                return [
                    'news_source_id' => $source->id,
                    'external_id' => ArticleHelper::generateExternalId($article, 'newsapi'),
                    'title' => ArticleHelper::extractTitle($article),
                    'content' => $article['content'] ?? $article['description'] ?? '',
                    'excerpt' => $article['description'] ?? '',
                    'author' => $article['author'] ?? 'Unknown',
                    'category' => $article['category'] ?? 'general',
                    'url' => ArticleHelper::extractUrl($article),
                    'image_url' => $article['urlToImage'] ?? null,
                    'published_at' => $article['publishedAt'] ?? now()->toISOString(),
                ];
            } catch (\Exception $e) {
                Log::warning('Failed to transform NewsAPI article', [
                    'article' => $article,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        }, $articles));
    }
}
