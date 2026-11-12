<?php

namespace App\Emulator\Drivers\Ada;

use App\Emulator\Contracts\EmulatorInstaller;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\pause;
use function Laravel\Prompts\warning;

/**
 * Ada builds its own schema through Entity Framework migrations the first time
 * it starts, so there is nothing for Atom to import - only a check that the
 * database it has been pointed at is the one Ada already set up.
 */
class AdaInstaller implements EmulatorInstaller
{
    /**
     * Tables Ada creates and no other emulator does. Checking a spread rather
     * than one marker catches a database left half-built by an interrupted
     * first run, not just an empty one.
     *
     * @var list<string>
     */
    private const REQUIRED_TABLES = [
        'players',
        'player_data',
        'player_avatar_data',
        'player_website_data',
        'player_game_settings',
        'player_navigator_settings',
        'player_role',
        'player_badges',
        'roles',
        'badges',
    ];

    /** A table only another emulator creates. */
    private const FOREIGN_MARKER_TABLE = 'emulator_settings';

    public function prepare(Command $command): bool
    {
        if (Schema::hasTable(self::FOREIGN_MARKER_TABLE)) {
            error('This database already belongs to another emulator. Point Atom and Ada at a database of their own.');

            return false;
        }

        while (($missing = $this->missingTables()) !== []) {
            $this->explain($missing);

            if ((bool) $command->option('no-interaction')) {
                return false;
            }

            pause('Start Ada, wait for it to finish, then press enter to check again.');
        }

        info('Ada schema found.');

        return true;
    }

    /** @return list<string> */
    private function missingTables(): array
    {
        return array_values(array_filter(
            self::REQUIRED_TABLES,
            fn (string $table): bool => ! Schema::hasTable($table),
        ));
    }

    /** @param list<string> $missing */
    private function explain(array $missing): void
    {
        $found = count(self::REQUIRED_TABLES) - count($missing);

        warning(sprintf(
            'Ada has not built its schema in this database yet (%d of %d tables found).',
            $found,
            count(self::REQUIRED_TABLES),
        ));

        note(
            'Ada creates its tables through Entity Framework migrations the first time it runs.' . PHP_EOL
            . 'Point Ada at this same database and start it once, then continue here.' . PHP_EOL
            . PHP_EOL
            . 'Missing: ' . implode(', ', array_slice($missing, 0, 5))
            . (count($missing) > 5 ? sprintf(' and %d more', count($missing) - 5) : ''),
        );
    }
}
