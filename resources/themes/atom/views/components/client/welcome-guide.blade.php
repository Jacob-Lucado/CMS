<div id="welcome-guide" style="position:fixed;inset:0;z-index:40;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.55);font-family:'Ubuntu Condensed',sans-serif">
    <div style="width:min(440px,92vw);background:#ece9e0;color:#1f2937;border:2px solid #1f2937;border-radius:6px;box-shadow:0 8px 30px rgba(0,0,0,.5);overflow:hidden">
        <div style="padding:10px 16px;background:#2f6f8f;color:#fff;font-size:18px;font-weight:700">
            {{ __('Welcome to :hotel', ['hotel' => setting('hotel_name')]) }}
        </div>

        <ol style="list-style:decimal;margin:0;padding:16px 16px 4px 36px;font-size:16px;line-height:1.5">
            <li>{{ __('Click a spot on the floor to walk there.') }}</li>
            <li>{{ __('Click the bar at the bottom of the screen and type to chat.') }}</li>
            <li>{{ __('Open the rooms icon in the toolbar to find rooms and people.') }}</li>
            <li>{{ __('The shop icon sells furni, clothes and pets.') }}</li>
            <li>{{ __('Open the Me menu and pick Daily Hub for a free reward every day.') }}</li>
        </ol>

        <div style="padding:12px 16px 16px;text-align:right">
            <button type="button" onclick="closeWelcomeGuide()"
                style="padding:6px 20px;border:2px solid #cf9d15;border-radius:4px;background:#eeb425;color:#000;font-size:16px;font-weight:700;cursor:pointer">
                {{ __('Got it') }}
            </button>
        </div>
    </div>
</div>

<script>
    // Shown once per browser, so returning players are not slowed down.
    (function () {
        const key = 'welcome-guide-seen';

        try {
            if (!localStorage.getItem(key)) {
                document.getElementById('welcome-guide').style.display = 'flex';
            }
        } catch (e) {}
    })();

    function closeWelcomeGuide() {
        document.getElementById('welcome-guide').style.display = 'none';

        try {
            localStorage.setItem('welcome-guide-seen', '1');
        } catch (e) {}
    }
</script>
