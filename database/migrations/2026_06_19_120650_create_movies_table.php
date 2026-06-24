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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('slug');

            $table->unsignedSmallInteger('year')->nullable();

            $table->text('description')->nullable();
            $table->string('poster_path')->nullable();

            $table->unsignedBigInteger('tmdb_id')->nullable();
            $table->string('imdb_id')->nullable();

            $table->enum('visibility', ['private', 'public'])->default('private');

            $table->boolean('is_favorite')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'visibility']);
            $table->index(['user_id', 'is_favorite']);
            $table->index(['user_id', 'title']);
            $table->index(['tmdb_id']);
            $table->index(['imdb_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
