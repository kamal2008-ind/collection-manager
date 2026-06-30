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
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('slug');
            $table->string('author')->nullable();

            $table->unsignedSmallInteger('year')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('isbn')->nullable();
            $table->string('publisher')->nullable();
            $table->enum('visibility', ['private', 'public'])->default('private');
            $table->boolean('is_favorite')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'visibility']);
            $table->index(['user_id', 'is_favorite']);
            $table->index(['user_id', 'title']);
            $table->index('isbn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
