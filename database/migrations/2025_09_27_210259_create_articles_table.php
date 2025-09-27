<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_source_id')->constrained()->onDelete('cascade');
            $table->string('external_id')->nullable();
            $table->string('title');
            $table->text('content');
            $table->text('excerpt')->nullable();
            $table->string('author')->nullable();
            $table->string('category')->nullable();
            $table->string('url');
            $table->string('image_url')->nullable();
            $table->timestamp('published_at');
            $table->timestamps();

            $table->index(['news_source_id', 'external_id']);
            $table->index(['published_at']);
            $table->index(['category']);
            $table->fullText(['title', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
