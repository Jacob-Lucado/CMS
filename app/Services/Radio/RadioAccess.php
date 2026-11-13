<?php

namespace App\Services\Radio;

use App\Emulator\Contracts\RankRepository;
use App\Models\Miscellaneous\WebsiteSetting;
use App\Models\Radio\RadioEntry;
use App\Models\User;
use App\Services\SettingsService;

/**
 * Who runs the radio. DJs are the roles picked in Housekeeping, and the hotel's top rank always counts.
 */
class RadioAccess
{
    public const MAX_PENDING_PER_PLAYER = 3;

    public function __construct(private readonly RankRepository $ranks) {}

    /** @return list<int|string> */
    public function djRoles(): array
    {
        $roles = json_decode((string) setting('radio_dj_roles', '[]'), true);

        return is_array($roles) ? array_values(array_filter($roles, fn (mixed $role): bool => is_int($role) || is_string($role))) : [];
    }

    public function isDj(?User $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        $top = $this->ranks->highestRank();

        return ($top > 0 && (int) $user->rank >= $top) || $this->ranks->holdsAny($user, $this->djRoles());
    }

    public function submissionsOpen(): bool
    {
        return (string) setting('radio_submissions', '1') === '1';
    }

    public function onAir(): ?RadioEntry
    {
        $id = (int) setting('radio_on_air', '0');

        return $id > 0 ? RadioEntry::query()->approved()->find($id) : null;
    }

    public function putOnAir(?RadioEntry $entry): void
    {
        $this->save(['radio_on_air' => (string) ($entry->id ?? 0)]);
    }

    /** @param list<int|string> $roles */
    public function configure(array $roles, bool $submissions): void
    {
        $this->save([
            'radio_dj_roles' => json_encode(array_values($roles)),
            'radio_submissions' => $submissions ? '1' : '0',
        ]);
    }

    /** @param array<string, string|false> $values */
    private function save(array $values): void
    {
        foreach ($values as $key => $value) {
            WebsiteSetting::query()->updateOrCreate(['key' => $key], ['value' => (string) $value, 'comment' => 'Hotel radio']);
        }

        app(SettingsService::class)->refresh();
    }
}
