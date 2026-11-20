<?php

namespace App\Services;

use App\Models\Miscellaneous\WebsiteSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SettingsService
{
    /** @var Collection<string, mixed>|null */
    private ?Collection $cachedSettings = null;

    public function __construct(private readonly InstallationService $installation) {}

    /** @return Collection<string, mixed> */
    protected function settings(): Collection
    {
        if ($this->cachedSettings !== null) {
            return $this->cachedSettings;
        }

        if ($this->isInstallationIncomplete()) {
            $this->cachedSettings = $this->fetchSettings();

            return $this->cachedSettings;
        }

        $this->cachedSettings = collect(Cache::rememberForever('website_settings', function () {
            return $this->fetchSettings()->toArray();
        }));

        return $this->cachedSettings;
    }

    /**
     * @template TDefault
     *
     * @param  TDefault  $default
     *
     * @return string|TDefault
     */
    public function getOrDefault(string $key, mixed $default = null): mixed
    {
        if ($key === 'theme') {
            $base = self::pinnedTheme() ?? $this->settings()->get($key, $default);

            return self::visitorTheme($base) ?? $base;
        }

        $value = $this->settings()->get($key, $default);

        // Badge links are built as path + code, so the folder always ends in one slash.
        if ($key === 'badges_path' && is_string($value) && $value !== '') {
            return rtrim($value, '/') . '/';
        }

        return $value;
    }

    public const THEME_COOKIE = 'site_theme';

    /**
     * The themes a visitor can switch between.
     *
     * @return list<string>
     */
    public static function switchableThemes(): array
    {
        return array_values(array_filter((array) config('theme.switchable', []), 'is_string'));
    }

    /**
     * The theme the visitor picked, when the hotel's own theme is one of the switchable ones.
     */
    public static function visitorTheme(mixed $base): ?string
    {
        $choices = self::switchableThemes();

        if (! is_string($base) || ! in_array($base, $choices, true) || ! app()->bound('request')) {
            return null;
        }

        $picked = request()->cookie(self::THEME_COOKIE);

        return is_string($picked) && in_array($picked, $choices, true) ? $picked : null;
    }

    /**
     * The theme CMS_THEME pins, when it names a theme this CMS ships.
     */
    public static function pinnedTheme(): ?string
    {
        $theme = config('theme.pinned');

        if (! is_string($theme) || preg_match('/^[a-z0-9_-]+$/', $theme) !== 1) {
            return null;
        }

        return is_dir(resource_path("themes/{$theme}")) ? $theme : null;
    }

    /**
     * Drop the persisted and memoized settings so the next read refetches.
     * Instance-level, so objects already holding the singleton see fresh
     * values instead of a stale forgotten instance.
     */
    public function refresh(): void
    {
        Cache::forget('website_settings');
        $this->cachedSettings = null;
    }

    private function isInstallationIncomplete(): bool
    {
        return ! $this->installation->isComplete();
    }

    /** @return Collection<string, mixed> */
    private function fetchSettings(): Collection
    {
        if (! Schema::hasTable('website_settings')) {
            return collect();
        }

        return WebsiteSetting::query()->pluck('value', 'key');
    }
}
