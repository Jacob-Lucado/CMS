<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ada declares both of these columns as longtext with no index, but Atom looks
 * them up on the hot path: the ban middleware matches an address on every
 * request, and every badge grant resolves a code. Add prefix indexes so those
 * lookups stop scanning the table.
 *
 * Indexes are additive - Ada's own migrations do not read them back - and are
 * named apart from the ix_ prefix EF generates so the two never collide.
 */
return new class extends Migration
{
    /** @var array<string, array{table: string, column: string, length: int}> */
    private const INDEXES = [
        'atom_banned_ip_addresses_ip_address_index' => [
            'table' => 'banned_ip_addresses',
            'column' => 'ip_address',
            // Long enough for a full IPv6 address.
            'length' => 45,
        ],
        'atom_badges_code_index' => [
            'table' => 'badges',
            'column' => 'code',
            'length' => 64,
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $name => $index) {
            if (! Schema::hasTable($index['table']) || $this->indexExists($index['table'], $name)) {
                continue;
            }

            DB::statement(sprintf(
                'CREATE INDEX `%s` ON `%s` (`%s`(%d))',
                $name,
                $index['table'],
                $index['column'],
                $index['length'],
            ));
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $name => $index) {
            if (Schema::hasTable($index['table']) && $this->indexExists($index['table'], $name)) {
                DB::statement(sprintf('DROP INDEX `%s` ON `%s`', $name, $index['table']));
            }
        }
    }

    private function indexExists(string $table, string $name): bool
    {
        return DB::selectOne(
            'SELECT 1 AS `exists` FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?
             LIMIT 1',
            [Schema::getConnection()->getTablePrefix() . $table, $name],
        ) !== null;
    }
};
