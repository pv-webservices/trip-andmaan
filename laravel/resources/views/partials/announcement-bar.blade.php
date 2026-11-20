{{-- Scrolling announcement bar. Rendered only when at least one announcement applies, so it never leaves empty space. --}}
@php
    $bg = $bar['background'] ?? '#04203a';
    $fg = $bar['text_color'] ?? '#ffffff';
    $speed = (int) ($bar['speed'] ?? 50);
@endphp
<section class="announce" aria-label="Announcements" data-speed="{{ max(20, min(120, $speed)) }}" style="--announce-bg:{{ $bg }};--announce-fg:{{ $fg }}">
    <div class="announce-viewport">
        <ul class="announce-track">
            @foreach ([false, true] as $copy)
                @foreach ($announcements as $a)
                    <li @if($copy) data-announce-copy aria-hidden="true" @endif>
                        <span>{{ $a->text }}</span>
                        @if (filled($a->link_url))
                            <a href="{{ $a->link_url }}" @if($copy) tabindex="-1" @endif @if(str_starts_with($a->link_url, 'http')) target="_blank" rel="noopener noreferrer" @endif>{{ $a->link_label ?: 'Learn more' }}</a>
                        @endif
                    </li>
                @endforeach
            @endforeach
        </ul>
    </div>
    <button class="announce-toggle" type="button" aria-pressed="false" aria-label="Pause announcements"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 5h4v14H7zM13 5h4v14h-4z"/></svg></button>
</section>
