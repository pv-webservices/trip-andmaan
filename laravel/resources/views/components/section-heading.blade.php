@props(['eyebrow' => null, 'title' => null, 'accent' => null, 'text' => null, 'link' => null, 'center' => false, 'data' => null])
@php
    if (is_array($data)) {
        $eyebrow ??= $data['eyebrow'] ?? null;
        $title ??= $data['title'] ?? null;
        $accent ??= $data['accent'] ?? null;
        $text ??= $data['text'] ?? null;
        $link ??= $data['link'] ?? null;
    }
@endphp
<div @class(['section-head', 'center' => $center]) data-reveal>
    <div>
        @if (filled($eyebrow))<p class="eyebrow">{{ $eyebrow }}</p>@endif
        <h2>{{ $title }} @if (filled($accent))<span class="accent">{{ $accent }}</span>@endif</h2>
        @if (filled($text))<p class="lead">{{ $text }}</p>@endif
    </div>
    @if (is_array($link) && filled($link['url'] ?? null) && ($link['visible'] ?? true))
        <a class="btn btn-ghost" href="{{ $link['url'] }}">{{ $link['label'] ?? '' }} <x-ta-icon :size="18" /></a>
    @endif
</div>
