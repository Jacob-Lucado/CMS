<?php

test('pages share the hotel name with a large preview image', function () {
    installHotel();
    setSetting('hotel_name', 'Lucado Hotel');

    $this->get(route('article.index'))->assertOk()
        ->assertSee('<meta property="og:site_name" content="Lucado Hotel">', false)
        ->assertSee('<meta property="og:image" content="' . asset('images/og/lucado-hotel.png') . '">', false)
        ->assertSee('<meta property="og:image:width" content="1200">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
        ->assertSee('"@type":"WebSite","name":"Lucado Hotel"', false);

    expect(public_path('images/og/lucado-hotel.png'))->toBeFile();
});

test('a custom share image replaces the bundled one', function () {
    installHotel();
    setSetting('seo_image', 'https://cdn.example.com/hotel.png');

    $this->get(route('article.index'))->assertOk()
        ->assertSee('<meta property="og:image" content="https://cdn.example.com/hotel.png">', false)
        ->assertDontSee('og:image:width');
});
