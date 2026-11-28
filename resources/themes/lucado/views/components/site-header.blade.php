@inject('onlineUsers', 'App\Services\User\UserApiService')
<header class="lucado-bar">
    <div class="lucado-bar-inner">
        <a href="{{ auth()->check() ? route('me.show') : route('welcome') }}">
            <img class="lucado-bar-logo" src="{{ setting('cms_logo') }}" alt="{{ __('Hotel logo') }}">
        </a>

        <div class="flex items-center gap-x-2">
            <span class="los-pill hidden md:inline-flex"><i class="los-dot"></i>{{ trans_choice(':count player online|:count players online', $onlineUsers->onlineUserCount()) }}</span>

            @auth
                <a class="los-btn" data-turbolinks="false" href="{{ route('nitro-client') }}">{{ __('Nitro client') }}</a>

                @if (config('habbo.client.flash_enabled'))
                    <a class="los-btn" data-turbolinks="false" href="{{ route('flash-client') }}">{{ __('Flash client') }}</a>
                @endif
            @else
                <a class="los-btn" data-turbolinks="false" href="{{ route('register') }}">{{ __('Register') }}</a>
            @endauth
        </div>
    </div>
</header>
