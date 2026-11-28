<footer class="mercury-footer mt-auto">
    <div class="mx-auto max-w-7xl">
        <nav class="mb-2 flex flex-wrap items-center justify-center text-[13px]" aria-label="{{ __('Footer') }}">
            <a href="{{ route('welcome') }}">{{ __('Home') }}</a>
            @if (filled(setting('discord_invitation_link')))
                <span class="sep">|</span>
                <a href="{{ setting('discord_invitation_link') }}" target="_blank" rel="noopener">Discord</a>
            @endif
            <span class="sep">|</span>
            <a href="{{ route('article.index') }}">{{ __('Articles') }}</a>
        </nav>

        <p class="text-[13px] font-semibold">
            &copy; {{ date('Y') }} {{ setting('hotel_name') }} - {{ __('All rights reserved.') }}
        </p>
        <p class="mt-1 text-[12px] opacity-80">
            {{ __('A re-edit of the open source Atom CMS, made for :hotel.', ['hotel' => setting('hotel_name')]) }}
            <a href="https://github.com/atom-projects/atom-cms" target="_blank" rel="noopener">Atom CMS</a>
        </p>
    </div>
</footer>
