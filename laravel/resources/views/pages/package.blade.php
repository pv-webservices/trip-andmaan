@php
    $l = $s['labels'] ?? [];
    $duration = $tour->durationLabel();
    $clamp = function (string $text, int $max = 160): string {
        $clean = trim(preg_replace('/\s+/u', ' ', $text));
        if (mb_strlen($clean) <= $max) {
            return $clean;
        }
        $cut = mb_substr($clean, 0, $max - 1);

        return rtrim(mb_substr($cut, 0, mb_strrpos($cut, ' ')), ",;:. —-").'…';
    };
    $title = filled($tour->meta_title) ? $tour->meta_title
        : str_replace(['{title}', '{duration}'], [$tour->title, $duration], $l['meta_title'] ?? '{title} — {duration} Andaman Tour');
    $description = $clamp(filled($tour->meta_description) ? $tour->meta_description
        : str_replace(['{duration}', '{overview}'], [$duration, $tour->overview], $l['meta_description'] ?? '{duration} Andaman tour. {overview}'));
    $interest = "{$tour->title} ({$duration})";
    $enquirySubject = "the {$interest} package";
    $notes = [...($s['default_notes']['notes'] ?? []), ...($tour->notes ?? [])];
    $pdf = $site->media($tour->itinerary_pdf_id);
    $related = $site->tours()
        ->reject(fn ($t) => $t->slug === $tour->slug)
        ->sortBy(fn ($t) => abs($t->nights - $tour->nights) * 1000 + $t->sort)
        ->take((int) ($l['related_count'] ?? 3))
        ->values();
    $whatsapp = $site->whatsappFor("the {$interest} package and would like a quote");
@endphp
@extends('layouts.site')

@section('content')
    <x-page-hero :hero="['title' => $tour->title, 'description' => $tour->overview, 'eyebrow' => $duration, 'image_id' => $tour->image_id, 'mobile_image_id' => $tour->mobile_image_id, 'alt' => $tour->image_alt, 'focal' => $tour->focal !== '50% 50%' ? $tour->focal : null]" :crumbs="[['Tour Packages', '/tour-packages/']]">
        <ul class="route-chips" aria-label="Where you stay">
            @foreach ($tour->stays ?? [] as $stay)
                <li><strong>{{ (int) $stay['nights'] }}N</strong> {{ $stay['place'] }}</li>
            @endforeach
        </ul>
    </x-page-hero>
    <section class="section">
        <div class="wrap detail-grid">
            <div>
                <div data-reveal>
                    <p class="eyebrow">{{ $l['itinerary_eyebrow'] ?? 'Day by day' }}</p>
                    <h2 style="font-size:var(--fs-h2)">{{ $l['itinerary_title'] ?? 'Your' }} <span class="accent">{{ $l['itinerary_accent'] ?? 'itinerary' }}</span></h2>
                    @if ($pdf)
                        <a class="btn btn-ghost itinerary-download" href="{{ $pdf->url() }}" target="_blank" rel="noopener"><x-ta-icon name="download" :size="18" /> {{ $l['pdf_label'] ?? 'Download Itinerary (PDF)' }}</a>
                    @endif
                </div>
                <ol class="itinerary">
                    @foreach ($tour->itinerary() as $i => $day)
                        <li class="itinerary-day">
                            <details @if($i === 0) open @endif>
                                <summary>
                                    <span class="itinerary-num">Day {{ $i + 1 }}</span>
                                    <span class="itinerary-title">{{ $day['title'] }}</span>
                                </summary>
                                <div class="itinerary-body">
                                    @foreach ($day['paragraphs'] as $para)<p>{{ $para }}</p>@endforeach
                                </div>
                            </details>
                        </li>
                    @endforeach
                </ol>
                @if (! empty($tour->inclusions) || ! empty($tour->exclusions))
                    <div class="package-lists" data-reveal>
                        @if (! empty($tour->inclusions))
                            <div>
                                <h2>{{ $l['inclusions_title'] ?? 'Included' }}</h2>
                                <ul class="check-list">@foreach ($tour->inclusions as $item)<li><x-ta-icon name="check" :size="20" />{{ $item }}</li>@endforeach</ul>
                            </div>
                        @endif
                        @if (! empty($tour->exclusions))
                            <div>
                                <h2>{{ $l['exclusions_title'] ?? 'Not included' }}</h2>
                                <ul class="notice-list">@foreach ($tour->exclusions as $item)<li>{{ $item }}</li>@endforeach</ul>
                            </div>
                        @endif
                    </div>
                @endif
                @if ($notes)
                    <div class="notice" style="margin-top:36px" data-reveal>
                        <x-ta-icon name="compass" :size="22" />
                        <div>
                            <p><strong>{{ $l['notes_title'] ?? 'Good to know' }}</strong></p>
                            <ul class="notice-list">@foreach ($notes as $note)<li>{{ $note }}</li>@endforeach</ul>
                        </div>
                    </div>
                @endif
                @php $moments = array_values(array_filter($tour->gallery ?? [], fn ($m) => $site->media($m['media_id'] ?? null))); @endphp
                @if ($moments)
                    <div style="margin-top:48px" data-reveal>
                        <h2 style="font-size:clamp(1.6rem, 1.2rem + 1.4vw, 2.4rem)">{{ $l['moments_title'] ?? 'Moments from' }} <span class="accent">{{ $l['moments_accent'] ?? 'this route' }}</span></h2>
                        @if (filled($l['moments_note'] ?? null))<p class="note" style="margin-top:8px">{{ $l['moments_note'] }}</p>@endif
                        <div class="route-moments">
                            @foreach ($moments as $m)
                                <figure>
                                    <x-picture :id="$m['media_id']" :alt="($m['alt'] ?? null) ?: ($m['caption'] ?? null) ?: 'A Trip Andaman travel moment'" sizes="(max-width: 760px) 50vw, 20vw" />
                                    <figcaption>{{ $m['caption'] ?? '' }}</figcaption>
                                </figure>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            <aside class="info-panel">
                <p class="eyebrow">{{ $duration }}</p>
                <h2>{{ $l['aside_title'] ?? 'Enquire about this tour' }}</h2>
                <dl class="package-facts">
                    <div><dt>{{ $l['route_label'] ?? 'Route' }}</dt><dd>{{ $tour->routeLabel() }}</dd></div>
                    <div>
                        <dt>{{ $l['price_label'] ?? 'Price' }}</dt>
                        @if ($tour->price->isOnRequest())
                            <dd>{{ $l['on_request_text'] ?? 'On request, for your dates and hotel choice' }}</dd>
                        @else
                            <dd>
                                <span class="package-price">{{ $tour->price->label() }}@if ($tour->price->original())<s aria-label="Original price {{ $tour->price->original() }}">{{ $tour->price->original() }}</s>@endif</span>
                                @if ($tour->price->note())<small style="display:block">{{ $tour->price->note() }}</small>@endif
                            </dd>
                        @endif
                    </div>
                </dl>
                <a class="btn btn-whatsapp" href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer"><x-wa-icon :size="18" /> {{ $l['whatsapp_label'] ?? 'Get a Quote on WhatsApp' }}</a>
                @if ($pdf)
                    <a class="btn btn-glass" href="{{ $pdf->url() }}" target="_blank" rel="noopener" style="margin-top:10px"><x-ta-icon name="download" :size="18" /> {{ $l['pdf_short_label'] ?? 'Itinerary PDF' }}</a>
                @endif
                <div class="divider"></div>
                <a class="call" href="tel:{{ $site->tel() }}"><span><x-ta-icon name="phone" :size="18" /></span>{{ $site->phone() }}</a>
                <a class="text-link" href="/tour-packages/">{{ $l['all_label'] ?? 'See all packages' }} <x-ta-icon :size="16" /></a>
            </aside>
        </div>
    </section>
    @if ($related->isNotEmpty())
        <section class="section bg-mist">
            <div class="wrap">
                <x-section-heading :eyebrow="$l['related_eyebrow'] ?? 'Compare options'" :title="$l['related_title'] ?? 'Similar'" :accent="$l['related_accent'] ?? 'packages'" :link="['url' => '/tour-packages/', 'label' => $l['related_link'] ?? 'All Packages']" />
                <div class="package-grid">
                    @foreach ($related as $i => $p)<x-package-card :tour="$p" :index="$i" />@endforeach
                </div>
            </div>
        </section>
    @endif
    <x-enquiry-banner :page="$s['enquiry_banner'] ?? null" />
@endsection
