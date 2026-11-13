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
            $page('me.show', __('Me'), asset('assets/images/icons/navigation/home.png')),
            $page('home.show', __('My Home'), asset('assets/images/icons/feeds.png'), [$user->username]),
            $page('draw-badge', __('Badge Drawer'), asset('assets/images/icons/star.gif')),
        ]];

        $groups[] = ['label' => setting('hotel_name', __('Hotel')), 'icon' => '🏨', 'hotel' => true, 'pages' => array_values(array_filter([
            $page('nitro-client', __('Nitro client'), asset('assets/images/icons/lighthouse.png')),
            config('habbo.client.flash_enabled') ? $page('flash-client', __('Flash client'), '⚡') : null,
        ]))];
    } else {
        $groups[] = ['label' => __('Welcome'), 'icon' => '🏠', 'pages' => [
            $page('welcome', __('Welcome'), asset('assets/images/icons/navigation/home.png')),
            $page('register', __('Create an account'), asset('assets/images/icons/star.gif')),
        ]];
    }

    if ($user) {
        $groups[] = ['label' => __('Social'), 'icon' => '💬', 'pages' => [
            $page('social.index', __('Social feed'), asset('assets/images/icons/chat.png')),
            $page('social.user', __('My profile'), asset('assets/images/icons/friends.png'), [$user->username]),
            $page('outfits.index', __('Outfit of the Week'), asset('assets/images/icons/star.gif')),
            $page('marketplace.index', __('Marketplace'), asset('assets/images/icons/catalog.png')),
            $page('notifications.index', __('Notifications'), asset('assets/images/icons/feeds.png')),
        ]];
    }

    $groups[] = ['label' => __('News'), 'icon' => '📰', 'pages' => [$page('article.index', __('Articles'), asset('assets/images/icons/article.gif'))]];

    if ($user) {
        $groups[] = ['label' => __('Community'), 'icon' => '👥', 'pages' => array_values(array_filter([
            $page('staff.index', __('Staff'), asset('assets/images/icons/rank.png')),
            $page('teams.index', __('Teams'), asset('assets/images/icons/friends.png')),
            $page('team-applications.index', __('Team applications'), asset('assets/images/icons/chat.png')),
            $page('staff-applications.index', __('Staff applications'), asset('assets/images/icons/due-chat.png')),
            Emulator::supports(Feature::CameraPhotos) ? $page('photos.index', __('Photos'), asset('assets/images/icons/camera.png')) : null,
        ]))];

        $groups[] = ['label' => __('Radio'), 'icon' => '📻', 'pages' => [$page('radio.index', __('Radio'), 'https://cdn.habboassets.com/images.habbo.com/c_images/album1584/PTG12.gif')]];

        $groups[] = ['label' => __('Leaderboards'), 'icon' => '🏆', 'pages' => [$page('leaderboard.index', __('Leaderboards'), asset('assets/images/icons/navigation/leaderboards.png'))]];

        if (Emulator::supports(Feature::RareValues)) {
            $groups[] = ['label' => __('Rare values'), 'icon' => '💎', 'pages' => [$page('values.index', __('Rare values'), asset('assets/images/icons/ltd.png'))]];
        }

        $groups[] = ['label' => __('Shop'), 'icon' => '🛒', 'pages' => [$page('shop.index', __('Shop'), asset('assets/images/icons/catalog.png'))]];

        $groups[] = ['label' => __('Help center'), 'icon' => '🛟', 'pages' => array_values(array_filter([
            $page('help-center.index', __('Help center'), '🛟'),
            $page('help-center.ticket.create', __('New ticket'), '🎫'),
            hasPermission('manage_website_tickets') ? $page('help-center.ticket.index', __('Open tickets'), '📂') : null,
            $page('help-center.rules.index', __('Rules'), asset('assets/images/icons/navigation/rules.gif')),
        ]))];

        $groups[] = ['label' => __('Settings'), 'icon' => '⚙️', 'pages' => [
            $page('settings.account.show', __('Account'), '⚙️'),
            $page('settings.password.show', __('Password'), '🔒'),
            $page('settings.two-factor', __('Two-factor'), '🔐'),
            $page('settings.session-logs', __('Session logs'), '🕘'),
        ]];
    } else {
        $groups[] = ['label' => __('Rules'), 'icon' => '📜', 'pages' => [$page('help-center.rules.index', __('Rules'), asset('assets/images/icons/navigation/rules.gif'))]];
    }

    $links = array_values(array_filter([
        $link(setting('discord_invitation_link'), __('Discord'), asset('assets/images/icons/discord.png')),
        $user && hasPermission('housekeeping_access') ? $link(setting('housekeeping_url') ?: url('/housekeeping'), __('Housekeeping'), '🛠️') : null,
        $user && hasPermission('generate_logo') ? $link(route('logo-generator.index'), __('Logo generator'), '🖌️') : null,
    ]));

    // Each group shows its first app's pixel icon.
    $groups = array_map(fn (array $group): array => $group['pages'] === [] ? $group : [...$group, 'icon' => $group['pages'][0]['icon']], $groups);

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
            'balance' => route('me.balance', absolute: false),
            'notifications' => '/' . ltrim(parse_url(route('notifications.index'), PHP_URL_PATH), '/'),
            'unread' => \App\Models\Social\WebsiteNotification::query()->where('user_id', $user->id)->whereNull('read_at')->count(),
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
