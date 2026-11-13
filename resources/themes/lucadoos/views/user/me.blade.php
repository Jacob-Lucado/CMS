<x-app-layout>
    @push('title', $user->username)
    @php
        $rankName = $user->permission?->name ?? $user->permission?->rank_name;
        $discord = $user->discordAccount;
        $referralsDone = (int) ($user->referrals->referrals_total ?? 0);
        $referralsNeeded = max(1, (int) setting('referrals_needed'));
    @endphp

    <div class="col-span-12 flex flex-col gap-5">
        <section class="los-profile">
            <img class="los-profile-avatar" alt=""
                 src="{{ setting('avatar_imager') }}{{ $user->look }}&direction=2&head_direction=3&gesture=sml&action=wav&size=l">

            <div class="los-profile-text">
                <span class="los-pill">{{ $rankName ?: __('Player') }}</span>
                <h1>{{ $user->username }}</h1>
                <p>{{ $user->motto ?: __('No motto yet.') }}</p>

                <div class="los-hero-actions">
                    <a href="{{ route('nitro-client') }}" class="los-btn" data-los-open data-turbolinks="false">{{ __('Enter the hotel') }}</a>
                    <a href="{{ route('home.show', $user) }}" class="los-btn gray" data-los-open>{{ __('My home page') }}</a>
                    <a href="{{ route('settings.account.show') }}" class="los-btn gray" data-los-open>{{ __('Settings') }}</a>
                </div>
            </div>
        </section>

        <div class="los-stats">
            <div class="los-stat"><img src="{{ asset('/assets/images/icons/credits.png') }}" alt=""><span>{{ __('Credits') }}</span><strong>{{ number_format((int) $user->credits) }}</strong></div>
            <div class="los-stat"><img src="{{ asset('/assets/images/icons/duckets.png') }}" alt=""><span>{{ __('Duckets') }}</span><strong>{{ number_format($user->currency('duckets')) }}</strong></div>
            <div class="los-stat"><img src="{{ asset('/assets/images/icons/diamond.png') }}" alt=""><span>{{ __('Diamonds') }}</span><strong>{{ number_format($user->currency('diamonds')) }}</strong></div>
            <div class="los-stat"><img src="{{ asset('/assets/images/icons/online-friends.png') }}" alt=""><span>{{ __('Friends online') }}</span><strong>{{ $onlineFriends->count() }}</strong></div>
        </div>

        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-12 flex flex-col gap-5 lg:col-span-8">
                <section class="los-card">
                    <header class="los-card-head">
                        <span class="los-head-emoji" aria-hidden="true">👥</span>
                        <div>
                            <h2 class="los-card-title">{{ __('Friends online') }}</h2>
                            <p class="los-card-sub">{{ __('Who is in the hotel right now.') }}</p>
                        </div>
                    </header>
                    <div class="los-card-body">
                        <div class="los-friends">
                            @forelse ($onlineFriends as $friend)
                                <a class="los-friend" href="{{ route('home.show', $friend) }}" data-los-open title="{{ $friend->motto }}">
                                    <img alt="" src="{{ setting('avatar_imager') }}{{ $friend->look }}&direction=2&head_direction=3&gesture=sml&headonly=1&size=m">
                                    <span>{{ $friend->username }}</span>
                                </a>
                            @empty
                                <p class="los-empty">{{ __('None of your friends are online. Make some in the hotel!') }}</p>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="los-card">
                    <header class="los-card-head">
                        <span class="los-head-emoji" aria-hidden="true">🎁</span>
                        <div>
                            <h2 class="los-card-title">{{ __('Invite friends') }}</h2>
                            <p class="los-card-sub">{{ __('Get :amount diamonds for every :needed players who join with your link.', ['amount' => setting('referral_reward_amount'), 'needed' => $referralsNeeded]) }}</p>
                        </div>
                    </header>
                    <div class="los-card-body gap-3">
                        <div class="los-copy">
                            <input id="referral" type="text" readonly value="{{ sprintf('%s/register/%s', config('habbo.site.site_url'), $user->referral_code) }}">
                            <button type="button" class="los-btn gray" onclick="losCopyReferral()">{{ __('Copy') }}</button>
                        </div>
                        <div class="los-progress" role="progressbar" aria-valuenow="{{ min($referralsDone, $referralsNeeded) }}" aria-valuemax="{{ $referralsNeeded }}">
                            <span style="width: {{ min(100, round($referralsDone / $referralsNeeded * 100)) }}%"></span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="los-card-sub">{{ __(':done of :needed players joined', ['done' => $referralsDone, 'needed' => $referralsNeeded]) }}</span>
                            @if ($referralsDone >= $referralsNeeded)
                                <form method="POST" action="{{ route('claim.referral-reward') }}">
                                    @csrf
                                    <button class="los-btn">{{ __('Claim reward') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-span-12 flex flex-col gap-5 lg:col-span-4">
                <section class="los-card">
                    <header class="los-card-head">
                        <span class="los-head-emoji" aria-hidden="true">💬</span>
                        <div>
                            <h2 class="los-card-title">{{ __('Discord') }}</h2>
                            <p class="los-card-sub">{{ $discord ? __('Linked to your account.') : __('Sign in with Discord next time.') }}</p>
                        </div>
                    </header>
                    <div class="los-card-body gap-3">
                        @if ($discord)
                            <div class="los-linking">
                                @if ($discord->avatarUrl())
                                    <img src="{{ $discord->avatarUrl() }}" alt="">
                                @endif
                                <div>
                                    <strong>{{ $discord->discord_username }}</strong>
                                    <span>{{ __('Linked :when', ['when' => $discord->created_at?->diffForHumans()]) }}</span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('settings.discord.destroy') }}">
                                @csrf
                                @method('DELETE')
                                <button class="los-btn gray full">{{ __('Unlink Discord') }}</button>
                            </form>
                        @else
                            <x-los.discord-button :label="__('Link Discord')" />
                            @inject('discordLogin', 'App\Services\Auth\DiscordLogin')
                            @unless ($discordLogin->isEnabled())
                                <p class="los-empty">{{ __('Discord linking is not switched on yet.') }}</p>
                            @endunless
                        @endif
                    </div>
                </section>

                <section class="los-card">
                    <header class="los-card-head">
                        <span class="los-head-emoji" aria-hidden="true">📰</span>
                        <div>
                            <h2 class="los-card-title">{{ __('Latest news') }}</h2>
                        </div>
                    </header>
                    <div class="los-card-body">
                        <div class="los-list">
                            @forelse ($articles as $article)
                                <a href="{{ route('article.show', $article->slug) }}" data-los-open>
                                    <span class="los-news-thumb" style="background-image: url('/storage/{{ $article->image }}')"></span>
                                    <span>
                                        <strong>{{ $article->title }}</strong>
                                        <small>{{ $article->created_at?->diffForHumans() }}</small>
                                    </span>
                                </a>
                            @empty
                                <p class="los-empty">{{ __('No news yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @push('javascript')
        <script>
            function losCopyReferral() {
                const field = document.getElementById('referral');
                navigator.clipboard?.writeText(field.value).catch(() => {
                    field.select();
                    document.execCommand('copy');
                });
                window.toast?.(@js(__('Your invite link is copied.')));
            }
        </script>
    @endpush
</x-app-layout>
