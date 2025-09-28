<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'author' => $this->author,
            'category' => $this->category,
            'url' => $this->url,
            'image_url' => $this->image_url,
            'published_at' => $this->published_at->toISOString(),
            'source' => [
                'id' => $this->source->id,
                'name' => $this->source->name,
                'slug' => $this->source->slug,
            ],
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
