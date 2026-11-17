<?php

namespace App\Console\Commands;

use App\Models\WebsiteBadge;
use App\Services\SettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class ImportBadgeData extends Command
{
    protected $signature = 'import:badge-data {--refresh : Overwrite names and descriptions of badges that already exist}';

    protected $description = 'Import badge data from JSON file';

    private const CHUNK_SIZE = 100;

    private const BADGE_PREFIX = 'badge_desc_';

    public function __construct(
        private readonly SettingsService $settingsService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $jsonPath = $this->settingsService->getOrDefault('nitro_external_texts_file');

        if (! is_string($jsonPath)) {
            $this->validateJsonFile(null);

            return self::FAILURE;
        }

        if (! $this->validateJsonFile($jsonPath)) {
            return self::FAILURE;
        }

        try {
            $this->processBadgeData($jsonPath, (bool) $this->option('refresh'));
            $this->info('Badge data imported successfully.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Failed to import badge data. Check the logs for details.');

            return self::FAILURE;
        }
    }

    /** Badge texts say "Habbo"; this hotel says "Lucado". */
    public static function rebrand(string $text): string
    {
        return str_replace('Habbo', 'Lucado', $text);
    }

    private function validateJsonFile(?string $jsonPath): bool
    {
        if (empty($jsonPath)) {
            $this->error('The JSON file path is not configured in the website settings.');

            return false;
        }

        if (! file_exists($jsonPath)) {
            $this->error('The JSON file does not exist at the specified path: ' . $jsonPath);

            return false;
        }

        return true;
    }

    private function processBadgeData(string $jsonPath, bool $refresh): void
    {
        $document = json_decode(File::get($jsonPath), flags: JSON_THROW_ON_ERROR);

        if (! is_object($document)) {
            throw new RuntimeException('Nitro external texts must contain a JSON object.');
        }

        $jsonData = get_object_vars($document);

        // Extract badge names and descriptions
        $badgeNames = Collection::make($jsonData)
            ->filter(fn ($value, $key) => str_starts_with($key, 'badge_name_'))
            ->mapWithKeys(fn ($value, $key) => [str_replace('badge_name_', '', $key) => $value]);

        $badgeDescriptions = Collection::make($jsonData)
            ->filter(fn ($value, $key) => str_starts_with($key, self::BADGE_PREFIX))
            ->mapWithKeys(fn ($value, $key) => [str_replace(self::BADGE_PREFIX, '', $key) => $value]);

        // Combine badge names and descriptions
        $badgeData = $badgeNames->map(fn ($name, $key) => [
            'badge_key' => $key, // Use only the badge name (e.g., 14X12, 14XR1)
            'badge_name' => self::rebrand((string) $name),
            'badge_description' => self::rebrand((string) $badgeDescriptions->get($key, 'No description available')),
        ])->values();

        $badgeData = $badgeData->merge($this->undocumentedBadges($badgeData->pluck('badge_key')))->values();

        // A later chunk can fail after earlier definitions were updated.
        // Keep the previous import intact unless every chunk succeeds.
        DB::transaction(function () use ($badgeData, $refresh): void {
            $badgeData->chunk(self::CHUNK_SIZE)->each(function ($chunk) use ($refresh) {
                if ($refresh) {
                    WebsiteBadge::upsert($chunk->toArray(), ['badge_key'], ['badge_name', 'badge_description']);
                } else {
                    // Keep names and descriptions that were edited in housekeeping.
                    $now = now();
                    WebsiteBadge::insertOrIgnore($chunk->map(fn (array $row) => $row + ['created_at' => $now, 'updated_at' => $now])->all());
                }

                $this->info('Processed ' . $chunk->count() . ' badges.');
            });
        });
    }

    /**
     * Badges that exist in the game (definitions table or image files) but have no text yet.
     *
     * @param  Collection<int, string>  $known
     * @return Collection<int, array{badge_key: string, badge_name: string, badge_description: string}>
     */
    private function undocumentedBadges(Collection $known): Collection
    {
        $codes = Collection::make();

        if (Schema::hasTable('badge_definitions')) {
            $codes = $codes->merge(DB::table('badge_definitions')->pluck('code'));
        }

        $directory = $this->settingsService->getOrDefault('badge_path_filesystem');
        if (is_string($directory) && is_dir($directory)) {
            $codes = $codes->merge(array_map(fn (string $file) => basename($file, '.gif'), glob(rtrim($directory, '/') . '/*.gif') ?: []));
        }

        return $codes
            ->map(fn ($code) => trim((string) $code))
            ->filter(fn (string $code) => $code !== '' && strlen($code) <= 255 && ! preg_match('/^\d+_[0-9A-Z]{26}$/', $code))
            ->unique()
            ->diff($known)
            ->map(fn (string $code) => ['badge_key' => $code, 'badge_name' => $code, 'badge_description' => 'No description available'])
            ->values();
    }
}
