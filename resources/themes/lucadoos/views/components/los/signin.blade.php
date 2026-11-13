@inject('onlineUsers', 'App\Services\User\UserApiService')
<div class="los-signin">
    <div class="los-signin-head">
        <span class="los-signin-mark" aria-hidden="true">🔑</span>
        <div>
            <h2>{{ __('Welcome to :hotel', ['hotel' => setting('hotel_name')]) }}</h2>
            <p>{{ trans_choice(':count player online|:count players online', $onlineUsers->onlineUserCount()) }}</p>
        </div>
    </div>

    <x-los.discord-button :label="__('Sign in with Discord')" />
    @inject('discordLogin', 'App\Services\Auth\DiscordLogin')
    @if ($discordLogin->isEnabled())
        <div class="los-or"><span>{{ __('or use your hotel name') }}</span></div>
    @endif

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
        <a href="{{ route('forgot.password.get') }}" data-los-open>{{ __('Forgot your password?') }}</a>
        <a href="{{ route('register') }}" data-los-open>{{ __('New here? Create an account') }}</a>
    </div>
</div>
