<x-app-layout>
    @push('title', __('Welcome to the best hotel on the web!'))

    @guest
        <div class="mercury-bleed col-span-12 -mx-6 -mt-6 md:-mt-0">
            <section class="mercury-hero">
                <div class="mercury-hero-inner">
                    <div class="hidden flex-col justify-center lg:flex">
                        <img class="mercury-hero-logo self-start" src="{{ setting('cms_logo') }}" alt="{{ setting('hotel_name') }}">
                        <p class="mercury-welcome-title">{{ __('Welcome') }}</p>
                        <p class="mercury-welcome-text">
                            {{ __('An online virtual world where you can create your own avatar, make friends, chat, create rooms and much more!') }}
                        </p>
                    </div>

                    <div class="mercury-login">
                        <div class="mercury-login-title">
                            <strong>{{ __('Login area') }}</strong>
                            <span>{{ __('Sign in to play with us.') }}</span>
                        </div>

                        <form action="{{ route('login') }}" method="POST">
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

                            <button type="submit">{{ __('Login') }}</button>

                            <div class="text-center text-sm font-semibold text-gray-500">
                                <a href="{{ route('forgot.password.get') }}" class="hover:underline">{{ __('Did you forget your password?') }}</a>
                            </div>
                        </form>

                        <div class="mercury-login-divider">{{ __('Dont have an account?') }}</div>

                        <a class="mercury-register" data-turbolinks="false" href="{{ route('register') }}">{{ __('Register') }}</a>
                    </div>
                </div>
            </section>
        </div>
    @endguest

    <div class="col-span-12 space-y-10">
        <div class="mercury-strip">
            <div class="w-full">
                <h2>{{ __('What is new at :hotel?', ['hotel' => setting('hotel_name')]) }}</h2>
                <p>{{ __('Keep up to date with the latest hotel gossip.') }}</p>
            </div>
        </div>

        <div class="mercury-news">
            @forelse ($articles as $article)
                <a class="mercury-news-card" href="{{ route('article.show', $article->slug) }}"
                    style="background-image: url('/storage/{{ $article->image }}')">
                    <span class="truncate">{{ $article->title }}</span>
                </a>
            @empty
                <x-filler-article-card />
            @endforelse
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
