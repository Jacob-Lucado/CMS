@props(['iconUrl' => '', 'color' => '', 'classes' => ''])
<article class="los-card los-package">
    <header class="los-card-head">
        @if (filled($iconUrl))
            <div class="icon" style="background-image: url({{ $iconUrl }}); background-color: {{ $color }};"></div>
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
</article>
