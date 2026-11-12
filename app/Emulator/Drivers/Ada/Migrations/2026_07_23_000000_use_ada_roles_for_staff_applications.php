<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        dropForeignKeyIfExists('website_staff_applications', 'rank_id');

        DB::table('website_staff_applications')
            ->whereNotNull('rank_id')
            ->whereNotIn('rank_id', DB::table('roles')->select('id'))
            ->update(['rank_id' => null]);

        Schema::table('website_staff_applications', function (Blueprint $table): void {
            $table->foreign('rank_id', 'website_staff_applications_rank_id_foreign')
                ->references('id')
                ->on('roles')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        dropForeignKeyIfExists('website_staff_applications', 'rank_id');

        DB::table('website_staff_applications')
            ->whereNotNull('rank_id')
            ->whereNotIn('rank_id', DB::table('permissions')->select('id'))
            ->update(['rank_id' => null]);

        Schema::table('website_staff_applications', function (Blueprint $table): void {
            $table->foreign('rank_id', 'website_staff_applications_rank_id_foreign')
                ->references('id')
                ->on('permissions')
                ->cascadeOnDelete();
        });
    }
};
