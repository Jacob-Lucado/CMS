@inject('onlineUsers', 'App\Services\User\UserApiService')
<header class="revpro-banner">
    <div class="revpro-banner-inner">
        <a href="{{ auth()->check() ? route('me.show') : route('welcome') }}">
            <img class="revpro-banner-logo" src="{{ setting('cms_logo') }}" alt="{{ __('Hotel logo') }}">
        </a>

        <div class="flex items-center gap-x-2">
            <span class="revpro-online hidden md:inline-block">
                {{ __(':online :hotel online', ['online' => $onlineUsers->onlineUserCount(), 'hotel' => setting('hotel_name')]) }}
            </span>

            @auth
                <a class="revpro-btn revpro-btn-green" data-turbolinks="false" href="{{ route('nitro-client') }}">{{ __('Nitro client') }}</a>

                @if (config('habbo.client.flash_enabled'))
                    <a class="revpro-btn" data-turbolinks="false" href="{{ route('flash-client') }}">{{ __('Flash client') }}</a>
                @endif
            @else
                <a class="revpro-btn" href="{{ route('welcome') }}">{{ __('Login') }}</a>
                <a class="revpro-btn revpro-btn-green" data-turbolinks="false" href="{{ route('register') }}">{{ __('Register') }}</a>
            @endauth
        </div>
    </div>
</header>
