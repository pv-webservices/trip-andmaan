@php
    $groupsSection = $s['groups'] ?? ['groups' => []];
    $tours = $site->tours();
    $groups = collect($groupsSection['groups'] ?? [])
        ->map(fn ($g) => [...$g, 'items' => $tours->filter(fn ($t) => $t->nights >= (int) $g['min_nights'] && $t->nights <= (int) $g['max_nights'])->values()])
        ->filter(fn ($g) => $g['items']->isNotEmpty())
        ->values();
    $faq = $s['faq'] ?? null;
    $schema = $faq && ! empty($faq['items']) ? [[
        '@type' => 'FAQPage',
        'mainEntity' => collect($faq['items'])->map(fn ($f) => ['@type' => 'Question', 'name' => $f['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']]])->all(),
    ]] : [];
@endphp
@extends('layouts.site')

@section('content')
    @php $hero = $s['hero'] ?? []; @endphp
    <x-page-hero :hero="$hero">
        @if (($hero['show_jump_links'] ?? true) && $groups->isNotEmpty())
            <nav class="xp-jump" aria-label="Jump to a package length">
                @foreach ($groups as $g)
                    <a href="#{{ $g['anchor'] }}"><x-ta-icon name="route" :size="16" /> {{ $g['eyebrow'] }}</a>
                @endforeach
                @if (filled($groupsSection['custom_jump_label'] ?? null))
                    <a href="/tour-packages/customized-trip/"><x-ta-icon name="compass" :size="16" /> {{ $groupsSection['custom_jump_label'] }}</a>
                @endif
            </nav>
        @endif
    </x-page-hero>

    @foreach ($groups as $gi => $g)
        @php
            $priced = $g['items']->contains(fn ($t) => ! $t->price->isOnRequest());
            $note = str_replace(['{count}', '{price}'], [(string) $g['items']->count(), $priced ? '' : 'price on request'], $groupsSection['note'] ?? '{count} itineraries · {price}');
            $note = trim(preg_replace('/\s*·\s*$/u', '', $note));
        @endphp
        <section @class(['section', 'bg-mist' => $gi % 2 === 1]) id="{{ $g['anchor'] }}" aria-labelledby="{{ $g['anchor'] }}-title">
            <div class="wrap">
                <div class="package-group-head" data-reveal>
                    <p class="eyebrow">{{ $g['eyebrow'] }}</p>
                    <h2 id="{{ $g['anchor'] }}-title">{{ $g['title'] }}</h2>
                    <p class="note">{{ $note }}</p>
                </div>
                <div @class(['package-grid', 'package-grid-two' => $g['items']->count() === 4])>
                    @foreach ($g['items'] as $i => $tour)
                        <x-package-card :tour="$tour" :index="$i" />
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach

    @if ($sv = $s['services'] ?? null)
        <section class="section bg-sand">
            <div class="wrap">
                <x-section-heading :data="$sv" />
                <div class="service-cards">
                    @foreach ($sv['items'] ?? [] as $i => $card)
                        <article class="service-card" data-reveal style="--i:{{ $i }}">
                            <div class="media-frame"><x-picture :id="$card['image_id'] ?? null" :alt="$card['alt'] ?? ''" sizes="(max-width: 760px) 100vw, 33vw" /></div>
                            <h3>{{ $card['title'] }}</h3>
                            <p>{{ $card['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if (($c = $s['customise'] ?? null) || $faq)
        <section class="section">
            <div class="wrap split top">
                @if ($c)
                    <div data-reveal>
                        @if (filled($c['eyebrow'] ?? null))<p class="eyebrow">{{ $c['eyebrow'] }}</p>@endif
                        <h2>{{ $c['title'] ?? '' }} @if (filled($c['accent'] ?? null))<span class="accent">{{ $c['accent'] }}</span>@endif</h2>
                        @if (filled($c['text'] ?? null))<p class="lead">{{ $c['text'] }}</p>@endif
                        @if ($site->media($c['image_id'] ?? null))
                            <div class="media-frame" style="margin:32px 0"><x-picture :id="$c['image_id']" :alt="$c['alt'] ?? ''" sizes="(max-width: 1024px) 100vw, 45vw" /></div>
                        @endif
                        <x-cta :link="$c['primary'] ?? null" />
                    </div>
                @endif
                @if ($faq)
                    <div data-reveal style="--i:1">
                        <h3 style="font-size:var(--fs-h3);margin-bottom:8px">{{ $faq['title'] ?? '' }}</h3>
                        <div class="faq">
                            @foreach ($faq['items'] ?? [] as $i => $f)
                                <details @if($i === 0 && ($faq['first_open'] ?? true)) open @endif>
                                    <summary>{{ $f['question'] }}</summary>
                                    <p>{{ $f['answer'] }}</p>
                                </details>
                            @endforeach
                        </div>
                        <x-cta :link="$faq['link'] ?? null" class="text-link" style="margin-top:24px" :iconSize="16" />
                    </div>
                @endif
            </div>
        </section>
    @endif

    <x-enquiry-banner :page="$s['enquiry_banner'] ?? null" />
@endsection
