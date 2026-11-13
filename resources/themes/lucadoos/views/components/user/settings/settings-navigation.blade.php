@php
    $items = [
        ['route' => 'settings.account.show', 'icon' => '👤', 'label' => __('Account'), 'hint' => __('Email, name and motto')],
        ['route' => 'settings.password.show', 'icon' => '🔒', 'label' => __('Password'), 'hint' => __('Change your password')],
        ['route' => 'settings.two-factor', 'icon' => '🛡️', 'label' => __('Two-factor'), 'hint' => __('A code on sign-in')],
        ['route' => 'settings.session-logs', 'icon' => '🧾', 'label' => __('Sign-in history'), 'hint' => __('Where you signed in')],
    ];
@endphp
<nav class="los-side-nav" aria-label="{{ __('Settings') }}">
    @foreach ($items as $item)
        <a href="{{ route($item['route']) }}" @class(['is-active' => request()->routeIs($item['route'])])>
            <span class="los-side-icon" aria-hidden="true">{{ $item['icon'] }}</span>
            <span>
                <strong>{{ $item['label'] }}</strong>
                <small>{{ $item['hint'] }}</small>
            </span>
        </a>
    @endforeach
    <a href="{{ route('me.show') }}#discord">
        <span class="los-side-icon" aria-hidden="true">💬</span>
        <span>
            <strong>{{ __('Discord') }}</strong>
            <small>{{ __('Link or unlink on your profile') }}</small>
        </span>
    </a>
</nav>
