<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleSearchRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\NewsSource;
use App\Models\Source;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    public function index(ArticleSearchRequest $request): JsonResponse
    {
        $query = Article::with('source')->latest('published_at');

        $this->searchWithFilter($query, $request);

        $this->searchWithUserPreferences($query, $request);

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

    private function searchWithFilter($query, ArticleSearchRequest $request): void
    {
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        if ($request->has('category') && $request->category) {
            $query->byCategory($request->category);
        }

        if ($request->has('source') && $request->source) {
            $query->bySource($request->source);
        }

        if ($request->has('author') && $request->author) {
            $query->byAuthor($request->author);
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->publishedBetween(
                $request->from_date,
                $request->to_date ?? now()->toDateString()
            );
        }
    }

    private function searchWithUserPreferences($query, ArticleSearchRequest $request): void
    {
        if (!$request->user() || !$request->user()->preferences) {
            return;
        }

        $preferences = $request->user()->preferences;
        $hasExplicitFilters = $request->hasAny(['search', 'category', 'source', 'author']);

        if (!$hasExplicitFilters) {
            if (!empty($preferences->preferred_sources)) {
                $query->bySource($preferences->preferred_sources);
            }

            if (!empty($preferences->preferred_categories)) {
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
     * Get available filter options (sources, categories, authors)
     */
    public function filters(): JsonResponse
    {
        $filters = [
            'sources' => NewsSource::where('is_active', true)
                ->get(['id', 'name', 'slug'])
                ->toArray(),
            'categories' => Article::distinct()
                ->whereNotNull('category')
                ->pluck('category')
                ->filter()
                ->values()
                ->toArray(),
            'authors' => Article::distinct()
                ->whereNotNull('author')
                ->where('author', '!=', '')
                ->pluck('author')
                ->filter()
                ->values()
                ->toArray(),
        ];

        return $this->successResponse($filters, 'Available filters retrieved');
    }
}
