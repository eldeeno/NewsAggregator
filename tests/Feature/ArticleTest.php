<?php

use App\Models\Article;
use App\Models\NewsSource;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Articles', function () {
    beforeEach(function () {
        $this->source = NewsSource::factory()->create();
        $this->articles = Article::factory()->count(10)->create([
            'source_id' => $this->source->id
        ]);
    });

    it('can list articles with pagination', function () {
        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total']
            ]);
    });

    it('can search articles by keyword', function () {
        $article = $this->articles->first();

        $response = $this->getJson("/api/articles?search={$article->title}");

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => $article->title]);
    });

    it('can filter articles by category', function () {
        $article = Article::factory()->create([
            'source_id' => $this->source->id,
            'category' => 'technology'
        ]);

        $response = $this->getJson('/api/articles?category=technology');

        $response->assertStatus(200)
            ->assertJsonFragment(['category' => 'technology']);
    });

    it('can show individual article', function () {
        $article = $this->articles->first();

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'title', 'content', 'source']
            ]);
    });

    it('applies user preferences when authenticated', function () {
        $user = User::factory()->create();
        $user->preferences()->create([
            'preferred_sources' => [$this->source->id],
            'preferred_categories' => ['technology']
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200);
        // Add more specific assertions based on your preference logic
    });
});
