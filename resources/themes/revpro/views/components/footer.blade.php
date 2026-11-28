<footer class="revpro-footer mt-auto">
    <span class="float-right">
        <a href="{{ route('help-center.index') }}">{{ __('Help') }}</a>
        <span class="sep">|</span>
        <a href="{{ route('help-center.rules.index') }}">{{ __('Rules') }}</a>
        @if (filled(setting('discord_invitation_link')))
            <span class="sep">|</span>
            <a href="{{ setting('discord_invitation_link') }}" target="_blank" rel="noopener">Discord</a>
        @endif
    </span>

    &copy; {{ date('Y') }} {{ setting('hotel_name') }} - {{ __('All rights reserved.') }}
    <br>
    <span class="text-[12px]">
        {{ __('A re-edit of the open source Atom CMS, made for :hotel.', ['hotel' => setting('hotel_name')]) }}
        <a href="https://github.com/atom-projects/atom-cms" target="_blank" rel="noopener">Atom CMS</a>
    </span>
</footer>