@props(['article', 'forSlider' => false])
<a class="los-news" href="{{ route('article.show', $article->slug) }}" data-los-open>
    <span class="los-news-image" style="background-image: url('/storage/{{ $article->image }}')"></span>
    <span class="los-news-title">{{ $article->title }}</span>
    <span class="los-news-meta">{{ $article->user->username ?? setting('hotel_name') }} · {{ $article->created_at?->diffForHumans() }}</span>
</a>
