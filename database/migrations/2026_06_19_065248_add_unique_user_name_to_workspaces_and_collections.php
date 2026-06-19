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
        // Schema::table('workspaces', function (Blueprint $table) {
        //     $table->unique(['user_id', 'name'], 'workspaces_user_name_unique');
        // });

        Schema::table('collections', function (Blueprint $table) {
            $table->unique(['user_id', 'name'], 'collections_user_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('workspaces', function (Blueprint $table) {
        //     $table->dropUnique('workspaces_user_name_unique');
        // });

        Schema::table('collections', function (Blueprint $table) {
            $table->dropUnique('collections_user_name_unique');
        });
    }
};
