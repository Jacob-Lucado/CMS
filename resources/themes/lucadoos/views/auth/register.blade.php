<x-app-layout>
    @push('title', __('Create an account'))
    @php
        $discord = session('discord.pending');
        $lookFallback = 'hr-100-61.hd-180-1.ch-210-66.lg-270-110.sh-305-62';
        $suggestedName = is_array($discord)
            ? mb_substr(preg_replace('/[^A-Za-z0-9_.-]/', '', (string) ($discord['username'] ?? '')), 0, \App\Emulator\Emulator::constraints()->usernameLength)
            : '';
    @endphp

    <div class="col-span-12">
        <section class="los-setup">
            <aside class="los-setup-side">
                <img class="los-setup-avatar" alt=""
                     src="{{ setting('avatar_imager') }}{{ setting('start_look') ?: $lookFallback }}&direction=2&head_direction=3&gesture=sml&action=wav&size=l">
                <h2>{{ __('Join :hotel', ['hotel' => setting('hotel_name')]) }}</h2>
                <ul class="los-perks">
                    <li>{{ __(':credits credits to start with', ['credits' => number_format((int) (setting('start_credits') ?: 1000))]) }}</li>
                    <li>{{ __('Your own rooms to build and decorate') }}</li>
                    <li>{{ __('Play right in your browser, nothing to install') }}</li>
                </ul>
            </aside>

            <div class="los-setup-main">
                <header>
                    <h1>{{ __('Create your account') }}</h1>
                    <p>{{ __('It is free and takes about a minute.') }}</p>
                </header>

                @if (is_array($discord))
                    <div class="los-linking">
                        @if (! empty($discord['avatar']))
                            <img src="https://cdn.discordapp.com/avatars/{{ $discord['id'] }}/{{ $discord['avatar'] }}.png?size=64" alt="">
                        @endif
                        <div>
                            <strong>{{ $discord['username'] }}</strong>
                            <span>{{ __('This Discord account will be linked when you finish.') }}</span>
                        </div>
                    </div>
                @else
                    <x-los.discord-button :label="__('Sign up with Discord')" />
                    @inject('discordLogin', 'App\Services\Auth\DiscordLogin')
                    @if ($discordLogin->isEnabled())
                        <div class="los-or"><span>{{ __('or fill this in') }}</span></div>
                    @endif
                @endif

                @error('registration')
                    <p class="los-alert">{{ $message }}</p>
                @enderror

                <form method="POST" action="{{ route('register.store') }}" class="los-form">
                    @csrf

                    <div class="los-field">
                        <x-form.label for="username">{{ __('Hotel name') }}</x-form.label>
                        <x-form.input error-bag="register" name="username" type="text" placeholder="{{ __('Pick the name other players will see') }}"
                                      value="{{ old('username', $suggestedName) }}" />
                    </div>

                    <div class="los-field">
                        <x-form.label for="mail">{{ __('Email') }}</x-form.label>
                        <x-form.input error-bag="register" name="mail" type="email" placeholder="{{ __('For password resets, never shown to anyone') }}"
                                      value="{{ old('mail', is_array($discord) ? ($discord['email'] ?? '') : '') }}" :autofocus="false" />
                    </div>

                    <div class="los-field-row">
                        <div class="los-field">
                            <x-form.label for="password">{{ __('Password') }}</x-form.label>
                            <x-form.input error-bag="register" name="password" type="password" placeholder="{{ __('At least 8 characters') }}" :autofocus="false" />
                        </div>

                        <div class="los-field">
                            <x-form.label for="password_confirmation">{{ __('Repeat password') }}</x-form.label>
                            <x-form.input error-bag="register" name="password_confirmation" type="password" placeholder="{{ __('Same as above') }}" :autofocus="false" />
                        </div>
                    </div>

                    @if (setting('requires_beta_code'))
                        <div class="los-field">
                            <x-form.label for="beta_code">{{ __('Beta code') }}</x-form.label>
                            <x-form.input error-bag="register" name="beta_code" type="text" placeholder="{{ __('The code you were given') }}"
                                          value="{{ old('beta_code') }}" :autofocus="false" />
                        </div>
                    @endif

                    <label class="los-check">
                        <input type="checkbox" name="terms" @checked(old('terms'))>
                        <span>{!! __('I agree to the :rules', ['rules' => '<a href="' . e(route('help-center.rules.index')) . '" data-los-open>' . e(__('hotel rules')) . '</a>']) !!}</span>
                    </label>
                    @error('terms')
                        <p class="los-alert">{{ $message }}</p>
                    @enderror

                    <input type="hidden" name="referral_code" value="{{ $referral_code }}">

                    @if (setting('google_recaptcha_enabled'))
                        <div class="g-recaptcha" data-sitekey="{{ config('habbo.site.recaptcha_site_key') }}"></div>
                    @endif

                    @if (setting('cloudflare_turnstile_enabled'))
                        <x-turnstile />
                    @endif

                    <button type="submit" class="los-btn full">{{ __('Create my account') }}</button>
                </form>

                <p class="los-setup-foot">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('welcome') }}" target="_top">{{ __('Sign in') }}</a>
                </p>
            </div>
        </section>
    </div>
</x-app-layout>
