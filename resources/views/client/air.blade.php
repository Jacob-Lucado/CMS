<!DOCTYPE HTML>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ setting('hotel_name') }} - {{ __('AIR client') }}</title>

    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: system-ui, sans-serif; color: #fff; background: #14192b; }
        main { max-width: 420px; padding: 32px; text-align: center; }
        h1 { margin: 0 0 8px; font-size: 22px; }
        p { margin: 0 0 20px; line-height: 1.5; color: #c4cadb; }
        a.open { display: inline-block; padding: 12px 28px; font-weight: 700; color: #0b1220; text-decoration: none; background: #5bd1ff; border-radius: 999px; }
        a.open:hover { filter: brightness(1.1); }
        ol { margin: 24px 0 0; padding: 0; text-align: left; list-style-position: inside; color: #c4cadb; font-size: 14px; line-height: 1.7; }
        ol a { color: #5bd1ff; }
    </style>
</head>

<body>
    <main>
        <h1>{{ __('Play in the AIR client') }}</h1>
        <p>{{ __('Open the game in the Habbo AIR client with HabboCustomLauncher.') }}</p>
        <a class="open" href="{{ $url }}" target="_top">{{ __('Open AIR client') }}</a>
        <ol>
            <li>{{ __('Download') }} <a href="https://github.com/DennisObject/HabboCustomLauncher/releases/latest" rel="noopener" target="_blank">HabboCustomLauncher</a> {{ __('(version 2026.11.21 or newer).') }}</li>
            <li>{{ __('Start the launcher once.') }}</li>
            <li>{{ __('Come back here and press Open AIR client.') }}</li>
        </ol>
    </main>
</body>

</html>
