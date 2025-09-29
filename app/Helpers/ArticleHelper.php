<?php

namespace App\Helpers;

class ArticleHelper
{
    /**
     * Generate a consistent external_id across all news services
     */
    public static function generateExternalId(array $articleData, string $service): string
    {
        $strategies = [
            // Use native ID if available and reliable
            function ($data) use ($service) {
                $nativeId = match($service) {
                    'nytimes' => $data['_id'] ?? null,
                    'guardian' => $data['id'] ?? null,
                    'newsapi' => null, // NewsAPI doesn't have a native ID
                    default => null
                };

                return $nativeId ? "{$service}_{$nativeId}" : null;
            },

            // Use URL as fallback
            function ($data) use ($service) {
                $url = $data['web_url'] ?? $data['url'] ?? $data['webUrl'] ?? null;
                return $url ? "{$service}_" . md5($url) : null;
            },

            // Final fallback - hash the title + published date
            function ($data) use ($service) {
                $title = $data['headline']['main'] ?? $data['title'] ?? $data['webTitle'] ?? '';
                $published = $data['pub_date'] ?? $data['publishedAt'] ?? $data['webPublicationDate'] ?? '';

                if ($title && $published) {
                    return "{$service}_" . md5($title . $published);
                }

                return "{$service}_" . uniqid();
            }
        ];

        foreach ($strategies as $strategy) {
            if ($externalId = $strategy($articleData)) {
                return $externalId;
            }
        }

        return "{$service}_" . uniqid();
    }

    /**
     * Extract URL consistently across services
     */
    public static function extractUrl(array $articleData): ?string
    {
        return $articleData['web_url'] ?? $articleData['url'] ?? $articleData['webUrl'] ?? null;
    }

    /**
     * Extract title consistently across services
     */
    public static function extractTitle(array $articleData): string
    {
        $title = $articleData['headline']['main'] ?? $articleData['title'] ?? $articleData['webTitle'] ?? '';
        return $title ?: 'No Title';
    }
}
