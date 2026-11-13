<?php

namespace App\Filament\Forms;

use App\Emulator\Contracts\BadgeRepository;
use App\Rules\ValidBadgeCode;
use App\Support\BadgeCode;
use Filament\Forms\Components\Select;

/**
 * A searchable list of the hotel's badges with their pictures. A code that isn't listed can still be typed in.
 */
final class BadgePicker
{
    public static function make(string $name = 'badge'): Select
    {
        return Select::make($name)
            ->label(__('Badge'))
            ->placeholder(__('Search by badge code'))
            ->searchable()
            ->allowHtml()
            ->required()
            ->rules([new ValidBadgeCode])
            ->searchPrompt(__('Type part of a badge code, like ADM or ACH_'))
            ->getSearchResultsUsing(fn (string $search): array => self::options($search))
            ->getOptionLabelUsing(fn (string $value): string => self::label($value))
            ->dehydrateStateUsing(fn (?string $state): string => trim((string) $state));
    }

    /** @return array<string, string> */
    public static function options(string $search): array
    {
        $term = trim($search);
        $codes = app(BadgeRepository::class)->search($term, 40);

        if ($term !== '' && strlen($term) <= BadgeCode::MAX_LENGTH && preg_match('/^[A-Za-z0-9_-]+$/', $term) === 1 && ! in_array($term, $codes, true)) {
            array_unshift($codes, $term);
        }

        return collect($codes)->mapWithKeys(fn (string $code): array => [$code => self::label($code)])->all();
    }

    public static function label(string $code): string
    {
        $src = e(setting('badges_path') . $code . '.gif');

        return '<span style="display:inline-flex;align-items:center;gap:.6rem">'
            . '<img src="' . $src . '" alt="" loading="lazy" style="width:40px;height:40px;object-fit:contain;image-rendering:pixelated" onerror="this.style.visibility=\'hidden\'">'
            . '<span style="font-family:ui-monospace,monospace">' . e($code) . '</span></span>';
    }
}
