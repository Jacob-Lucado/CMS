<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Photo ids can be UUIDs, so likes and comments keep their target as text.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['website_social_comments', 'website_social_likes'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->string('target_id', 36)->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['website_social_comments', 'website_social_likes'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->unsignedBigInteger('target_id')->change();
            });
        }
    }
};
