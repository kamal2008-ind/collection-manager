<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movie_shares', function (Blueprint $table) {
            $table->id();

            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shared_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shared_with_user_id')->constrained('users')->cascadeOnDelete();

            $table->string('permission')->default('view');
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            $table->unique(['movie_id', 'shared_with_user_id'], 'movie_share_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_shares');
    }
};
