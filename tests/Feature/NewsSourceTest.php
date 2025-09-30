<?php

use App\Models\NewsSource;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Source Model', function () {
    it('can create news source with factory', function () {
        $source = NewsSource::factory()->create();

        expect($source->id)->toBeInt();
        expect($source->name)->toBeString();
        expect($source->slug)->toBeString();
        expect($source->api_service)->toBeIn(['newsapi', 'guardian', 'nytimes']);
    });

    it('can create active source', function () {
        $source = NewsSource::factory()->active()->create();

        expect($source->is_active)->toBeTrue();
    });

    it('can create the 3 selected service sources', function () {
        $newsApiSource = NewsSource::factory()->newsapi()->create();
        $guardianSource = NewsSource::factory()->guardian()->create();
        $nyTimesSource = NewsSource::factory()->nytimes()->create();

        expect($newsApiSource->api_service)->toBe('newsapi');
        expect($guardianSource->api_service)->toBe('guardian');
        expect($nyTimesSource->api_service)->toBe('nytimes');
        expect($newsApiSource->slug)->toBe('newsapi');
        expect($guardianSource->slug)->toBe('guardian');
        expect($nyTimesSource->slug)->toBe('nytimes');
    });

    it('has config as array', function () {
        $source = NewsSource::factory()->create();

        expect($source->config)->toBeArray();
    });
});
