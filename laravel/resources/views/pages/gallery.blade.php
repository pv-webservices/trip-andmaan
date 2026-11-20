@extends('layouts.site')

@section('content')
    @php $g = $s['photos'] ?? []; @endphp
    <x-page-hero :hero="$s['hero'] ?? []" />
    <section class="section">
        <div class="wrap">
            <x-gallery :items="$g['items'] ?? []" :all-label="$g['all_label'] ?? 'All'" />
            @if (filled($g['note'] ?? null))
                <p class="note" style="margin-top:12px">
                    {{ $g['note'] }}
                    @if (filled($g['credits_label'] ?? null))
                        <a class="text-link" href="/image-credits/">{{ $g['credits_label'] }} <x-ta-icon :size="14" /></a>
                    @endif
                </p>
            @endif
        </div>
    </section>
    @if ($v = $s['videos'] ?? null)
        <section class="section bg-ocean">
            <div class="wrap">
                <x-section-heading :data="$v" />
                <x-reel-grid :reels="$v['reels'] ?? []" />
                @php $films = array_values(array_filter($v['films'] ?? [], fn ($f) => $site->media($f['video_id'] ?? null))); @endphp
                @if ($films)
                    <div class="film-pair" style="margin-top:clamp(14px, 1.6vw, 24px)">
                        @foreach ($films as $i => $f)
                            @php $video = $site->media($f['video_id']); $poster = $site->media($f['poster_id'] ?? null); @endphp
                            <figure data-reveal @if($i) style="--i:{{ $i }}" @endif>
                                <video controls playsinline preload="none" @if($poster) poster="{{ $poster->urlNear(640) }}" @endif aria-label="{{ $f['aria'] }}"><source src="{{ $video->url() }}" type="{{ $video->mime }}"></video>
                                <figcaption><strong>{{ $f['title'] }}</strong>{{ $f['meta'] ?? '' }}</figcaption>
                            </figure>
                        @endforeach
                    </div>
                @endif
                @if (filled($v['whatsapp_label'] ?? null) || is_array($v['secondary'] ?? null))
                    <div class="btn-row" style="margin-top:36px">
                        @if (filled($v['whatsapp_label'] ?? null))
                            <x-whatsapp-button :subject="$v['whatsapp_subject'] ?? 'water sports at North Bay'">{{ $v['whatsapp_label'] }}</x-whatsapp-button>
                        @endif
                        <x-cta :link="$v['secondary'] ?? null" class="btn btn-glass" :icon="false" />
                    </div>
                @endif
            </div>
        </section>
    @endif
    <x-enquiry-banner :page="$s['enquiry_banner'] ?? null" />
@endsection
