@extends('layouts.site')

@section('content')
    @php
        $hero = $s['hero'] ?? [];
        $list = $s['items'] ?? ['items' => []];
        $items = $list['items'] ?? [];
    @endphp
    <x-page-hero :hero="$hero">
        @if (($hero['show_jump_links'] ?? true) && $items)
            <nav class="xp-jump" aria-label="Jump to an experience">
                @foreach ($items as $e)
                    <a href="#{{ $e['anchor'] }}"><x-ta-icon :name="$e['icon'] ?? 'arrow'" :size="16" /> {{ $e['title'] }}</a>
                @endforeach
            </nav>
        @endif
    </x-page-hero>
    <section class="section">
        <div class="wrap">
            @if ($n = $s['notice'] ?? null)
                <div class="notice" data-reveal>
                    <x-ta-icon :name="$n['icon'] ?? 'compass'" :size="22" />
                    <p>{{ $n['text'] ?? '' }}</p>
                </div>
            @endif
            <div class="xp-list">
                @foreach ($items as $i => $e)
                    <article class="xp-row" id="{{ $e['anchor'] }}">
                        <div class="media-frame" data-reveal><x-picture :id="$e['image_id'] ?? null" :alt="$e['alt'] ?? ''" sizes="(max-width: 1024px) 100vw, 55vw" /></div>
                        <div data-reveal style="--i:1">
                            <span class="xp-index" aria-hidden="true">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            @if (filled($list['eyebrow'] ?? null))<p class="eyebrow">{{ $list['eyebrow'] }}</p>@endif
                            <h2>{{ $e['title'] }}</h2>
                            <p class="lead">{{ $e['text'] }}</p>
                            @if (filled($list['note'] ?? null))<p class="note">{{ $list['note'] }}</p>@endif
                            @if (filled($list['whatsapp_label'] ?? null))
                                <x-whatsapp-button :subject="($e['whatsapp_subject'] ?? null) ?: mb_strtolower($e['title']).' in the Andamans'">{{ $list['whatsapp_label'] }}</x-whatsapp-button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @if ($ws = $s['water_sports'] ?? null)
        <section class="section bg-ocean" id="water-sports" aria-label="{{ $ws['eyebrow'] ?? 'Water sports' }}">
            <div class="wrap">
                <x-section-heading :data="$ws" />
                <x-reel-grid :reels="$ws['reels'] ?? []" />
                <div class="btn-row" style="margin-top:36px">
                    @if (filled($ws['whatsapp_label'] ?? null))
                        <x-whatsapp-button :subject="$ws['whatsapp_subject'] ?? 'water sports at North Bay'">{{ $ws['whatsapp_label'] }}</x-whatsapp-button>
                    @endif
                    <x-cta :link="$ws['secondary'] ?? null" class="btn btn-glass" :icon="false" />
                </div>
            </div>
        </section>
    @endif
    <x-enquiry-banner :page="$s['enquiry_banner'] ?? null" />
@endsection
