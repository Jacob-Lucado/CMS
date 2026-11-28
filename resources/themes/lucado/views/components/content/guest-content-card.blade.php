@props(['icon' => '', 'classes' => ''])
<section {{ $attributes->merge(['class' => 'los-card ' . $classes]) }}>
    <header class="los-card-head">
        @if (filled($icon))
            <div class="icon {{ $icon }}"></div>
        @endif
        <div>
            <h2 class="los-card-title">{{ $title }}</h2>
            @isset($underTitle)
                <p class="los-card-sub">{{ $underTitle }}</p>
            @endisset
        </div>
    </header>
    <div class="los-card-body">
        {{ $slot }}
    </div>
</section>
