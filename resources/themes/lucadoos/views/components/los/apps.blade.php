@php
    use App\Emulator\Data\Feature;
    use App\Emulator\Emulator;

    $user = auth()->user();
    $page = fn (string $route, string $title, string $icon, array $params = []): array => [
        'path' => '/' . ltrim(parse_url(route($route, $params), PHP_URL_PATH) ?? '', '/'),
        'title' => $title,
        'icon' => $icon,
    ];
    $link = fn (?string $url, string $title, string $icon): ?array => filled($url) ? ['url' => $url, 'title' => $title, 'icon' => $icon] : null;

    $groups = [];

    if ($user) {
        $groups[] = ['label' => __('Me'), 'icon' => '🏠', 'pages' => [
            $page('me.show', __('Me'), '🏠'),
            $page('home.show', __('My Home'), '🖼️', [$user->username]),
            $page('draw-badge', __('Badge Drawer'), '✏️'),
        ]];

        $groups[] = ['label' => setting('hotel_name', __('Hotel')), 'icon' => '🏨', 'hotel' => true, 'pages' => array_values(array_filter([
            $page('nitro-client', __('Nitro client'), '🏨'),
            config('habbo.client.flash_enabled') ? $page('flash-client', __('Flash client'), '⚡') : null,
        ]))];
    } else {
        $groups[] = ['label' => __('Welcome'), 'icon' => '🏠', 'pages' => [
            $page('welcome', __('Welcome'), '🏠'),
            $page('register', __('Create an account'), '✨'),
        ]];
    }

    $groups[] = ['label' => __('News'), 'icon' => '📰', 'pages' => [$page('article.index', __('Articles'), '📰')]];

    if ($user) {
        $groups[] = ['label' => __('Community'), 'icon' => '👥', 'pages' => array_values(array_filter([
            $page('staff.index', __('Staff'), '🛡️'),
            $page('teams.index', __('Teams'), '👥'),
            $page('team-applications.index', __('Team applications'), '📝'),
            $page('staff-applications.index', __('Staff applications'), '📋'),
            Emulator::supports(Feature::CameraPhotos) ? $page('photos.index', __('Photos'), '📷') : null,
        ]))];

        $groups[] = ['label' => __('Leaderboards'), 'icon' => '🏆', 'pages' => [$page('leaderboard.index', __('Leaderboards'), '🏆')]];

        if (Emulator::supports(Feature::RareValues)) {
            $groups[] = ['label' => __('Rare values'), 'icon' => '💎', 'pages' => [$page('values.index', __('Rare values'), '💎')]];
        }

        $groups[] = ['label' => __('Shop'), 'icon' => '🛒', 'pages' => [$page('shop.index', __('Shop'), '🛒')]];

        $groups[] = ['label' => __('Help center'), 'icon' => '🛟', 'pages' => array_values(array_filter([
            $page('help-center.index', __('Help center'), '🛟'),
            $page('help-center.ticket.create', __('New ticket'), '🎫'),
            hasPermission('manage_website_tickets') ? $page('help-center.ticket.index', __('Open tickets'), '📂') : null,
            $page('help-center.rules.index', __('Rules'), '📜'),
        ]))];

        $groups[] = ['label' => __('Settings'), 'icon' => '⚙️', 'pages' => [
            $page('settings.account.show', __('Account'), '⚙️'),
            $page('settings.password.show', __('Password'), '🔒'),
            $page('settings.two-factor', __('Two-factor'), '🔐'),
            $page('settings.session-logs', __('Session logs'), '🕘'),
        ]];
    } else {
        $groups[] = ['label' => __('Rules'), 'icon' => '📜', 'pages' => [$page('help-center.rules.index', __('Rules'), '📜')]];
    }

    $links = array_values(array_filter([
        $link(setting('discord_invitation_link'), __('Discord'), '💬'),
        $user && hasPermission('housekeeping_access') ? $link(setting('housekeeping_url') ?: url('/housekeeping'), __('Housekeeping'), '🛠️') : null,
        $user && hasPermission('generate_logo') ? $link(route('logo-generator.index'), __('Logo generator'), '🖌️') : null,
    ]));

    $losData = [
        'hotel' => setting('hotel_name', config('app.name')),
        'groups' => $groups,
        'links' => $links,
        'user' => $user ? [
            'name' => $user->username,
            'head' => setting('avatar_imager') . $user->look . '&direction=2&head_direction=2&headonly=1&gesture=sml&size=s',
            'credits' => (int) $user->credits,
            'duckets' => (int) $user->currency('duckets'),
            'diamonds' => (int) $user->currency('diamonds'),
        ] : null,
        'home' => $user ? $page('me.show', '', '')['path'] : '/',
        'clients' => array_values(array_filter([
            '/' . ltrim(parse_url(route('nitro-client'), PHP_URL_PATH), '/'),
            '/' . ltrim(parse_url(route('flash-client'), PHP_URL_PATH), '/'),
        ])),
        'strings' => [
            'start' => __('Start'),
            'search' => __('Type here to search'),
            'pinned' => __('Pinned'),
            'reset' => __('Reset'),
            'dragHint' => __('drag to reorder · ✕ to unpin'),
            'nothingPinned' => __('Nothing pinned. Use the ☆ beside any page to pin it here.'),
            'noMatch' => __('Nothing matches that.'),
            'personalize' => __('Personalize'),
            'wallpaper' => __('Wallpaper'),
            'anyColor' => __('Any color'),
            'waves' => __('Neon waves'),
            'wavesHint' => __('Glowing lines rolling over the wallpaper.'),
            'theme' => __('Theme'),
            'toLight' => __('Switch to light'),
            'toDark' => __('Switch to dark'),
            'savedHere' => __('These are saved in this browser.'),
            'minimize' => __('Minimize'),
            'maximize' => __('Maximize'),
            'close' => __('Close'),
            'pickWindow' => __('Pick a window for this space'),
            'signIn' => __('Sign in'),
            'logout' => __('Log out'),
            'links' => __('Links'),
        ],
    ];
@endphp
<script type="application/json" id="los-data">@json($losData)</script>
