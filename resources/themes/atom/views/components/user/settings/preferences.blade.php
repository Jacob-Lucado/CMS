@php
    $themeCards = [
        'lucadoos' => ['LucadoOS', __('A desktop-style site with windows and apps.')],
        'atom' => [__('Classic site'), __('The original layout with a banner and a menu bar.')],
        'mercury' => ['Mercury', __('Light and friendly, with a red welcome area.')],
        'revpro' => ['RevPro', __('A grey page with a blue login card and a dark menu bar.')],
    ];
    $themeChoices = array_values(array_filter(config('theme.switchable', []), fn ($theme) => isset($themeCards[$theme])));
    $currentTheme = setting('theme');
    $languages = DB::table('website_languages')->get();
    $discord = auth()->user()->discordAccount;
@endphp

<div class="col-span-12 mt-3 space-y-3">
    @if (count($themeChoices) > 1)
        <x-content.content-card icon="hotel-icon" classes="border dark:border-gray-900">
            <x-slot:title>{{ __('Appearance') }}</x-slot:title>
            <x-slot:under-title>{{ __('Pick how the site looks. Your choice is kept on this device.') }}</x-slot:under-title>

            <div class="grid grid-cols-1 gap-3 px-2 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($themeChoices as $theme)
                    <a data-turbolinks="false" href="{{ route('theme.switch', $theme) }}" @class([
                        'flex flex-col gap-y-1 rounded border-2 p-3 text-sm transition dark:text-gray-100',
                        'border-[#eeb425] bg-amber-50 dark:bg-gray-700' => $currentTheme === $theme,
                        'border-gray-300 bg-gray-100 hover:border-gray-400 dark:border-gray-600 dark:bg-gray-800' => $currentTheme !== $theme,
                    ])>
                        <span class="font-bold">{{ $themeCards[$theme][0] }}</span>
                        <span class="text-gray-600 dark:text-gray-300">{{ $themeCards[$theme][1] }}</span>
                        <span class="mt-1 text-xs font-semibold {{ $currentTheme === $theme ? 'text-green-700' : 'text-gray-500' }}">
                            {{ $currentTheme === $theme ? __('In use') : __('Use this theme') }}
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3 px-2 text-sm dark:text-gray-200">
                <span class="font-semibold">{{ __('Color mode') }}</span>
                <button type="button" id="settings-color-mode"
                    onclick="var d=document.documentElement.classList.toggle('dark');localStorage.setItem('theme',d?'dark':'light');this.textContent=d?'{{ __('Switch to light') }}':'{{ __('Switch to dark') }}'"
                    class="rounded border-2 border-gray-300 bg-gray-100 px-4 py-2 font-semibold text-gray-800 hover:bg-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                    {{ __('Switch to dark') }}
                </button>
                <script>
                    (function () {
                        var button = document.getElementById('settings-color-mode');
                        if (button && document.documentElement.classList.contains('dark')) {
                            button.textContent = '{{ __('Switch to light') }}';
                        }
                    })();
                </script>
            </div>
        </x-content.content-card>
    @endif

    @if ($languages->count() > 1)
        <x-content.content-card icon="hotel-icon" classes="border dark:border-gray-900">
            <x-slot:title>{{ __('Language') }}</x-slot:title>
            <x-slot:under-title>{{ __('Choose the language of the site.') }}</x-slot:under-title>

            <div class="flex flex-wrap gap-2 px-2">
                @foreach ($languages as $language)
                    <a data-turbolinks="false" href="{{ route('language.select', $language->country_code) }}"
                        class="flex items-center gap-x-2 rounded border-2 border-gray-300 bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        <img src="/assets/images/icons/flags/{{ $language->country_code }}.png" alt="">
                        {{ strtoupper($language->country_code) }}
                    </a>
                @endforeach
            </div>
        </x-content.content-card>
    @endif

    <x-content.content-card icon="hotel-icon" classes="border dark:border-gray-900">
        <x-slot:title>{{ __('Connected accounts') }}</x-slot:title>
        <x-slot:under-title>{{ __('Link Discord to sign in with it.') }}</x-slot:under-title>

        <div class="flex flex-wrap items-center justify-between gap-3 px-2 text-sm dark:text-gray-200">
            @if ($discord)
                <div class="flex items-center gap-x-3">
                    @if ($discord->avatarUrl())
                        <img class="h-10 w-10 rounded-full" src="{{ $discord->avatarUrl() }}" alt="">
                    @endif
                    <div>
                        <p class="font-bold">Discord</p>
                        <p class="text-gray-600 dark:text-gray-300">{{ __('Linked as :name', ['name' => $discord->discord_username]) }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.discord.destroy') }}"
                    onsubmit="return confirm('{{ __('Unlink your Discord account?') }}')">
                    @method('DELETE')
                    @csrf
                    <button type="submit" class="rounded border-2 border-red-300 bg-red-50 px-4 py-2 font-semibold text-red-800 hover:bg-red-100">{{ __('Unlink Discord') }}</button>
                </form>
            @elseif (app(\App\Services\Auth\DiscordLogin::class)->isEnabled())
                <div>
                    <p class="font-bold">Discord</p>
                    <p class="text-gray-600 dark:text-gray-300">{{ __('Not linked yet.') }}</p>
                </div>

                <a data-turbolinks="false" href="{{ route('discord.redirect') }}" class="rounded border-2 border-indigo-400 bg-indigo-500 px-4 py-2 font-semibold text-white hover:bg-indigo-600">{{ __('Link Discord') }}</a>
            @else
                <p class="text-gray-600 dark:text-gray-300">{{ __('Discord linking is not set up on this hotel yet.') }}</p>
            @endif
        </div>
    </x-content.content-card>

    <x-content.content-card icon="hotel-icon" classes="border dark:border-gray-900">
        <x-slot:title>{{ __('Security') }}</x-slot:title>
        <x-slot:under-title>{{ __('Keep your account safe.') }}</x-slot:under-title>

        <div class="grid grid-cols-1 gap-3 px-2 text-sm sm:grid-cols-3 dark:text-gray-200">
            @php($twoFactorOn = auth()->user()->two_factor_confirmed_at !== null || filled(auth()->user()->two_factor_secret))
            <a data-turbolinks="false" href="{{ route('settings.password.show') }}" class="rounded border-2 border-gray-300 bg-gray-100 p-3 hover:bg-gray-200 dark:border-gray-600 dark:bg-gray-800">
                <span class="font-bold">{{ __('Password') }}</span><br>
                <span class="text-gray-600 dark:text-gray-300">{{ __('Change your password.') }}</span>
            </a>
            <a data-turbolinks="false" href="{{ route('settings.two-factor') }}" class="rounded border-2 border-gray-300 bg-gray-100 p-3 hover:bg-gray-200 dark:border-gray-600 dark:bg-gray-800">
                <span class="font-bold">{{ __('Two factor') }}</span><br>
                <span class="{{ $twoFactorOn ? 'text-green-700' : 'text-gray-600 dark:text-gray-300' }}">{{ $twoFactorOn ? __('Turned on') : __('Turned off') }}</span>
            </a>
            <a data-turbolinks="false" href="{{ route('settings.session-logs') }}" class="rounded border-2 border-gray-300 bg-gray-100 p-3 hover:bg-gray-200 dark:border-gray-600 dark:bg-gray-800">
                <span class="font-bold">{{ __('Session logs') }}</span><br>
                <span class="text-gray-600 dark:text-gray-300">{{ __('See where you are signed in.') }}</span>
            </a>
        </div>
    </x-content.content-card>
</div>
