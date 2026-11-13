<x-app-layout>
    @push('title', __('Shop'))

    <div class="col-span-12 flex flex-col gap-5 los-shop">
        <x-modals.modal-wrapper>
            <div class="los-notice">
                <span aria-hidden="true">ℹ️</span>
                <span>{{ __('Please read the shop') }} <button type="button" x-on:click="open = true">{{ __('terms and conditions') }}</button> {{ __('before buying anything.') }}</span>
            </div>

            <x-modals.regular-modal>
                <x-slot name="title">
                    <h2 class="text-2xl">
                        {{ __('Shop Terms & Conditions') }}
                    </h2>
                </x-slot>

                <div class="space-y-3 p-2">
                    <p>
                        {{ __('Here at :hotel Hotel we are accepting donations to keep the hotel up & running and as a thank you, you will in return receive in-game goods.', ['hotel' => setting('hotel_name')]) }}
                    </p>

                    <div class="flex flex-col gap-y-2 !mt-6">
                        <p class="font-semibold">{{ __('Why are donations important?') }}</p>

                        <p>{{ __('Donations are important, as it will help to pay our monthly bills needed to keep the hotel up & running, as well as adding new and exciting features for you and others to enjoy!') }}</p>
                    </div>

                    <div class="flex flex-col gap-y-2 !mt-6">
                        <p class="font-semibold">{{ __('Our terms') }}</p>

                        <p>{{ __('Once a donation has been made and received by us, it is non-refundable under any circumstances. The donated amount which is converted into website balance cannot be converted back into cash or other forms of money. By making a donation, you acknowledge and accept these terms and agree not to initiate a chargeback or dispute with your bank or card issuer.') }}</p>
                    </div>

                    <div class="flex flex-col gap-y-2 !mt-6">
                        <p class="font-semibold">{{ __('Notice') }}</p>

                        <p>{{ __('It is important to consider the consequences of our spending habits, especially when it comes to financial decisions. If you find yourself tempted to spend money you do not have, take a moment to reflect.') }}</p>
                        <p>{{ __('Remember, your financial well-being is crucial, and making responsible choices is key. If you are facing difficulties in controlling your spending habits, do not hesitate to seek friendly and professional guidance. There are resources available that can provide valuable advice and support.') }}</p>
                    </div>
                </div>
            </x-modals.regular-modal>
        </x-modals.modal-wrapper>

        <div class="los-shop-top">
            <section class="los-card">
                <header class="los-card-head">
                    <span class="los-head-emoji" aria-hidden="true">💰</span>
                    <div>
                        <h2 class="los-card-title">{{ __('Your balance') }}</h2>
                        <p class="los-card-sub">{{ __('Top up to buy packages.') }}</p>
                    </div>
                    <strong class="los-balance">{{ \App\Support\StorefrontMoney::format(auth()->user()->website_balance) }}</strong>
                </header>
                <div class="los-card-body">
                    @if (\App\Services\Payments\StripeCheckout::isConfigured())
                        <form action="{{ route('stripe.checkout') }}" method="POST" class="los-copy">
                            @csrf
                            <input name="amount" type="number" min="1" max="250" step="1" value="5" aria-label="{{ __('Amount') }}">
                            <button type="submit" class="los-btn">{{ __('Top up with card') }}</button>
                        </form>
                        <p class="los-card-sub">{{ __('Secure checkout by Stripe. Cards, Apple Pay and Google Pay work.') }}</p>
                    @else
                        <p class="los-empty">{{ __('Top-ups are not switched on yet.') }}</p>
                    @endif
                </div>
            </section>

            <section class="los-card">
                <header class="los-card-head">
                    <span class="los-head-emoji" aria-hidden="true">🎟️</span>
                    <div>
                        <h2 class="los-card-title">{{ __('Have a voucher?') }}</h2>
                        <p class="los-card-sub">{{ __('Redeem it for free credit.') }}</p>
                    </div>
                </header>
                <div class="los-card-body">
                    <form action="{{ route('shop.use-voucher') }}" method="POST" class="flex flex-col gap-2">
                        @csrf
                        <div class="los-copy">
                            <input name="code" type="text" placeholder="{{ __('Voucher code') }}" aria-label="{{ __('Voucher code') }}" required>
                            <button type="submit" class="los-btn gray">{{ __('Redeem') }}</button>
                        </div>
                        @if (setting('google_recaptcha_enabled'))
                            <div class="g-recaptcha" data-sitekey="{{ config('habbo.site.recaptcha_site_key') }}"></div>
                        @endif
                        @if (setting('cloudflare_turnstile_enabled'))
                            <x-turnstile />
                        @endif
                    </form>
                </div>
            </section>
        </div>

        <nav class="los-tabs" aria-label="{{ __('Categories') }}">
            <a href="{{ route('shop.index') }}" @class(['is-active' => ! request()->route('category')])>{{ __('All') }}</a>
            @foreach ($categories as $category)
                <a href="{{ route('shop.index', $category->slug) }}" @class(['is-active' => request()->route('category')?->slug === $category->slug || request()->route('category') === $category->slug])>
                    @if ($category->icon)
                        <img src="{{ $category->icon }}" alt="">
                    @endif
                    {{ $category->name }}
                </a>
            @endforeach
        </nav>

        <div class="los-package-grid">
            @forelse ($shopPackages as $shopPackage)
                <x-shop.package-card :package="$shopPackage" />
            @empty
                <p class="los-empty">{{ __('Nothing for sale here right now.') }}</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
