{{-- Inner-page hero with breadcrumbs and BreadcrumbList JSON-LD. $hero: page_hero section data (or package/destination data). --}}
@props(['hero' => [], 'crumbs' => []])
@php
    $title = $hero['title'] ?? '';
    $path = request()->getPathInfo();
    $path = str_starts_with($path, '/preview') ? (substr($path, 8) ?: '/') : $path;
    $items = collect([['Home', '/'], ...$crumbs, [$title, $path]])
        ->values()
        ->map(fn ($c, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $c[0], 'item' => $site->absolute($c[1])])
        ->all();
    $breadcrumbs = \App\Cms\JsonLd::graph([['@type' => 'BreadcrumbList', 'itemListElement' => $items]]);
    $video = $site->media($hero['video_id'] ?? null);
    $poster = $site->media($hero['poster_id'] ?? null) ?? $site->media($hero['image_id'] ?? null);
@endphp
<section class="page-hero">
    <x-picture :id="$hero['image_id'] ?? null" :mobile="$hero['mobile_image_id'] ?? null" :alt="$hero['alt'] ?? ''" :focal="$hero['focal'] ?? null" eager sizes="100vw" />
    @if ($video)
        <video class="hero-video" data-autoplay muted loop playsinline preload="none" @if($poster) poster="{{ $poster->urlNear(1200) }}" @endif aria-hidden="true"><source src="{{ $video->url() }}" type="{{ $video->mime }}"></video>
    @endif
    <div class="wrap">
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <a href="/">Home</a>
            @foreach ($crumbs as [$label, $href])
                <span aria-hidden="true">/</span>
                <a href="{{ $href }}">{{ $label }}</a>
            @endforeach
            <span aria-hidden="true">/</span>
            <span aria-current="page">{{ $title }}</span>
        </nav>
        @if (filled($hero['eyebrow'] ?? null))<p class="eyebrow">{{ $hero['eyebrow'] }}</p>@endif
        <h1>{{ $title }}</h1>
        <p class="lead">{{ $hero['description'] ?? '' }}</p>
        @php $buttons = collect([$hero['primary'] ?? null, $hero['secondary'] ?? null])->filter(fn ($b) => is_array($b) && filled($b['url'] ?? null) && filled($b['label'] ?? null) && ($b['visible'] ?? true)); @endphp
        @if ($buttons->isNotEmpty())
            <div class="btn-row">
                <x-cta :link="$hero['primary'] ?? null" class="btn btn-sun" />
                <x-cta :link="$hero['secondary'] ?? null" class="btn btn-glass" :icon="false" />
            </div>
        @endif
        {{ $slot }}
    </div>
    <script type="application/ld+json">{!! $breadcrumbs !!}</script>
</section>
