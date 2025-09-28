<?php

namespace App\Services;

use App\Models\NewsSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsApiService
{
    private string $baseUrl = 'https://newsapi.org/v2';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.newsapi.key');
    }

    public function fetchArticles(Source $source): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/everything", [
                'apiKey' => $this->apiKey,
                'sources' => 'bbc-news,cnn,reuters',
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
        return array_map(function ($article) use ($source) {
            return [
                'news_source_id' => $source->id,
                'external_id' => md5($article['url']),
                'title' => $article['title'],
                'content' => $article['content'] ?? $article['description'],
                'excerpt' => $article['description'],
                'author' => $article['author'],
                'category' => 'general',
                'url' => $article['url'],
                'image_url' => $article['urlToImage'],
                'published_at' => $article['publishedAt'],
            ];
        }, $articles);
    }
}
