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
        Schema::create('workspace_shares', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workspace_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('shared_by_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('shared_with_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('permission')->default('view');

            $table->timestamp('last_accessed_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['workspace_id', 'shared_with_user_id'],
                'workspace_share_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspace_shares');
    }
};
