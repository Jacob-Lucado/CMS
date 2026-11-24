@php
    $hotelName = setting('hotel_name', config('app.name'));
    $pageTitle = trim($__env->yieldPushContent('title'));
    $metaDescription = trim($__env->yieldPushContent('meta_description'))
        ?: setting('seo_description')
        ?: __('Join :hotel today! Meet new friends, play games, design your own rooms and become part of our community.', ['hotel' => $hotelName]);
    $metaDescription = \Illuminate\Support\Str::limit(trim(strip_tags($metaDescription)), 160, '');
    $metaKeywords = setting('seo_keywords')
        ?: implode(', ', [$hotelName, 'habbo', 'retro hotel', 'virtual world', 'pixel art', 'community', 'free to play']);
    $pageImage = trim($__env->yieldPushContent('meta_image')) ?: setting('seo_image');
    $metaImage = $pageImage ?: asset('images/og/lucado-hotel.png');
    if (! str_starts_with($metaImage, 'http')) {
        $metaImage = asset($metaImage);
    }
    $metaImageAlt = __(':hotel - meet friends, build rooms and play games', ['hotel' => $hotelName]);
@endphp

<title>{{ $pageTitle ? sprintf('%s - %s', $pageTitle, $hotelName) : $hotelName }}</title>

<meta name="description" content="{{ $metaDescription }}">
<meta name="keywords" content="{{ $metaKeywords }}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ url()->current() }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $hotelName }}">
<meta property="og:title" content="{{ $pageTitle ?: $hotelName }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:alt" content="{{ $metaImageAlt }}">
@unless ($pageImage)
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
@endunless

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle ?: $hotelName }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $metaImage }}">
<meta name="twitter:image:alt" content="{{ $metaImageAlt }}">

<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => $hotelName, 'url' => url('/'), 'image' => $metaImage, 'description' => $metaDescription], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
