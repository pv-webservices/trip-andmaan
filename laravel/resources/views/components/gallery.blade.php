@props(['items' => [], 'compact' => false, 'allLabel' => 'All'])
@php
    $items = array_values(array_filter($items, fn ($i) => $site->media($i['image_id'] ?? null)));
    $categories = collect($items)->pluck('category')->filter()->unique()->values();
@endphp
@if (! $compact && $categories->isNotEmpty())
    <div class="gallery-filters" aria-label="Filter gallery">
        @foreach ([$allLabel, ...$categories] as $i => $category)
            <button type="button" @class(['filter-button', 'is-active' => $i === 0]) aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" data-filter="{{ $i === 0 ? 'All' : $category }}">{{ $category }}</button>
        @endforeach
    </div>
@endif
<div @class(['gallery-grid', 'compact' => $compact])>
    @foreach ($items as $item)
        @php $media = $site->media($item['image_id']); @endphp
        <figure data-category="{{ $item['category'] ?? '' }}">
            <a class="gallery-item" href="{{ $media->largestUrl() }}" data-gallery data-caption="{{ $item['title'] }}">
                <x-picture :id="$item['image_id']" :alt="$item['title']" sizes="(max-width: 760px) 50vw, 33vw" />
                <span class="gallery-hover" aria-hidden="true"><span>{{ $item['title'] }}</span><x-ta-icon name="arrow-up-right" :size="18" /></span>
            </a>
            @unless ($compact)<figcaption>{{ $item['title'] }}</figcaption>@endunless
        </figure>
    @endforeach
</div>
<dialog class="lightbox" aria-label="Photo gallery viewer">
    <div class="lightbox-top">
        <p class="lightbox-counter" aria-live="polite"></p>
        <button class="icon-button" data-gallery-close aria-label="Close photo viewer"><x-ta-icon name="close" :size="20" /></button>
    </div>
    <div class="lightbox-stage">
        <button class="icon-button" data-gallery-prev aria-label="Previous photograph"><x-ta-icon name="arrow-left" :size="20" /></button>
        <img src="{{ $items ? $site->media($items[0]['image_id'])->largestUrl() : '' }}" alt="" width="1600" height="1200" loading="lazy" decoding="async">
        <button class="icon-button" data-gallery-next aria-label="Next photograph"><x-ta-icon :size="20" /></button>
    </div>
    <div class="lightbox-nav-mobile">
        <button class="icon-button" data-gallery-prev aria-label="Previous photograph"><x-ta-icon name="arrow-left" :size="20" /></button>
        <button class="icon-button" data-gallery-next aria-label="Next photograph"><x-ta-icon :size="20" /></button>
    </div>
    <p class="lightbox-caption" aria-live="polite"></p>
</dialog>
