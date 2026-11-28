@inject('onlineUsers', 'App\Services\User\UserApiService')
@if (request()->routeIs('welcome') && ! auth()->check())
    {{-- The welcome area on the home page takes the banner's place for visitors. --}}
@else
    <header class="mercury-banner">
        <div class="mercury-banner-inner">
            <a href="{{ auth()->check() ? route('me.show') : route('welcome') }}">
                <img class="mercury-banner-logo" src="{{ setting('cms_logo') }}" alt="{{ __('Hotel logo') }}">
            </a>

            <div class="flex items-center gap-x-3">
                <span class="mercury-online hidden md:inline-block">
                    {{ __(':online :hotel online', ['online' => $onlineUsers->onlineUserCount(), 'hotel' => setting('hotel_name')]) }}
                </span>

                @auth
                    <a class="mercury-pill mercury-pill-green" data-turbolinks="false" href="{{ route('nitro-client') }}">{{ __('Nitro client') }}</a>

                    @if (config('habbo.client.flash_enabled'))
                        <a class="mercury-pill" data-turbolinks="false" href="{{ route('flash-client') }}">{{ __('Flash client') }}</a>
                    @endif
                @else
                    <a class="mercury-pill" href="{{ route('welcome') }}">{{ __('Login') }}</a>
                    <a class="mercury-pill mercury-pill-green" data-turbolinks="false" href="{{ route('register') }}">{{ __('Register') }}</a>
                @endauth
            </div>
        </div>
    </header>
@endif
