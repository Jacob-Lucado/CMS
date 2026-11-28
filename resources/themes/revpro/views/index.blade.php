<x-app-layout>
    @push('title', __('Welcome to the best hotel on the web!'))

    @guest
        <section class="col-span-12 grid grid-cols-12 gap-6">
            <div class="revpro-feature col-span-12 lg:col-span-9">
                <div class="revpro-feature-text">
                    <img src="{{ setting('cms_logo') }}" alt="{{ setting('hotel_name') }}">
                    <strong>{{ __('Welcome to :hotel', ['hotel' => setting('hotel_name')]) }}</strong>
                    <em>{{ __('Make friends, build rooms, compete in events and have a great time!') }}</em>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-3">
                <form class="revpro-card space-y-3" action="{{ route('login') }}" method="POST">
                    @csrf

                    <div>
                        <x-form.label for="username">{{ __('Username') }}</x-form.label>
                        <x-form.input error-bag="login" name="username" value="{{ old('username') }}" placeholder="{{ __('Username') }}" />
                    </div>

                    <div>
                        <x-form.label for="password">{{ __('Password') }}</x-form.label>
                        <x-form.input error-bag="login" name="password" placeholder="########" type="password" />
                    </div>

                    @if (setting('google_recaptcha_enabled'))
                        <div class="g-recaptcha" data-sitekey="{{ config('habbo.site.recaptcha_site_key') }}"></div>
                    @endif

                    @if (setting('cloudflare_turnstile_enabled'))
                        <x-turnstile />
                    @endif

                    <button class="revpro-submit" type="submit">{{ __('Login') }}</button>

                    <a class="revpro-forgot" href="{{ route('forgot.password.get') }}">{{ __('Did you forget your password?') }}</a>
                </form>

                <div class="revpro-register">
                    <h2>{{ __('New here?') }}</h2>
                    <a data-turbolinks="false" href="{{ route('register') }}">{{ __('Register') }}</a>
                </div>
            </div>
        </section>
    @endguest

    <div class="col-span-12 space-y-6">
        @php
            $revproTiles = ['news1.gif', 'news2.gif', 'news3.gif', 'news4.gif', 'news5.gif', 'news6.gif'];
        @endphp

        <div class="revpro-news">
            @foreach ($articles as $article)
                @php
                    $revproImage = filled($article->image)
                        ? '/storage/' . $article->image
                        : '/assets/images/revpro/news/' . $revproTiles[$loop->index % 6];
                @endphp
                <a class="revpro-news-card" href="{{ route('article.show', $article->slug) }}"
                    style="background-image: url('{{ $revproImage }}')">
                    <span class="caption">
                        <strong>{{ $article->title }}</strong>
                        <em>{{ \Illuminate\Support\Str::limit(strip_tags((string) $article->short_story), 50) }}</em>
                    </span>
                </a>
            @endforeach

            @for ($i = $articles->count(); $i < 4; $i++)
                <div class="revpro-news-card" style="background-image: url('/assets/images/revpro/news/{{ $revproTiles[$i % 6] }}')">
                    <span class="caption">
                        <strong>{{ $i === 0 && $articles->isEmpty() ? __('No published articles') : setting('hotel_name') }}</strong>
                    </span>
                </div>
            @endfor
        </div>

        @if ($photos->isNotEmpty())
            <x-content.guest-content-card icon="camera-icon">
                <x-slot:title>
                    {{ __('Latest Photos') }}
                </x-slot:title>

                <x-slot:under-title>
                    {{ __('Have a look at some of the great moments captured by users around the hotel.') }}
                </x-slot:under-title>
                <x-photos :photos="$photos" />
            </x-content.guest-content-card>
        @endif
    </div>

    <x-fancybox-assets />
</x-app-layout>
