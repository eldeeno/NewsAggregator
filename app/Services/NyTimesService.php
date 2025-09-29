<?php

namespace App\Services;

use App\Models\NewsSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Helpers\ArticleHelper;

class NyTimesService
{
    private string $baseUrl = 'https://api.nytimes.com/svc/search/v2/articlesearch.json';
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.nytimes.key');
    }

    public function fetchArticles(NewsSource $source): array
    {
        try {
            $response = Http::get($this->baseUrl, [
                'api-key' => $this->apiKey,
                'sort' => 'newest',
                'page' => 0,
                'fl' => 'web_url,headline,abstract,byline,multimedia,pub_date,section_name,_id,lead_paragraph',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->transformArticles($data['response']['docs'] ?? [], $source);
            }

            Log::error('NYTimes API Error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('NYTimes API Exception: ' . $e->getMessage());
            return [];
        }
    }

    private function transformArticles(array $articles, NewsSource $source): array
    {
        return array_filter(array_map(function ($article) use ($source) {
            try {
                $headline = $article['headline'] ?? [];
                $multimedia = $article['multimedia'] ?? [];
                $byline = $article['byline'] ?? [];

                return [
                    'news_source_id' => $source->id,
                    'external_id' => ArticleHelper::generateExternalId($article, 'nytimes'),
                    'title' => ArticleHelper::extractTitle($article),
                    'content' => $article['lead_paragraph'] ?? $article['abstract'] ?? '',
                    'excerpt' => $article['abstract'] ?? '',
                    'author' => $this->extractAuthor($byline),
                    'category' => $article['section_name'] ?? 'general',
                    'url' => ArticleHelper::extractUrl($article),
                    'image_url' => $this->extractImageUrl($multimedia),
                    'published_at' => $article['pub_date'] ?? now()->toISOString(),
                ];
            } catch (\Exception $e) {
                Log::warning('Failed to transform NYTimes article', [
                    'article' => $article,
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        }, $articles));
    }

    private function extractAuthor(array $byline): string
    {
        if (!empty($byline['original'])) {
            return preg_replace('/^By /', '', $byline['original']);
        }

        if (!empty($byline['person']) && is_array($byline['person'])) {
            $authors = array_map(function ($person) {
                $firstName = $person['firstname'] ?? '';
                $lastName = $person['lastname'] ?? '';
                return trim("{$firstName} {$lastName}");
            }, $byline['person']);

            return implode(', ', array_filter($authors)) ?: 'The New York Times';
        }

        return 'The New York Times';
    }

    private function extractImageUrl(array $multimedia): ?string
    {
        if (empty($multimedia)) {
            return null;
        }

        // Filter out non-array elements and ensure we have valid multimedia items
        $validMultimedia = array_filter($multimedia, function ($media) {
            return is_array($media) && isset($media['type']);
        });

        if (empty($validMultimedia)) {
            return null;
        }

        // Look for xlarge image first
        $xlargeImage = array_filter($validMultimedia, function ($media) {
            return ($media['type'] === 'image' && ($media['subtype'] ?? '') === 'xlarge');
        });

        if (!empty($xlargeImage)) {
            $image = reset($xlargeImage);
            return 'https://www.nytimes.com/' . ($image['url'] ?? '');
        }

        // Look for large image
        $largeImage = array_filter($validMultimedia, function ($media) {
            return ($media['type'] === 'image' && ($media['subtype'] ?? '') === 'large');
        });

        if (!empty($largeImage)) {
            $image = reset($largeImage);
            return 'https://www.nytimes.com/' . ($image['url'] ?? '');
        }

        // Look for any image
        $anyImage = array_filter($validMultimedia, function ($media) {
            return $media['type'] === 'image';
        });

        if (!empty($anyImage)) {
            $image = reset($anyImage);
            return 'https://www.nytimes.com/' . ($image['url'] ?? '');
        }

        return null;
    }
}
