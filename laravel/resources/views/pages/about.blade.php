@extends('layouts.site')

@section('content')
    <x-page-hero :hero="$s['hero'] ?? []" />

    @if ($in = $s['intro'] ?? null)
        <section class="section">
            <div class="wrap split">
                <div class="media-stack" data-reveal>
                    <div class="media-frame portrait"><x-picture :id="$in['main_image_id'] ?? null" :alt="$in['main_alt'] ?? ''" sizes="(max-width: 1024px) 100vw, 45vw" /></div>
                    @if ($site->media($in['inset_image_id'] ?? null))
                        <div class="inset"><x-picture :id="$in['inset_image_id']" :alt="$in['inset_alt'] ?? ''" sizes="240px" /></div>
                    @endif
                </div>
                <div data-reveal style="--i:1">
                    @if (filled($in['eyebrow'] ?? null))<p class="eyebrow">{{ $in['eyebrow'] }}</p>@endif
                    <h2>{{ $in['title'] ?? '' }} @if (filled($in['accent'] ?? null))<span class="accent">{{ $in['accent'] }}</span>@endif</h2>
                    @foreach ($in['paragraphs'] ?? [] as $paragraph)
                        <p class="lead">{{ $paragraph }}</p>
                    @endforeach
                    @if (! empty($in['checklist']))
                        <ul class="check-list">
                            @foreach ($in['checklist'] as $item)
                                <li><x-ta-icon name="check" :size="20" /><span><strong>{{ $item['title'] }}.</strong> {{ $item['text'] ?? '' }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="btn-row" style="margin-top:32px">
                        <x-cta :link="$in['primary'] ?? null" />
                        @if ($in['show_phone'] ?? true)
                            <a class="btn btn-ghost" href="tel:{{ $site->tel() }}"><x-ta-icon name="phone" :size="18" /> {{ $site->phone() }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($h = $s['hosted'] ?? null)
        <section class="section bg-sand" aria-labelledby="hosted-title">
            <div class="wrap">
                <div class="package-group-head" data-reveal>
                    @if (filled($h['eyebrow'] ?? null))<p class="eyebrow">{{ $h['eyebrow'] }}</p>@endif
                    <h2 id="hosted-title">{{ $h['title'] ?? '' }} @if (filled($h['accent'] ?? null))<span class="accent">{{ $h['accent'] }}</span>@endif</h2>
                    @if (filled($h['text'] ?? null))<p class="lead" style="max-width:60ch">{{ $h['text'] }}</p>@endif
                </div>
                <div class="photo-strip">
                    @foreach ($h['items'] ?? [] as $i => $m)
                        <figure data-reveal style="--i:{{ $i }}">
                            <x-picture :id="$m['image_id'] ?? null" :alt="$m['alt'] ?? ''" sizes="(max-width: 760px) 50vw, 25vw" />
                            <figcaption>{{ $m['caption'] }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($v = $s['values'] ?? null)
        <section class="section bg-ocean">
            <div class="wrap">
                <x-section-heading :data="$v" />
                <div class="value-cards">
                    @foreach ($v['items'] ?? [] as $i => $card)
                        <article class="value-card" data-reveal style="--i:{{ $i }}">
                            <span class="num">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <h3>{{ $card['title'] }}</h3>
                            <p>{{ $card['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($d = $s['destinations'] ?? null)
        <section class="section">
            <div class="wrap">
                <x-section-heading :data="$d" />
                <div class="card-grid-3">
                    @foreach ((int) ($d['count'] ?? 3) > 0 ? $site->places()->take((int) $d['count']) : $site->places() as $i => $place)
                        <x-destination-card :place="$place" :index="$i" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-enquiry-banner :page="$s['enquiry_banner'] ?? null" />
@endsection
