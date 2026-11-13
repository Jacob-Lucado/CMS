<?php

use App\Models\Articles\WebsiteArticle;
use Database\Seeders\WebsiteArticleSeeder;

test('the welcome article is not added again after it was deleted', function () {
    installHotel();
    $this->seed(WebsiteArticleSeeder::class);
    WebsiteArticle::query()->sole()->delete();

    $this->seed(WebsiteArticleSeeder::class);

    expect(WebsiteArticle::query()->count())->toBe(0);
});
