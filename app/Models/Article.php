<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_source_id',
        'external_id',
        'title',
        'content',
        'excerpt',
        'author',
        'category',
        'url',
        'image_url',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(NewsSource::class, 'news_source_id');
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%");
        });
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', 'like', "%{$category}%");
    }

    /**
     * Exact match category scope
     */
    public function scopeByCategoryExact(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for multiple categories (exact match)
     */
    public function scopeByCategories(Builder $query, array $categories): Builder
    {
        return $query->whereIn('category', $categories);
    }

    /**
     * Scope for multiple categories (LIKE match)
     */
    public function scopeByCategoriesLike(Builder $query, array $categories): Builder
    {
        return $query->where(function ($q) use ($categories) {
            foreach ($categories as $category) {
                $q->orWhere('category', 'like', "%{$category}%");
            }
        });
    }

    public function scopeBySource(Builder $query, array $sourceIds): Builder
    {
        return $query->whereIn('news_source_id', $sourceIds);
    }

    public function scopeByAuthor(Builder $query, string $author): Builder
    {
        return $query->where('author', 'like', "%{$author}%");
    }

    public function scopePublishedBetween(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('published_at', [$from, $to]);
    }
}
