<x-app-layout>
    @push('title', __('Welcome'))
    @inject('onlineUsers', 'App\Services\User\UserApiService')

    @php
        $online = $onlineUsers->onlineUserCount();
        $backdrop = filled(setting('cms_me_backdrop')) ? "--los-backdrop: url('" . setting('cms_me_backdrop') . "')" : '';
        $featured = $articles->first();
        $others = $articles->slice(1);
    @endphp

    <div class="col-span-12 flex flex-col gap-6">
        @guest
            <section class="lc-hero" style="{{ $backdrop }}">
                <div class="lc-hero-copy">
                    <span class="los-pill"><i class="los-dot"></i>{{ trans_choice(':count player online|:count players online', $online) }}</span>
                    <h1>{{ __('Welcome to :hotel', ['hotel' => setting('hotel_name')]) }}</h1>
                    <p>{{ __('Build rooms, make friends and hang out. Sign up in a minute and jump straight in from your browser.') }}</p>

                    <div class="lc-hero-actions">
                        <a href="{{ route('register') }}" class="los-btn">{{ __('Create an account') }}</a>
                        <x-los.discord-button />
                    </div>
                </div>

                <div class="lucado-signin">
                    <h2>{{ __('Sign in') }}</h2>

                    <form class="flex flex-col gap-y-3" action="{{ route('login') }}" method="POST">
                        @csrf

                        <div>
                            <x-form.label for="username">{{ __('Username') }}</x-form.label>
                            <x-form.input error-bag="login" name="username" value="{{ old('username') }}" placeholder="{{ __('Username') }}" />
                        </div>

                        <div>
                            <x-form.label for="password">{{ __('Password') }}</x-form.label>
                            <x-form.input error-bag="login" name="password" placeholder="{{ __('Password') }}" type="password" />
                        </div>

                        @if (setting('google_recaptcha_enabled'))
                            <div class="g-recaptcha" data-sitekey="{{ config('habbo.site.recaptcha_site_key') }}"></div>
                        @endif

                        @if (setting('cloudflare_turnstile_enabled'))
                            <x-turnstile />
                        @endif

                        <button type="submit" class="los-btn full">{{ __('Sign in') }}</button>
                    </form>

                    <div class="los-signin-links">
                        <a href="{{ route('forgot.password.get') }}">{{ __('Forgot your password?') }}</a>
                    </div>
                </div>
            </section>

            <section class="lc-features">
                <div class="lc-feature">
                    <span aria-hidden="true">🏠</span>
                    <h3>{{ __('Build your rooms') }}</h3>
                    <p>{{ __('Decorate with thousands of furni and show your place off.') }}</p>
                </div>
                <div class="lc-feature">
                    <span aria-hidden="true">👋</span>
                    <h3>{{ __('Make friends') }}</h3>
                    <p>{{ __('Chat, trade and hang out with players from everywhere.') }}</p>
                </div>
                <div class="lc-feature">
                    <span aria-hidden="true">🎉</span>
                    <h3>{{ __('Join the events') }}</h3>
                    <p>{{ __('Games, competitions and prizes run all the time.') }}</p>
                </div>
            </section>
        @else
            @php($player = auth()->user())
            <section class="lc-hero lc-hero-back" style="{{ $backdrop }}">
                <div class="lc-hero-copy">
                    <span class="los-pill"><i class="los-dot"></i>{{ trans_choice(':count player online|:count players online', $online) }}</span>
                    <h1>{{ __('Welcome back, :name', ['name' => $player->username]) }}</h1>
                    <p>{{ $player->motto ?: __('Good to see you again.') }}</p>

                    <div class="lc-hero-actions">
                        <a href="{{ route('nitro-client') }}" class="los-btn" data-turbolinks="false">{{ __('Enter the hotel') }}</a>
                        <a href="{{ route('me.show') }}" class="los-btn gray">{{ __('My profile') }}</a>
                    </div>
                </div>

                <img class="lc-hero-avatar los-avatar-live" alt=""
                     src="{{ setting('avatar_imager') }}{{ $player->look }}&direction=2&head_direction=3&gesture=sml&action=wav&size=l">
            </section>
        @endguest

        <section class="lc-news">
            <header class="lc-news-head">
                <div>
                    <h2>{{ __('Latest news') }}</h2>
                    <p>{{ __('What is new around the hotel.') }}</p>
                </div>
                <a href="{{ route('article.index') }}" class="los-btn gray">{{ __('All news') }}</a>
            </header>

            @if ($featured)
                <div class="lc-news-grid">
                    <a class="lc-news-main" href="{{ route('article.show', $featured->slug) }}">
                        <span class="lc-news-cover" @if (filled($featured->image)) style="background-image: url('/storage/{{ $featured->image }}')" @endif></span>
                        <span class="lc-news-body">
                            <small>{{ $featured->user?->username }} · {{ $featured->created_at?->diffForHumans() }}</small>
                            <strong>{{ $featured->title }}</strong>
                            <span>{{ \Illuminate\Support\Str::limit(strip_tags((string) $featured->short_story), 140) }}</span>
                        </span>
                    </a>

                    <div class="lc-news-list">
                        @forelse ($others as $article)
                            <a class="lc-news-row" href="{{ route('article.show', $article->slug) }}">
                                <span class="lc-news-thumb" @if (filled($article->image)) style="background-image: url('/storage/{{ $article->image }}')" @endif></span>
                                <span>
                                    <strong>{{ $article->title }}</strong>
                                    <small>{{ $article->user?->username }} · {{ $article->created_at?->diffForHumans() }}</small>
                                </span>
                            </a>
                        @empty
                            <p class="los-empty">{{ __('That is all the news for now.') }}</p>
                        @endforelse
                    </div>
                </div>
            @else
                <p class="los-empty">{{ __('No news yet. Check back soon.') }}</p>
            @endif
        </section>

        @if ($photos->isNotEmpty())
            <section class="los-card">
                <header class="los-card-head">
                    <span class="los-head-emoji" aria-hidden="true">📸</span>
                    <div>
                        <h2 class="los-card-title">{{ __('Latest photos') }}</h2>
                        <p class="los-card-sub">{{ __('Moments players caught around the hotel.') }}</p>
                    </div>
                </header>
                <div class="los-card-body">
                    <x-photos :photos="$photos" />
                </div>
            </section>
            <x-fancybox-assets />
        @endif
    </div>
</x-app-layout>

@auth
    @push('javascript')
        <script>
            document.querySelectorAll('img.los-avatar-live').forEach((img) => {
                const base = img.getAttribute('src');
                const frames = [0, 1].map((n) => base + '&frame_num=' + n);
                let ready = 0;
                frames.forEach((src) => {
                    const probe = new Image();
                    probe.onload = () => { ready++; };
                    probe.src = src;
                });
                let at = 0;
                setInterval(() => {
                    if (ready === frames.length) {
                        at = 1 - at;
                        img.src = frames[at];
                    }
                }, 450);
            });
        </script>
    @endpush
@endauth
