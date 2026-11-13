<x-app-layout>
    @push('title', __('Welcome'))
    @inject('onlineUsers', 'App\Services\User\UserApiService')

    <div class="col-span-12 flex flex-col gap-5">
        <section class="los-hero">
            <div class="los-hero-text">
                <span class="los-pill"><i class="los-dot"></i>{{ trans_choice(':count player online|:count players online', $onlineUsers->onlineUserCount()) }}</span>
                <h1>{{ setting('hotel_name') }}</h1>
                <p>{{ __('Build rooms, make friends and hang out. Sign up in a minute and jump straight in from your browser.') }}</p>

                <div class="los-hero-actions">
                    <a href="{{ route('register') }}" class="los-btn" data-los-open>{{ __('Create an account') }}</a>
                    <x-los.discord-button />
                </div>
            </div>

            <img class="los-hero-avatar" alt=""
                 src="{{ setting('avatar_imager') }}{{ setting('start_look') ?: 'hr-100-61.hd-180-1.ch-210-66.lg-270-110.sh-305-62' }}&direction=4&head_direction=3&gesture=sml&action=wav&size=l">
        </section>

        <section class="los-card">
            <header class="los-card-head">
                <span class="los-head-emoji" aria-hidden="true">📰</span>
                <div>
                    <h2 class="los-card-title">{{ __('Latest news') }}</h2>
                    <p class="los-card-sub">{{ __('What is new around the hotel.') }}</p>
                </div>
            </header>

            <div class="los-card-body">
                <div class="los-news-grid">
                    @forelse ($articles as $article)
                        <a class="los-news" href="{{ route('article.show', $article->slug) }}" data-los-open>
                            <span class="los-news-image" style="background-image: url('/storage/{{ $article->image }}')"></span>
                            <span class="los-news-title">{{ $article->title }}</span>
                            <span class="los-news-meta">{{ $article->user?->username }} · {{ $article->created_at?->diffForHumans() }}</span>
                        </a>
                    @empty
                        <p class="los-empty">{{ __('No news yet. Check back soon.') }}</p>
                    @endforelse
                </div>
            </div>
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
