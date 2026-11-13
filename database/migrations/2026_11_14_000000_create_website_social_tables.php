<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The social feed: posts, comments, likes, tags, reports, kudos and notifications.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_social_posts', function (Blueprint $table): void {
            $table->id();
            $table->playerId('user_id');
            $table->string('type', 20)->index();
            $table->text('body')->nullable();
            $table->json('data')->nullable();
            $table->unsignedBigInteger('shared_post_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignPlayer('user_id')->cascadeOnDelete();
            $table->index('created_at');
        });

        // Comments and likes land on a post or on a camera photo.
        Schema::create('website_social_comments', function (Blueprint $table): void {
            $table->id();
            $table->string('target_type', 10);
            $table->unsignedBigInteger('target_id');
            $table->playerId('user_id');
            $table->string('body', 500);
            $table->timestamps();

            $table->foreignPlayer('user_id')->cascadeOnDelete();
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('website_social_likes', function (Blueprint $table): void {
            $table->string('target_type', 10);
            $table->unsignedBigInteger('target_id');
            $table->playerId('user_id');
            $table->timestamp('created_at')->nullable();

            $table->primary(['target_type', 'target_id', 'user_id']);
            $table->foreignPlayer('user_id')->cascadeOnDelete();
        });

        Schema::create('website_social_tags', function (Blueprint $table): void {
            $table->unsignedBigInteger('post_id');
            $table->playerId('user_id');

            $table->primary(['post_id', 'user_id']);
            $table->foreignPlayer('user_id')->cascadeOnDelete();
        });

        Schema::create('website_social_reports', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('post_id')->index();
            $table->playerId('user_id');
            $table->string('reason', 300);
            $table->playerId('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->unique(['post_id', 'user_id']);
            $table->foreignPlayer('user_id')->cascadeOnDelete();
            $table->foreignPlayer('resolved_by')->nullOnDelete();
        });

        Schema::create('website_social_profiles', function (Blueprint $table): void {
            $table->playerId('user_id')->primary();
            $table->string('background', 300)->nullable();
            $table->json('muted')->nullable();
            $table->boolean('hide_from_rare_directory')->default(false);
            $table->timestamps();

            $table->foreignPlayer('user_id')->cascadeOnDelete();
        });

        Schema::create('website_kudos', function (Blueprint $table): void {
            $table->id();
            $table->playerId('from_user_id');
            $table->playerId('to_user_id');
            $table->string('board', 40);
            $table->date('given_on');
            $table->timestamps();

            $table->unique(['from_user_id', 'to_user_id', 'board', 'given_on']);
            $table->index('to_user_id');
            $table->foreignPlayer('from_user_id')->cascadeOnDelete();
            $table->foreignPlayer('to_user_id')->cascadeOnDelete();
        });

        Schema::create('website_notifications', function (Blueprint $table): void {
            $table->id();
            $table->playerId('user_id');
            $table->string('type', 40);
            $table->playerId('actor_id')->nullable();
            $table->string('message', 300);
            $table->string('url', 300)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->foreignPlayer('user_id')->cascadeOnDelete();
            $table->foreignPlayer('actor_id')->nullOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['website_notifications', 'website_kudos', 'website_social_profiles', 'website_social_reports', 'website_social_tags', 'website_social_likes', 'website_social_comments', 'website_social_posts'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
