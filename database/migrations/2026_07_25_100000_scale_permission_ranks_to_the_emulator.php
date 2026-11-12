<?php

use App\Support\PermissionRanks;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Brings permission ranks inside the range the configured emulator has.
 *
 * Atom seeds its permission ladder - partly here in migrations, partly in the
 * seeders - against Arcturus, whose permissions table runs past the owner
 * tier. Ada's base roles stop at Admin, so those rows land above every rank
 * the hotel has and nobody, owner included, can reach them.
 *
 * Only rows already out of reach are touched. A permission requiring a higher
 * rank than the emulator defines is unusable by definition, so lowering it to
 * the top rank cannot widen access beyond the owner - it can only make a dead
 * permission work. Ranks an operator has deliberately set within range are
 * left exactly as they are.
 */
return new class extends Migration
{
    private const TABLES = ['website_permissions', 'website_housekeeping_permissions'];

    public function up(): void
    {
        $ceiling = PermissionRanks::ceiling();

        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->where('min_rank', '>', $ceiling)->update(['min_rank' => $ceiling]);
            }
        }
    }

    public function down(): void
    {
        // The original ranks are not recoverable, and restoring them would put
        // the permissions back out of reach.
    }
};
