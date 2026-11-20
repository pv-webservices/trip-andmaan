{{-- Responsive image from the media library. A mobile image (optional) is served to phones through <picture>. --}}
@props(['id' => null, 'alt' => '', 'class' => null, 'eager' => false, 'sizes' => '(max-width: 700px) 100vw, 50vw', 'mobile' => null, 'focal' => null])
@php
    $media = $site->media($id);
    $mobileMedia = $site->media($mobile);
    $fallback = $media?->urlNear(1200);
    $style = $focal ? 'object-position:'.$focal : null;
@endphp
@if ($media)
    @if ($mobileMedia)
        <picture>
            <source media="(max-width: 700px)" srcset="{{ $mobileMedia->srcset() }}" sizes="100vw">
            <img @if($class) class="{{ $class }}" @endif src="{{ $fallback }}" srcset="{{ $media->srcset() }}" sizes="{{ $sizes }}" alt="{{ $alt }}" @if($media->displayWidth()) width="{{ $media->displayWidth() }}" height="{{ $media->displayHeight() }}" @endif loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async" fetchpriority="{{ $eager ? 'high' : 'auto' }}" @if($style) style="{{ $style }}" @endif>
        </picture>
    @else
        <img @if($class) class="{{ $class }}" @endif src="{{ $fallback }}" srcset="{{ $media->srcset() }}" sizes="{{ $sizes }}" alt="{{ $alt }}" @if($media->displayWidth()) width="{{ $media->displayWidth() }}" height="{{ $media->displayHeight() }}" @endif loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async" fetchpriority="{{ $eager ? 'high' : 'auto' }}" @if($style) style="{{ $style }}" @endif>
    @endif
@endif
