<?php

use App\Emulator\Drivers\Arcturus\ArcturusInstaller;

/**
 * Collations that only one server implements are the difference between an
 * install that works everywhere and one that dies part-way through, leaving a
 * half-built database behind.
 */
function readDumpStatements(string $sql): array
{
    $path = tempnam(sys_get_temp_dir(), 'atom') . '.sql.gz';
    file_put_contents("compress.zlib://{$path}", $sql);

    try {
        $read = new ReflectionMethod(ArcturusInstaller::class, 'readStatements');

        return iterator_to_array($read->invoke(new ArcturusInstaller, $path));
    } finally {
        @unlink($path);
    }
}

test('mysql 8 only collations are rewritten to ones every server has', function () {
    // utf8mb4_0900_ai_ci exists on MySQL 8 and nowhere else: MySQL 5.7 and
    // MariaDB before 11.5 reject it with "Unknown collation" mid-import.
    $statements = readDumpStatements(
        "CREATE TABLE `a` (`id` int) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;\n"
        . "CREATE TABLE `b` (`id` int) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_cs;\n",
    );

    expect($statements)->toHaveCount(2)
        ->and($statements[0])->toContain('utf8mb4_general_ci')
        ->and($statements[1])->toContain('utf8mb4_bin')
        ->and(implode('', $statements))->not->toContain('utf8mb4_0900');
});

test('collations the dump already uses are left alone', function () {
    $statements = readDumpStatements(
        "CREATE TABLE `a` (`id` int) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n"
        . "CREATE TABLE `b` (`id` int) DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;\n",
    );

    expect($statements[0])->toContain('utf8mb4_unicode_ci')
        ->and($statements[1])->toContain('latin1_swedish_ci');
});

test('the bundled dump imports without a collation no server outside mysql 8 has', function () {
    $path = database_path('arcturus/BaseDB-MS-3.5.5.sql.gz');

    expect($path)->toBeReadableFile();

    // The shipped dump was taken from MySQL 8 and does carry the collation,
    // which is exactly why the installer normalises on the way through.
    expect(gzdecode((string) file_get_contents($path)))->toContain('utf8mb4_0900_ai_ci');

    $read = new ReflectionMethod(ArcturusInstaller::class, 'readStatements');
    $offending = 0;

    foreach ($read->invoke(new ArcturusInstaller, $path) as $statement) {
        if (str_contains($statement, 'utf8mb4_0900')) {
            $offending++;
        }
    }

    expect($offending)->toBe(0);
});
