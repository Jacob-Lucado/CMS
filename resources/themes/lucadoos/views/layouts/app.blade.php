{{--
    LucadoOS: the hotel as a desktop. A normal visit gets the desktop with the page in its first window; a page asked for
    by one of the desktop's windows (an iframe) gets just the page, so every window is a real, independent page.
    LucadoOS™ is a trademark of Jacob Lucado.
--}}
@php
    $losEmbed = request()->query('los') === 'embed' || request()->header('Sec-Fetch-Dest') === 'iframe';
    $losTitle = trim($__env->yieldPushContent('title')) ?: setting('hotel_name', config('app.name'));
    $losTheme = setting('theme', 'lucadoos');
@endphp
<!DOCTYPE html>
<html @class(['app', 'dark', 'los-embed' => $losEmbed]) lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark" data-los-auth="{{ auth()->check() ? '1' : '0' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f1724">

    <x-seo-meta />

    <link rel="icon" type="image/gif" sizes="18x17" href="{{ asset('assets/images/home_icon.gif') }}">

    <script>
        {{-- Runs before the first paint so the page never flashes the wrong theme. Shares Atom's "theme" key. --}}
        (() => {
            const root = document.documentElement;
            let saved = null;
            try { saved = localStorage.getItem('theme'); } catch { /* storage blocked: use the default */ }
            const theme = saved === 'light' || saved === 'dark' ? saved : @js(setting('cms_color_mode') === 'light' ? 'light' : 'dark');
            root.dataset.theme = theme;
            root.classList.toggle('dark', theme === 'dark');
        })();
    </script>

    <script src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/tippy-bundle.umd.min.js') }}"></script>

    @livewireStyles
    @livewireScriptConfig
    @vite(['resources/themes/' . $losTheme . '/css/app.css', 'resources/themes/' . $losTheme . '/js/app.js'], 'build')
    @stack('scripts')
    <x-turnstile.scripts />
</head>

@if ($losEmbed)
    <body class="los-page-body">
        <x-messages.flash-messages />

        <main class="los-content">
            <div class="mx-auto grid max-w-7xl grid-cols-12 gap-x-3 gap-y-8 p-5">
                {{ $slot }}
            </div>
        </main>
@else
    <body class="los-os los-orig">
        @if (config('habbo.site.debug_mode_enabled') && config('habbo.site.site_environment') === 'production')
            <div class="los-warning">
                {{ __('It seems like debug mode is enabled while being in production. It is heavily recommended too set APP_DEBUG in the .env file to false in production mode') }}
            </div>
        @endif

        <x-messages.flash-messages />
        <x-los.apps />

        <div class="los-desk" id="los-desk">
            <div class="los-logo" aria-hidden="true"></div>

            <nav class="los-icons" aria-label="{{ __('Desktop') }}"></nav>

            <div class="los-layer">
                <section class="los-win los-main focus max" role="dialog" aria-label="{{ $losTitle }}"
                         data-los-path="{{ '/' . ltrim(request()->path(), '/') . (request()->getQueryString() ? '?' . request()->getQueryString() : '') }}"
                         data-los-title="{{ $losTitle }}">
                    <header class="los-win-bar">
                        <div class="los-win-title"><i aria-hidden="true">🪟</i><span>{{ $losTitle }}</span></div>
                        <div class="los-win-btns"></div>
                    </header>

                    <div class="los-win-body los-page">
                        <div class="los-scroll">
                            <main class="los-content">
                                <div class="mx-auto grid max-w-7xl grid-cols-12 gap-x-3 gap-y-8 p-5">
                                    {{ $slot }}
                                </div>
                            </main>
                        </div>
                    </div>
                </section>
            </div>

            @guest
                <template id="los-signin">
                    <x-los.signin />
                </template>
            @endguest

            @auth
                <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>
                    @csrf
                </form>
            @endauth

            <noscript>
                <nav class="los-noscript">
                    @foreach (['welcome' => __('Home'), 'article.index' => __('News'), 'help-center.rules.index' => __('Rules')] as $route => $label)
                        <a href="{{ route($route) }}">{{ $label }}</a>
                    @endforeach
                </nav>
            </noscript>

            <p class="los-tm">LucadoOS™ is a trademark of Jacob Lucado.</p>
        </div>

        @if ($errors->hasBag('login') || session()->has('wrong-auth'))
            <script>window.losOpenSignIn = true;</script>
        @endif
@endif

    @if (setting('google_recaptcha_enabled'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif

    <script defer src="{{ asset('assets/js/alpine-ui.js') }}"></script>

    @stack('javascript')
    </body>
</html>
