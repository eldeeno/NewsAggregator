<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleSearchRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\NewsSource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    public function index(ArticleSearchRequest $request): JsonResponse
    {
        $query = Article::with('source')->latest('published_at');

        $this->applySearchFilters($query, $request);
        $this->applyUserPreferences($query, $request);

        $perPage = $request->per_page ?? 20;
        $articles = $query->paginate($perPage);

        return $this->paginatedResponse(
            ArticleResource::collection($articles),
            'Articles retrieved successfully'
        );
    }

    public function show(Article $article): JsonResponse
    {
        $article->load('source');

        return $this->successResponse(
            new ArticleResource($article),
            'Article retrieved successfully'
        );
    }

    private function applySearchFilters($query, ArticleSearchRequest $request): void
    {
        // Search term
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Category filter - support single category or comma-separated multiple categories
        if ($request->filled('category')) {
            $categories = $this->parseCategories($request->category);
            if (count($categories) > 1) {
                $query->byCategories($categories);
            } else {
                $query->byCategory($categories[0]);
            }
        }

        // Source filter
        if ($request->filled('source')) {
            $sources = is_array($request->source) ? $request->source : [$request->source];
            $query->bySource($sources);
        }

        // Author filter
        if ($request->filled('author')) {
            $query->byAuthor($request->author);
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $toDate = $request->to_date ?? now()->toDateString();
            $query->publishedBetween($request->from_date, $toDate);
        }
    }

    /**
     * Parse categories from request - support array, comma-separated, or single value
     */
    private function parseCategories($categoryInput): array
    {
        if (is_array($categoryInput)) {
            return $categoryInput;
        }

        if (str_contains($categoryInput, ',')) {
            return array_map('trim', explode(',', $categoryInput));
        }

        return [trim($categoryInput)];
    }

    private function applyUserPreferences($query, ArticleSearchRequest $request): void
    {
        if (!$request->user() || !$request->user()->preferences) {
            return;
        }

        $preferences = $request->user()->preferences;
        $hasExplicitFilters = $request->hasAny(['search', 'category', 'source', 'author', 'from_date']);

        if (!$hasExplicitFilters) {
            if (!empty($preferences->preferred_sources)) {
                $query->bySource($preferences->preferred_sources);
            }

            if (!empty($preferences->preferred_categories)) {
                Log::info(123);
                $query->whereIn('category', $preferences->preferred_categories);
            }

            if (!empty($preferences->preferred_authors)) {
                $query->where(function ($q) use ($preferences) {
                    foreach ($preferences->preferred_authors as $author) {
                        $q->orWhere('author', 'like', "%{$author}%");
                    }
                });
            }
        }
    }

    /**
     * Get available filter options
     */
    public function filters(): JsonResponse
    {
        $filters = [
            'sources' => NewsSource::where('is_active', true)
                ->get(['id', 'name', 'slug'])
                ->toArray(),
            'categories' => Article::distinct()
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->orderBy('category')
                ->pluck('category')
                ->values()
                ->toArray(),
            'authors' => Article::distinct()
                ->whereNotNull('author')
                ->where('author', '!=', '')
                ->orderBy('author')
                ->pluck('author')
                ->values()
                ->toArray(),
        ];

        return $this->successResponse($filters, 'Available filters retrieved');
    }
}
