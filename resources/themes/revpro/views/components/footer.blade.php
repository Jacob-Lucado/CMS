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
</footer>
