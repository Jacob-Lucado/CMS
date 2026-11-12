<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stands in for Ada's EF migrations under test, the way the Arcturus core SQL
 * file stands in for its base database. On a real hotel Ada creates its own
 * schema before Atom is installed, so this only ever runs while testing.
 *
 * The dump is taken structure-only from an EF-migrated database, which keeps
 * the tests honest about column types, nullability and - importantly - which
 * constraints Ada does and does not declare.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! app()->environment('testing') || Schema::hasTable('players')) {
            return;
        }

        $path = database_path('ada/schema.sql');

        if (! is_readable($path)) {
            throw new RuntimeException('Unable to read the Ada schema.');
        }

        // The dump declares foreign keys in table order, so a table can
        // reference one that has not been created yet.
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        try {
            $file = new SplFileObject($path);
            $statement = '';

            foreach ($file as $line) {
                if (! is_string($line)) {
                    continue;
                }

                $statement .= $line;

                if (! str_ends_with(rtrim($line), ';')) {
                    continue;
                }

                DB::connection()->getPdo()->exec($statement);
                $statement = '';
            }

            if (trim($statement) !== '') {
                throw new RuntimeException('The Ada schema ends with an incomplete statement.');
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }
    }

    public function down(): void
    {
        // The emulator owns this schema, so Laravel must not drop it.
    }
};
