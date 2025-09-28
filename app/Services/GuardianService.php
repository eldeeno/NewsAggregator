<?php

namespace App\Services;

use App\Models\NewsSource;
use Illuminate\Support\Facades\Log;

class GuardianService
{
    private string $baseUrl = 'https://content.guardianapis.com';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.guardian.key');
    }

    public function fetchArticles(Source $source): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/search", [
                'api-key' => $this->apiKey,
                'page-size' => 50,
                'show-fields' => 'all',
            ]);

            if ($response->successful()) {
                return $this->transformArticles($response->json('response.results'), $source);
            }

            Log::error('Guardian API Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('Guardian API Exception: ' . $e->getMessage());
            return [];
        }
    }

    private function transformArticles(array $articles, NewsSource $source): array
    {
        return array_map(function ($article) use ($source) {
            return [
                'news_source_id' => $source->id,
                'external_id' => $article['id'],
                'title' => $article['webTitle'],
                'content' => $article['fields']['body'] ?? '',
                'excerpt' => $article['fields']['trailText'] ?? '',
                'author' => $article['fields']['byline'] ?? '',
                'category' => $article['sectionName'] ?? 'general',
                'url' => $article['webUrl'],
                'image_url' => $article['fields']['thumbnail'] ?? null,
                'published_at' => $article['webPublicationDate'],
            ];
        }, $articles);
    }
}
