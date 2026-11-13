@inject('onlineUsers', 'App\Services\User\UserApiService')
<div class="los-signin">
    <div class="los-signin-head">
        <span class="los-signin-mark" aria-hidden="true">🔑</span>
        <div>
            <h2>{{ __('Welcome to :hotel', ['hotel' => setting('hotel_name')]) }}</h2>
            <p>{{ __('There is currently :online users online', ['online' => $onlineUsers->onlineUserCount()]) }}</p>
        </div>
    </div>

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

        <x-form.primary-button>{{ __('Login') }}</x-form.primary-button>
    </form>

    <div class="los-signin-links">
        <a href="{{ route('forgot.password.get') }}" data-los-open>{{ __('Did you forget your password?') }}</a>
        <a href="{{ route('register') }}" data-los-open>{{ __('Dont have an account? Join now!') }}</a>
    </div>
</div>
