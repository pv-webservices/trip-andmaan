@props(['reels' => []])
@php $reels = array_values(array_filter($reels, fn ($r) => $site->media($r['video_id'] ?? null))); @endphp
@if ($reels)
    <ul @class(['reel-grid', 'reel-grid-few' => count($reels) < 4])>
        @foreach ($reels as $i => $r)
            @php $video = $site->media($r['video_id']); $poster = $site->media($r['poster_id'] ?? null); @endphp
            <li class="reel" data-reveal style="--i:{{ $i % 4 }}">
                <video data-autoplay muted loop playsinline preload="none" @if($poster) poster="{{ $poster->urlNear(640) }}" @endif width="480" height="480" aria-label="{{ filled($r['aria'] ?? null) ? $r['aria'] : 'Silent clip: '.$r['title'] }}">
                    <source src="{{ $video->url() }}" type="{{ $video->mime }}">
                </video>
                <div class="reel-body">
                    <h3>{{ $r['title'] }}</h3>
                    @if (filled($r['text'] ?? null))<p>{{ $r['text'] }}</p>@endif
                </div>
            </li>
        @endforeach
    </ul>
@endif
