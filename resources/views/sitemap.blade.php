<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
    @foreach ($staticUrls as $url)
        <url>
            <loc>{{ $url }}</loc>
            @isset($languages)
                @foreach ($languages as $lang)
                    <xhtml:link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}?lang={{ $lang }}" />
                @endforeach
            @endisset
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    @foreach ($quizzes as $quiz)
        <url>
            <loc>{{ route('quiz-player', ['code' => $quiz->unique_code]) }}</loc>
            @isset($languages)
                @foreach ($languages as $lang)
                    <xhtml:link rel="alternate" hreflang="{{ $lang }}" href="{{ route('quiz-player', ['code' => $quiz->unique_code]) }}?lang={{ $lang }}" />
                @endforeach
            @endisset
            <lastmod>{{ optional($quiz->updated_at)->tz('UTC')->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.6</priority>
        </url>
    @endforeach
</urlset>


