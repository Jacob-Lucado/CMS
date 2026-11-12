<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        dropForeignKeyIfExists('website_shop_articles', 'give_rank');

        DB::table('website_shop_articles')
            ->whereNotNull('give_rank')
            ->whereNotIn('give_rank', DB::table('roles')->select('id'))
            ->update(['give_rank' => null]);

        Schema::table('website_shop_articles', function (Blueprint $table): void {
            $table->foreign('give_rank', 'website_shop_articles_give_rank_foreign')
                ->references('id')
                ->on('roles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        dropForeignKeyIfExists('website_shop_articles', 'give_rank');

        DB::table('website_shop_articles')
            ->whereNotNull('give_rank')
            ->whereNotIn('give_rank', DB::table('permissions')->select('id'))
            ->update(['give_rank' => null]);

        Schema::table('website_shop_articles', function (Blueprint $table): void {
            $table->foreign('give_rank', 'website_shop_articles_give_rank_foreign')
                ->references('id')
                ->on('permissions')
                ->nullOnDelete();
        });
    }
};
