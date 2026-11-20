@php
    $l = $s['labels'] ?? [];
    $tokens = ['{name}' => $place->name, '{official}' => $place->official, '{intro}' => $place->intro];
    $title = filled($place->meta_title) ? $place->meta_title : strtr($l['meta_title'] ?? '{name} — {official} Travel Guide', $tokens);
    $description = filled($place->meta_description) ? $place->meta_description
        : strtr($l['meta_description'] ?? 'Discover {name} ({official}). {intro} Explore destination highlights and contact Trip Andaman to discuss your visit.', $tokens);
    $enquirySubject = "a trip to {$place->name}";
    $others = $site->places()->reject(fn ($o) => $o->slug === $place->slug)->take(3)->values();
@endphp
@extends('layouts.site')

@section('content')
    <x-page-hero :hero="['title' => $place->name, 'eyebrow' => $place->official, 'description' => $place->short.' '.$place->intro, 'image_id' => $place->image_id, 'mobile_image_id' => $place->mobile_image_id, 'alt' => $place->image_alt ?: $place->name.' scenery', 'focal' => $place->focal !== '50% 50%' ? $place->focal : null]" :crumbs="[['Destinations', '/destinations/']]" />
    <section class="section">
        <div class="wrap detail-grid">
            <div>
                <div data-reveal>
                    <p class="eyebrow">{{ $l['about_eyebrow'] ?? 'Get to know the island' }}</p>
                    <h2 style="font-size:var(--fs-h2)">{{ $l['about_title'] ?? 'A place to' }} <span class="accent">{{ $l['about_accent'] ?? 'discover' }}</span></h2>
                    <p class="lead" style="margin-top:20px">{{ $place->text }}</p>
                </div>
                @if ($site->media($place->secondary_image_id))
                    <div @class(['media-frame', 'portrait' => $place->secondary_portrait]) data-reveal>
                        <x-picture :id="$place->secondary_image_id" :alt="$place->secondary_alt ?? ''" sizes="(max-width: 1024px) 100vw, 60vw" />
                    </div>
                @endif
                @if (! empty($place->highlights))
                    <div data-reveal style="margin-top:40px">
                        <h2 style="font-size:clamp(1.6rem, 1.2rem + 1.4vw, 2.4rem)">{{ $l['radar_title'] ?? 'On your' }} <span class="accent">{{ $l['radar_accent'] ?? 'radar' }}</span></h2>
                        <ul class="highlight-cards">
                            @foreach ($place->highlights as $h)<li><x-ta-icon name="check" :size="24" />{{ $h }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <div class="notice" style="margin-top:36px" data-reveal>
                    <x-ta-icon name="compass" :size="22" />
                    <p><strong>{{ $l['before_label'] ?? 'Before you go.' }}</strong> {{ $place->note }} {{ $l['before_suffix'] ?? '' }}</p>
                </div>
                @if (filled($place->source))
                    <p class="source-link">
                        {{ $l['source_label'] ?? 'Destination reference:' }}
                        <a href="{{ $place->source }}" target="_blank" rel="noopener noreferrer">{{ $l['source_link'] ?? 'Official tourism information ↗' }}</a>{{ $l['source_suffix'] ?? '. Check current local notices before travel.' }}
                    </p>
                @endif
            </div>
            <aside class="info-panel">
                <p class="eyebrow">{{ $l['aside_eyebrow'] ?? 'Make room for the islands' }}</p>
                <h2>{{ $l['aside_title'] ?? 'Picture yourself here?' }}</h2>
                <p>{{ strtr($l['aside_text'] ?? '', $tokens) }}</p>
                <a class="btn btn-sun" href="/tour-packages/customized-trip/?interest={{ rawurlencode($place->name) }}">{{ $l['plan_label'] ?? 'Plan a Trip Here' }} <x-ta-icon :size="18" /></a>
                <x-whatsapp-button :subject="'a trip to '.$place->name">{{ $l['whatsapp_label'] ?? 'Ask on WhatsApp' }}</x-whatsapp-button>
                <div class="divider"></div>
                <a class="call" href="tel:{{ $site->tel() }}"><span><x-ta-icon name="phone" :size="18" /></span>{{ $site->phone() }}</a>
                <a class="text-link" href="/destinations/">{{ $l['others_label'] ?? 'Explore other destinations' }} <x-ta-icon :size="16" /></a>
            </aside>
        </div>
    </section>
    @if (! empty($place->reels))
        <section class="section bg-ocean" aria-labelledby="dest-reels">
            <div class="wrap">
                <p class="eyebrow">{{ $l['reels_eyebrow'] ?? '' }}</p>
                <h2 id="dest-reels" style="font-size:var(--fs-h2);margin-bottom:32px">{{ $l['reels_title'] ?? '' }} <span class="accent">{{ $l['reels_accent'] ?? '' }}</span></h2>
                <x-reel-grid :reels="$place->reels" />
                @if (filled($l['reels_url'] ?? null))
                    <a class="text-link" href="{{ $l['reels_url'] }}" style="margin-top:28px">{{ $l['reels_link'] ?? '' }} <x-ta-icon :size="16" /></a>
                @endif
            </div>
        </section>
    @endif
    @if ($others->isNotEmpty())
        <section class="section bg-mist">
            <div class="wrap">
                <x-section-heading :eyebrow="$l['more_eyebrow'] ?? 'Keep exploring'" :title="$l['more_title'] ?? 'More island'" :accent="$l['more_accent'] ?? 'inspiration'" :link="['url' => '/destinations/', 'label' => $l['more_link'] ?? 'All Destinations']" />
                <div class="card-grid-3">
                    @foreach ($others as $i => $other)<x-destination-card :place="$other" :index="$i" />@endforeach
                </div>
            </div>
        </section>
    @endif
    <x-enquiry-banner :page="$s['enquiry_banner'] ?? null" />
@endsection
