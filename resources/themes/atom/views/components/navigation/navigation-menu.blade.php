<div class="relative hidden h-full w-full flex-col items-center gap-y-2 py-3! md:flex! md:flex-row! md:gap-x-8 md:gap-y-0 md:py-0!" id="mobile-menu">
    @auth
        <x-navigation.dropdown icon="home" route-group="user*">
            {{ auth()->user()->username }}

            <x-slot:children>
                <x-navigation.dropdown-child :route="route('me.show')">
                    {{ __('Home') }}
                </x-navigation.dropdown-child>

				<x-navigation.dropdown-child :route="route('draw-badge')">
                    {{ __('Badge Drawer') }}
                </x-navigation.dropdown-child>

                <x-navigation.dropdown-child :route="route('home.show', auth()->user()->username)">
                    {{ __('My Home') }}
                </x-navigation.dropdown-child>
            </x-slot:children>
        </x-navigation.dropdown>
    @else
        <a href="{{ route('welcome') }}"
           class="nav-item dark:text-gray-200 {{ request()->routeIs('welcome') ? 'md:border-b-4! md:border-b-[#eeb425]!' : '' }}">
            <i class="mr-1 hidden navigation-icon home lg:inline-flex"></i>
            {{ __('Home') }}
        </a>
    @endauth

    @auth
        <x-navigation.dropdown icon="community" route-group="community*" :uppercase="true">
            {{ __('Community') }}

            <x-slot:children>
                <x-navigation.dropdown-child :route="route('article.index')">
                    {{ __('Articles') }}
                </x-navigation.dropdown-child>

                <x-navigation.dropdown-child :route="route('staff.index') ">
                    {{ __('Staff') }}
                </x-navigation.dropdown-child>

                <x-navigation.dropdown-child :route="route('teams.index')">
                    {{ __('Teams') }}
                </x-navigation.dropdown-child>
                
                <x-navigation.dropdown-child :route="route('team-applications.index')">
                    {{ __('Team applications') }}
                </x-navigation.dropdown-child>

                <x-navigation.dropdown-child :route="route('staff-applications.index')">
                    {{ __('Staff applications') }}
                </x-navigation.dropdown-child>

                <x-navigation.dropdown-child :route="route('photos.index')">
                    {{ __('Photos') }}
                </x-navigation.dropdown-child>
            </x-slot:children>
        </x-navigation.dropdown>

        <a href="{{ route('leaderboard.index') }}"
           class="nav-item dark:text-gray-200 {{ request()->routeIs('leaderboard.*') ? 'md:border-b-4! md:border-b-[#eeb425]!' : '' }}">
            <i class="navigation-icon leaderboards mr-1 hidden lg:inline-flex"></i>
            {{ __('Leaderboards') }}
        </a>

        <x-navigation.dropdown icon="community" route-group="rooms*" :uppercase="true">
            {{ __('Hotel') }}

            <x-slot:children>
                <x-navigation.dropdown-child :route="route('rooms.index')">
                    {{ __('Rooms') }}
                </x-navigation.dropdown-child>
                <x-navigation.dropdown-child :route="route('furni.index')">
                    {{ __('Furni') }}
                </x-navigation.dropdown-child>
                <x-navigation.dropdown-child :route="route('music.index')">
                    {{ __('Music charts') }}
                </x-navigation.dropdown-child>
                <x-navigation.dropdown-child :route="route('catalog.index')">
                    {{ __('Catalog') }}
                </x-navigation.dropdown-child>
                <x-navigation.dropdown-child :route="route('help-center.badges.index')">
                    {{ __('Badges') }}
                </x-navigation.dropdown-child>
                <x-navigation.dropdown-child :route="route('values.index')">
                    {{ __('Rare values') }}
                </x-navigation.dropdown-child>
                @auth
                <x-navigation.dropdown-child :route="route('marketplace.index')">
                    {{ __('Marketplace') }}
                </x-navigation.dropdown-child>
                @endauth
                @auth
                <x-navigation.dropdown-child :route="route('outfits.index')">
                    {{ __('Outfit of the week') }}
                </x-navigation.dropdown-child>
                @endauth
            </x-slot:children>
        </x-navigation.dropdown>

        <x-navigation.dropdown icon="community" route-group="events*" :uppercase="true">
            {{ __('Play') }}

            <x-slot:children>
                <x-navigation.dropdown-child :route="route('events.index')">
                    {{ __('Events') }}
                </x-navigation.dropdown-child>
                @auth
                <x-navigation.dropdown-child :route="route('daily-reward.show')">
                    {{ __('Daily reward') }}
                </x-navigation.dropdown-child>
                @endauth
                <x-navigation.dropdown-child :route="route('riddle.show')">
                    {{ __('Riddle') }}
                </x-navigation.dropdown-child>
                <x-navigation.dropdown-child :route="route('polls.index')">
                    {{ __('Polls') }}
                </x-navigation.dropdown-child>
                @auth
                <x-navigation.dropdown-child :route="route('gameboards.index')">
                    {{ __('Gameboards') }}
                </x-navigation.dropdown-child>
                @endauth
                @auth
                <x-navigation.dropdown-child :route="route('radio.index')">
                    {{ __('Radio') }}
                </x-navigation.dropdown-child>
                @endauth
            </x-slot:children>
        </x-navigation.dropdown>

        <x-navigation.dropdown icon="community" route-group="changelog*" :uppercase="true">
            {{ __('More') }}

            <x-slot:children>
                <x-navigation.dropdown-child :route="route('changelog.index')">
                    {{ __('What is new') }}
                </x-navigation.dropdown-child>
                <x-navigation.dropdown-child :route="route('hall-of-fame.index')">
                    {{ __('Hall of fame') }}
                </x-navigation.dropdown-child>
                <x-navigation.dropdown-child :route="route('suggestions.index')">
                    {{ __('Suggestions') }}
                </x-navigation.dropdown-child>
                @auth
                <x-navigation.dropdown-child :route="route('social.index')">
                    {{ __('Social feed') }}
                </x-navigation.dropdown-child>
                @endauth
                @auth
                <x-navigation.dropdown-child :route="route('notifications.index')">
                    {{ __('Notifications') }}
                </x-navigation.dropdown-child>
                @endauth
            </x-slot:children>
        </x-navigation.dropdown>

        <a data-turbolinks="false" href="{{ route('shop.index') }}"
           class="nav-item dark:text-gray-200 {{ request()->routeIs('shop.*') ? 'md:border-b-4! md:border-b-[#eeb425]!' : '' }}">
            <i class="navigation-icon mr-1 hidden lg:inline-flex shop"></i>
            {{ __('Shop') }}
        </a>
    @endauth

    <x-navigation.dropdown icon="rules" route-group="help-center*" :uppercase="true">
        {{ __('Assistance') }}

        <x-slot:children>
			@auth
            <x-navigation.dropdown-child :route="route('help-center.index')">
                {{ __('Help center') }}
            </x-navigation.dropdown-child>

            @if(hasPermission('manage_website_tickets'))
                <x-navigation.dropdown-child :route="route('help-center.ticket.index')">
                    {{ __('Open tickets') }}
                </x-navigation.dropdown-child>
            @endif

            @else
                <x-navigation.dropdown-child :route="route('help-center.rules.index')">
                    {{ __('Rules') }}
                </x-navigation.dropdown-child>
            @endauth
        </x-slot:children>
    </x-navigation.dropdown>

    <a href="{{ setting('discord_invitation_link') }}" target="_blank" class="nav-item dark:text-gray-200">
        {{ __('Discord') }}
    </a>

    <div class="w-full flex md:hidden gap-x-1 justify-center">
        <x-navigation.language-selector>
            <img src="/assets/images/icons/flags/{{ session()->has('locale') ? session()->get('locale') : config('habbo.site.default_language') }}.png"
                 alt="">
        </x-navigation.language-selector>
    </div>
</div>
