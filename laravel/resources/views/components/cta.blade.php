{{-- A button from a {label, url, visible} link. Renders nothing when hidden or incomplete. --}}
@props(['link' => null, 'class' => 'btn', 'icon' => true, 'iconSize' => 18])
@if (is_array($link) && filled($link['url'] ?? null) && filled($link['label'] ?? null) && ($link['visible'] ?? true))
    @php $external = str_starts_with($link['url'], 'http'); @endphp
    <a {{ $attributes->merge(['class' => $class]) }} href="{{ $link['url'] }}" @if($external) target="_blank" rel="noopener noreferrer" @endif>{{ $link['label'] }}@if ($icon) <x-ta-icon :size="$iconSize" />@endif</a>
@endif
