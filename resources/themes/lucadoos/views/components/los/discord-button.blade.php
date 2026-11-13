@props(['label' => __('Continue with Discord')])
@inject('discordLogin', 'App\Services\Auth\DiscordLogin')
@if ($discordLogin->isEnabled())
    <a href="{{ route('discord.redirect') }}" class="los-btn los-discord full" data-turbolinks="false" target="_top">
        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M20.3 4.4A19.7 19.7 0 0 0 15.4 3l-.6 1.3a18.3 18.3 0 0 0-5.6 0L8.6 3a19.6 19.6 0 0 0-4.9 1.4C.6 9 .1 13.5.4 17.9a19.9 19.9 0 0 0 6 3l1.3-2.1a12.8 12.8 0 0 1-2-1l.5-.4a14.2 14.2 0 0 0 12.2 0l.5.4c-.6.4-1.3.7-2 1l1.3 2.1a19.8 19.8 0 0 0 6-3c.4-5.1-.7-9.6-3.9-13.5ZM8.5 15.2c-1.2 0-2.2-1.1-2.2-2.4s1-2.4 2.2-2.4 2.2 1.1 2.2 2.4-1 2.4-2.2 2.4Zm7 0c-1.2 0-2.2-1.1-2.2-2.4s1-2.4 2.2-2.4 2.2 1.1 2.2 2.4-1 2.4-2.2 2.4Z"/></svg>
        <span>{{ $label }}</span>
    </a>
@endif
