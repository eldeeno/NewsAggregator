<?php

use App\Models\Article;
use App\Models\NewsSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);
describe('User Preferences', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);

        $this->sources = NewsSource::factory()->count(3)->active()->create();
    });

    it('can retrieve user preferences', function () {
        $response = $this->getJson('/api/preferences');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'preferred_sources',
                    'preferred_categories',
                    'preferred_authors',
                    'created_at',
                    'updated_at'
                ]
            ]);
    });

    it('can update user preferences', function () {
        $preferenceData = [
            'preferred_sources' => [$this->sources[0]->id, $this->sources[1]->id],
            'preferred_categories' => ['technology', 'science'],
            'preferred_authors' => ['John Doe', 'Jane Smith'],
        ];

        $response = $this->putJson('/api/preferences', $preferenceData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'preferred_sources' => $preferenceData['preferred_sources'],
                'preferred_categories' => $preferenceData['preferred_categories'],
                'preferred_authors' => $preferenceData['preferred_authors'],
            ]);
    });

    it('validates preferred sources exist', function () {
        $response = $this->putJson('/api/preferences', [
            'preferred_sources' => [999, 1000],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.status', 422)
            ->assertJsonPath('error.validation_errors.0.field', 'preferred_sources.0')
            ->assertJsonPath('error.validation_errors.1.field', 'preferred_sources.1');
    });

    it('applies preferences when fetching articles', function () {
        Article::factory()->count(5)->create([
            'news_source_id' => $this->sources[0]->id,
            'category' => 'technology'
        ]);

        $this->user->preferences()->create([
            'preferred_sources' => [$this->sources[0]->id],
            'preferred_categories' => ['technology'],
        ]);

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200);
    });

    it('ignores preferences when explicit filters are provided', function () {
        $this->user->preferences()->create([
            'preferred_sources' => [$this->sources[0]->id],
        ]);

        $response = $this->getJson("/api/articles?source[]={$this->sources[1]->id}");

        $response->assertStatus(200);
    });
});
