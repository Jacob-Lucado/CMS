<?php

namespace App\Http\Controllers\Social;

use App\Emulator\Contracts\MarketplaceRepository;
use App\Emulator\Data\MarketOffer;
use App\Emulator\Data\MarketTrend;
use App\Http\Controllers\Controller;
use App\Models\Community\RareValue\WebsiteRareValue;
use App\Models\Social\SocialProfile;
use App\Models\User;
use App\Services\Community\RareValues\RareValueCategoriesService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MarketplaceController extends Controller
{
    public function __construct(private readonly MarketplaceRepository $market) {}

    public function show(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $listings = $this->market->listings(60);
        $sales = $this->market->recentSales(20);
        $sellers = User::query()->whereIn('id', $listings->concat($sales)->map(fn (MarketOffer $offer): int => $offer->sellerId)->unique()->values()->all())->pluck('username', 'id');

        return view('marketplace', [
            'config' => [
                'available' => $this->market->available(),
                'search' => route('marketplace.search', absolute: false),
                'owners' => rtrim(route('marketplace.index', absolute: false), '/') . '/owners',
                'post' => route('social.posts.store', absolute: false),
                'profile' => route('social.profile', absolute: false),
                'hidden' => (bool) SocialProfile::query()->find($user->id)?->hide_from_rare_directory,
                'listings' => $this->offers($listings, $sellers),
                'sales' => $this->offers($sales, $sellers),
                'movers' => $this->market->topMovers(10)->map(fn (MarketTrend $trend): array => [
                    'name' => $trend->name,
                    'icon' => self::icon($trend->itemName),
                    'sold' => $trend->sold,
                    'average' => $trend->averagePrice,
                ])->values(),
                'rares' => WebsiteRareValue::query()->whereNotNull('item_id')->orderBy('name')->limit(300)->get(['id', 'name', 'furniture_icon', 'credit_value'])->map(fn (WebsiteRareValue $rare): array => [
                    'id' => $rare->id,
                    'name' => $rare->name,
                    'icon' => rtrim((string) setting('furniture_icons_path'), '/') . '/' . ltrim((string) $rare->furniture_icon, '/'),
                    'value' => $rare->credit_value,
                ])->values(),
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $term = (string) ($request->validate(['q' => ['nullable', 'string', 'max:60']])['q'] ?? '');
        $listings = $this->market->listings(60, $term === '' ? null : $term);
        $sellers = User::query()->whereIn('id', $listings->map(fn (MarketOffer $offer): int => $offer->sellerId)->unique()->values()->all())->pluck('username', 'id');

        return response()->json(['listings' => $this->offers($listings, $sellers)]);
    }

    public function owners(WebsiteRareValue $rare, RareValueCategoriesService $rares): JsonResponse
    {
        return response()->json(['owners' => collect($rares->itemsPerUser($rare))
            ->filter(fn (array $row): bool => $row['user'] !== null)
            ->sortByDesc('item_count')
            ->take(50)
            ->map(fn (array $row): array => ['name' => $row['user']?->username, 'count' => $row['item_count'], 'home' => route('home.show', (string) $row['user']?->username, false)])
            ->values()]);
    }

    public static function icon(string $itemName): string
    {
        return rtrim((string) setting('furniture_icons_path'), '/') . '/' . str_replace('*', '_', $itemName) . '_icon.png';
    }

    /**
     * @param  Collection<int, MarketOffer>  $offers
     * @param  Collection<array-key, mixed>  $sellers
     *
     * @return array<int, array<string, mixed>>
     */
    private function offers(Collection $offers, Collection $sellers): array
    {
        return $offers->map(fn (MarketOffer $offer): array => [
            'id' => $offer->id,
            'name' => $offer->name,
            'icon' => self::icon($offer->itemName),
            'price' => $offer->price,
            'seller' => (string) ($sellers[$offer->sellerId] ?? '?'),
            'ago' => $offer->listedAt?->diffForHumans(),
            'ltd' => $offer->limitedStack > 0 ? $offer->limitedNumber . '/' . $offer->limitedStack : null,
        ])->values()->all();
    }
}
