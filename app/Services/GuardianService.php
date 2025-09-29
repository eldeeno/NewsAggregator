<?php

namespace App\Services;

use App\Models\NewsSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Helpers\ArticleHelper;

class GuardianService
{
    private string $baseUrl = 'https://content.guardianapis.com';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.guardian.key');
    }

    public function fetchArticles(NewsSource $source): array
    {
        try {
            $response = Http::get("{$this->baseUrl}/search", [
                'api-key' => $this->apiKey,
                'page-size' => 100,
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
        return array_filter(array_map(function ($article) use ($source) {
            try {
                $fields = $article['fields'] ?? [];

                return [
                    'news_source_id' => $source->id,
                    'external_id' => ArticleHelper::generateExternalId($article, 'guardian'),
                    'title' => ArticleHelper::extractTitle($article),
                    'content' => $fields['body'] ?? '',
                    'excerpt' => $fields['trailText'] ?? '',
                    'author' => $fields['byline'] ?? 'The Guardian',
                    'category' => $article['sectionName'] ?? 'general',
                    'url' => ArticleHelper::extractUrl($article),
                    'image_url' => $fields['thumbnail'] ?? null,
                    'published_at' => $article['webPublicationDate'] ?? now()->toISOString(),
                ];
            } catch (\Exception $e) {
                Log::warning('Failed to transform Guardian article', [
                    'article' => $article,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        }, $articles));
    }
}
