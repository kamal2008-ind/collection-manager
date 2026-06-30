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
        Schema::create('book_shares', function (Blueprint $table) {
            $table->id();

            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shared_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shared_with_user_id')->constrained('users')->cascadeOnDelete();

            $table->string('permission')->default('view');
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            $table->unique(['book_id', 'shared_with_user_id'], 'book_share_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_shares');
    }
};
