<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ setting('hotel_name') }} - Nitro</title>

    <link href="https://fonts.googleapis.com/css2?family=Ubuntu+Condensed&display=swap" rel="stylesheet">

    @livewireStyles
    @livewireScriptConfig
    @vite(['resources/themes/' .  setting('theme') . '/css/app.css', 'resources/themes/' .  setting('theme') . '/js/app.js'], 'build')
</head>

<body class="overflow-hidden" id="nitro-client">
    @if (setting('enable_client_buttons') === '1')
        <div class="absolute top-4 left-4 z-10 flex gap-x-2" id="client-buttons">
            <div onclick="openSite()" title="{{ __('Website') }}">
                <x-client.client-button>
                    <x-icons.home />
                </x-client.client-button>
            </div>

            <div onclick="reloadClient()">
                <x-client.client-button>
                    <x-icons.reload />
                </x-client.client-button>
            </div>

            <div onclick="toggleFullscreen()">
                <x-client.client-button>
                    <x-icons.fullscreen />
                </x-client.client-button>
            </div>

            <x-client.client-button classes="flex items-center justify-center gap-x-1">
                <x-icons.user />

                <span id="online-count"></span>
            </x-client.client-button>
        </div>
    @endif
    <iframe id="nitro" src="{{ sprintf('%s/index.html?sso=%s%s', setting('nitro_path'), $sso, $room ? '&room=' . $room : '') }}"
        class="absolute top-0 left-0 m-0 h-full w-full overflow-hidden border-none p-0"></iframe>

    @if (setting('enable_client_buttons') === '1')
        <div id="site-overlay" style="position:fixed;inset:0;z-index:50;display:none;flex-direction:column;background:#000">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 16px;background:#1f2937;color:#fff;font-family:'Ubuntu Condensed',sans-serif">
                <span>{{ setting('hotel_name') }}</span>

                <button type="button" onclick="closeSite()"
                    style="padding:4px 16px;border:2px solid #cf9d15;border-radius:4px;background:#eeb425;color:#000;font-weight:700;cursor:pointer">
                    {{ __('Return to hotel') }}
                </button>
            </div>

            <iframe id="site-frame" data-src="{{ route('me.show') }}" style="flex:1;width:100%;border:0;background:#fff"></iframe>
        </div>

        <script>
            // The game keeps running behind the website, so the player never loses their place in a room.
            function openSite() {
                const overlay = document.getElementById('site-overlay');
                const frame = document.getElementById('site-frame');

                if (!frame.getAttribute('src')) {
                    frame.setAttribute('src', frame.dataset.src);
                }

                overlay.style.display = 'flex';
            }

            document.getElementById('site-frame').addEventListener('load', function () {
                try {
                    // The hotel is already open, so a link into the client just closes the website.
                    this.contentDocument.addEventListener('click', function (event) {
                        const link = event.target.closest('a[href]');

                        if (link && /\/(nitro|flash)$/.test(new URL(link.href, location.href).pathname)) {
                            event.preventDefault();
                            event.stopPropagation();
                            closeSite();
                        }
                    }, true);
                } catch (e) {}
            });

            function closeSite() {
                const overlay = document.getElementById('site-overlay');

                overlay.style.display = 'none';
            }
        </script>
    @endif

    <x-client.radio-mini />

    {{-- Show disconnected message on client if the user has been disconnected --}}
    <div id="disconnected" class="h-screen w-full">
        <div class="absolute h-full w-full bg-black/50"></div>

        <div class="relative flex h-full w-full flex-col items-center justify-center gap-4">
            <h2 class="text-2xl text-white">
                {{ __('Whoops! It seems like you have been disconnected...') }}
            </h2>

            <div class="flex gap-x-4">
                <button
                    class="py-2 px-4 text-white rounded bg-[#eeb425] hover:bg-[#e3aa1e] border-2 border-[#cf9d15] transition ease-in-out"
                    onclick="reloadClient()">
                    {{ __('Reload client') }}
                </button>

                <a data-turbolinks="false" href="{{ route('me.show') }}">
                    <x-form.secondary-button>
                        {{ __('Back to website') }}
                    </x-form.secondary-button>
                </a>
            </div>
        </div>
    </div>

    <script>
        function toggleFullscreen() {
            if (document.fullscreenElement) {
                document.exitFullscreen();

                return;
            }

            document.documentElement.requestFullscreen();
        }

        function reloadClient() {
            window.location.href = window.location;
        }

        window.addEventListener('DOMContentLoaded', () => {
            function getOnlineUserCount() {
                fetch('{{ route('api.online-count') }}')
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(response) {
                        document.getElementById('online-count').innerHTML = response.data.onlineCount;
                    });
            }

            getOnlineUserCount();

            setInterval(function() {
                getOnlineUserCount();
            }, 15000);
        });
    </script>

    <script src="{{ asset('assets/js/atom.js') }}"></script>
</body>

</html>
