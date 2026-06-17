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
        Schema::create('attachments', function (Blueprint $table) {

            $table->id();

            $table->string('container_type', 50);
            $table->unsignedBigInteger('container_id');

            $table->string('attachable_type', 50);
            $table->unsignedBigInteger('attachable_id');

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->timestamps();

            $table->unique(
                [
                    'container_type',
                    'container_id',
                    'attachable_type',
                    'attachable_id'
                ],
                'attachments_unique'
            );

            $table->index(
                ['container_type', 'container_id'],
                'container_idx'
            );

            $table->index(
                ['attachable_type', 'attachable_id'],
                'attachable_idx'
            );
        });

        /*
            container_type:
                workspace
                collection

            attachable_type:
                collection
                movie
                book
                music
                website
                shopping_asset
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
