<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleSearchRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    use ApiResponse;

    public function index(ArticleSearchRequest $request): JsonResponse
    {
        $query = Article::with('source');

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

        // Apply user preferences if authenticated
        if ($request->user() && $request->user()->preferences) {
            $preferences = $request->user()->preferences;

            if (!empty($preferences->preferred_sources)) {
                $query->bySource($preferences->preferred_sources);
            }

            if (!empty($preferences->preferred_categories)) {
                $query->whereIn('category', $preferences->preferred_categories);
            }
        }

        $perPage = $request->per_page ?? 20;
        $articles = $query->orderBy('published_at', 'desc')->paginate($perPage);

        return $this->paginatedResponse(ArticleResource::collection($articles), 'Articles retrieved successfully');
    }

    public function show(Article $article): JsonResponse
    {
        $article->load('source');

        return $this->successResponse(new ArticleResource($article), 'Article retrieved successfully');
    }
}
