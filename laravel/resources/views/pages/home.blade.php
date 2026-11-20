@extends('layouts.site')

@section('content')
    @php
        $hero = $s['hero'] ?? [];
        $heroVideo = $site->media($hero['video_id'] ?? null);
        $heroPoster = $site->media($hero['poster_id'] ?? null) ?? $site->media($hero['image_id'] ?? null);
        $film = $s['film_dialog'] ?? null;
        $filmVideo = $site->media($film['video_id'] ?? null);
        $filmPoster = $site->media($film['poster_id'] ?? null);
        $card = $hero['card'] ?? [];
        $secondary = $hero['secondary'] ?? null;
    @endphp
    <section class="hero">
        <div class="hero-media">
            <x-picture :id="$hero['image_id'] ?? null" :mobile="$hero['mobile_image_id'] ?? null" :alt="$hero['alt'] ?? ''" :focal="$hero['focal'] ?? null" eager sizes="100vw" />
            @if ($heroVideo)
                <video data-autoplay muted loop playsinline preload="none" @if($heroPoster) poster="{{ $heroPoster->urlNear(1200) }}" @endif aria-hidden="true"><source src="{{ $heroVideo->url() }}" type="{{ $heroVideo->mime }}"></video>
            @endif
        </div>
        @if (! empty($hero['script_lines']))
            <p class="hero-script" aria-hidden="true">{!! collect($hero['script_lines'])->map(fn ($l) => e($l))->implode('<br />') !!}</p>
        @endif
        <div class="wrap hero-grid">
            <div>
                @if (filled($hero['eyebrow'] ?? null))<p class="eyebrow">{{ $hero['eyebrow'] }}</p>@endif
                <h1>{{ $hero['title'] ?? '' }}</h1>
                @if (filled($hero['tagline_before'] ?? null) || filled($hero['tagline_script'] ?? null))
                    <p class="hero-tagline">{{ $hero['tagline_before'] ?? '' }} @if (filled($hero['tagline_script'] ?? null))<span class="script">{{ $hero['tagline_script'] }}</span>@endif {{ $hero['tagline_after'] ?? '' }}</p>
                @endif
                <p class="hero-copy">{{ $hero['copy'] ?? '' }}</p>
                <div class="btn-row">
                    <x-cta :link="$hero['primary'] ?? null" class="btn btn-sun" />
                    @if (is_array($secondary) && ($secondary['visible'] ?? true) && filled($secondary['label'] ?? null))
                        @if (($hero['secondary_opens_film'] ?? false) && $filmVideo)
                            <a class="btn btn-glass" href="{{ $filmVideo->url() }}" data-open-film><span class="play-dot"><x-ta-icon name="play" :size="13" /></span>{{ $secondary['label'] }}</a>
                        @else
                            <x-cta :link="$secondary" class="btn btn-glass" :icon="false" />
                        @endif
                    @endif
                </div>
            </div>
            @if (($card['visible'] ?? false) && $site->media($card['image_id'] ?? null))
                <aside class="hero-aside" aria-label="Moments from the islands">
                    <div class="hero-card">
                        <x-picture :id="$card['image_id']" :alt="$card['alt'] ?? ''" sizes="330px" />
                        <div class="hero-card-body">
                            <div>
                                <strong>{{ $card['title'] ?? '' }}</strong>
                                <span>{{ $card['text'] ?? '' }}</span>
                            </div>
                            @if ($card['show_call'] ?? true)
                                <a class="circle-btn" href="tel:{{ $site->tel() }}" aria-label="Call Trip Andaman on {{ $site->phone() }}"><x-ta-icon name="phone" :size="18" /></a>
                            @endif
                        </div>
                    </div>
                </aside>
            @endif
        </div>
        @if (isset($s['promises']))
            <a class="hero-scroll" href="#promise">{{ $hero['scroll_label'] ?? 'Scroll' }}</a>
        @endif
    </section>

    @if ($p = $s['promises'] ?? null)
        <div class="wrap promise-strip" id="promise">
            @foreach ($p['items'] ?? [] as $i => $item)
                <div class="promise" data-reveal style="--i:{{ $i }}">
                    <span class="promise-icon"><x-ta-icon :name="$item['icon'] ?? 'arrow'" :size="26" /></span>
                    <div>
                        <h2>{{ $item['title'] }}</h2>
                        <p>{{ $item['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($story = $s['story'] ?? null)
        <section class="section">
            <div class="wrap story-grid">
                <div class="story-copy" data-reveal>
                    @if (filled($story['eyebrow'] ?? null))<p class="eyebrow">{{ $story['eyebrow'] }}</p>@endif
                    <h2>{{ $story['title'] ?? '' }} @if (filled($story['accent'] ?? null))<span class="accent">{{ $story['accent'] }}</span>@endif</h2>
                    @if (filled($story['text'] ?? null))<p class="lead">{{ $story['text'] }}</p>@endif
                    <ul class="pillars">
                        @foreach ($story['pillars'] ?? [] as $pillar)
                            <li><x-ta-icon :name="$pillar['icon'] ?? 'arrow'" :size="22" />{{ $pillar['label'] }}</li>
                        @endforeach
                    </ul>
                    <div class="btn-row">
                        <x-cta :link="$story['primary'] ?? null" />
                        @if ($story['show_phone'] ?? true)
                            <a class="btn btn-ghost" href="tel:{{ $site->tel() }}"><x-ta-icon name="phone" :size="18" /> {{ $site->phone() }}</a>
                        @endif
                    </div>
                </div>
                <div class="story-collage" data-reveal style="--i:1">
                    <figure class="tall"><x-picture :id="$story['tall_image_id'] ?? null" :alt="$story['tall_alt'] ?? ''" sizes="(max-width: 1024px) 50vw, 26vw" /></figure>
                    <figure class="wide"><x-picture :id="$story['wide1_image_id'] ?? null" :alt="$story['wide1_alt'] ?? ''" sizes="(max-width: 1024px) 45vw, 22vw" /></figure>
                    <figure class="wide"><x-picture :id="$story['wide2_image_id'] ?? null" :alt="$story['wide2_alt'] ?? ''" sizes="(max-width: 1024px) 45vw, 22vw" /></figure>
                    @if (filled($story['badge_title'] ?? null))
                        <p class="story-badge">
                            <span><x-ta-icon name="pin" :size="20" /></span>
                            <span>{{ $story['badge_title'] }}<small>{{ $story['badge_text'] ?? '' }}</small></span>
                        </p>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if ($dest = $s['destinations'] ?? null)
        <section class="section bg-mist">
            <div class="wrap">
                <x-section-heading :data="$dest" />
                <div class="dest-bento">
                    @foreach ($site->places()->take((int) ($dest['count'] ?? 5)) as $i => $place)
                        <x-destination-card :place="$place" :index="$i" :sizes="$i === 0 ? '(max-width: 1024px) 80vw, 50vw' : '(max-width: 1024px) 80vw, 25vw'" />
                    @endforeach
                </div>
                @if (filled($dest['hint'] ?? null))
                    <p class="rail-hint" aria-hidden="true">{{ $dest['hint'] }} <x-ta-icon :size="16" /></p>
                @endif
            </div>
        </section>
    @endif

    @if (($xp = $s['experiences'] ?? null) && ! empty($xp['items']))
        <section class="section bg-ocean" aria-labelledby="xp-title">
            <div class="wrap xp-grid">
                <div data-reveal>
                    @if (filled($xp['eyebrow'] ?? null))<p class="eyebrow">{{ $xp['eyebrow'] }}</p>@endif
                    <h2 id="xp-title" style="font-size:var(--fs-h2)">{{ $xp['title'] ?? '' }} @if (filled($xp['accent'] ?? null))<span class="accent">{{ $xp['accent'] }}</span>@endif</h2>
                    @if (filled($xp['text'] ?? null))<p class="lead" style="margin-top:20px;max-width:48ch">{{ $xp['text'] }}</p>@endif
                    <div class="xp-tabs" role="tablist" aria-label="Experiences">
                        @foreach ($xp['items'] as $i => $e)
                            <button class="xp-tab" role="tab" id="xp-tab-{{ $i }}" aria-controls="xp-panel-{{ $i }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}" tabindex="{{ $i === 0 ? 0 : -1 }}">
                                <span class="num">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                <span>{{ $e['title'] }}</span>
                                <x-ta-icon :size="20" />
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="xp-stage" data-reveal style="--i:1">
                    @foreach ($xp['items'] as $i => $e)
                        <div @class(['xp-panel', 'is-active' => $i === 0]) role="tabpanel" id="xp-panel-{{ $i }}" aria-labelledby="xp-tab-{{ $i }}" @if($i !== 0) inert aria-hidden="true" @endif>
                            <x-picture :id="$e['image_id'] ?? null" :alt="$e['alt'] ?? ''" sizes="(max-width: 1024px) 100vw, 55vw" />
                            <div class="xp-caption">
                                <div>
                                    <h3>{{ $e['title'] }}</h3>
                                    <p>{{ $e['text'] }}</p>
                                </div>
                                @if (filled($e['url'] ?? null))
                                    <a class="circle-btn" href="{{ $e['url'] }}" aria-label="Read more about {{ $e['title'] }}"><x-ta-icon name="arrow-up-right" :size="20" /></a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($pk = $s['packages'] ?? null)
        @php $count = $site->tours()->count(); @endphp
        <section class="section">
            <div class="wrap">
                <x-section-heading :data="$pk" :text="str_replace('{count}', (string) $count, $pk['text'] ?? '')" />
                <div class="package-grid">
                    @foreach ($site->featuredTours() as $i => $tour)
                        <x-package-card :tour="$tour" :index="$i" />
                    @endforeach
                </div>
                @if (! empty($pk['planning']))
                    <div style="margin-top:clamp(40px,5vw,72px)">
                        @if (filled($pk['planning_eyebrow'] ?? null))<p class="eyebrow" style="margin-bottom:20px">{{ $pk['planning_eyebrow'] }}</p>@endif
                        <div class="style-rail">
                            @foreach ($pk['planning'] as $i => $plan)
                                <a class="style-card" href="/tour-packages/customized-trip/?interest={{ rawurlencode($plan['interest'] ?? $plan['title']) }}" data-reveal style="--i:{{ $i }}">
                                    <x-picture :id="$plan['image_id'] ?? null" alt="" sizes="(max-width: 760px) 78vw, (max-width: 1120px) 50vw, 25vw" />
                                    @if (filled($plan['tag'] ?? null))<span class="tag">{{ $plan['tag'] }}</span>@endif
                                    <div class="body">
                                        <h3>{{ $plan['title'] }}</h3>
                                        <div class="more"><div><p>{{ $plan['text'] ?? '' }}</p></div></div>
                                        <span class="cta">{{ $pk['planning_cta'] ?? 'Make it your trip' }} <x-ta-icon :size="16" /></span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if (($band = $s['film_band'] ?? null) && ($bandVideo = $site->media($band['video_id'] ?? null)))
        @php $bandPoster = $site->media($band['poster_id'] ?? null); @endphp
        <section class="film-band" aria-labelledby="film-title-band">
            <video data-autoplay muted loop playsinline preload="none" @if($bandPoster) poster="{{ $bandPoster->urlNear(640) }}" @endif aria-hidden="true"><source src="{{ $bandVideo->url() }}" type="{{ $bandVideo->mime }}"></video>
            <div data-reveal>
                @if (filled($band['eyebrow'] ?? null))<p class="eyebrow">{{ $band['eyebrow'] }}</p>@endif
                <h2 id="film-title-band">{{ $band['title'] ?? '' }} @if (filled($band['script'] ?? null))<span class="script">{{ $band['script'] }}</span>@endif</h2>
                @if (filled($band['text'] ?? null))<p class="lead">{{ $band['text'] }}</p>@endif
                <div class="btn-row">
                    @if ($filmVideo && filled($band['film_label'] ?? null))
                        <a class="btn btn-glass" href="{{ $filmVideo->url() }}" data-open-film><span class="play-dot"><x-ta-icon name="play" :size="13" /></span>{{ $band['film_label'] }}</a>
                    @endif
                    @if (filled($band['whatsapp_label'] ?? null))
                        <x-whatsapp-button :subject="$band['whatsapp_subject'] ?? 'snorkelling and scuba diving in the Andamans'">{{ $band['whatsapp_label'] }}</x-whatsapp-button>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if ($mo = $s['moments'] ?? null)
        <section class="section">
            <div class="wrap">
                <x-section-heading :data="$mo" />
                <div class="moments">
                    @foreach ($mo['items'] ?? [] as $i => $m)
                        <figure @class(['moment', 'tall' => $m['tall'] ?? false]) data-reveal @if($i) style="--i:{{ $i }}" @endif>
                            @if (($m['kind'] ?? 'image') === 'video' && ($v = $site->media($m['video_id'] ?? null)))
                                @php $poster = $site->media($m['poster_id'] ?? null); @endphp
                                <video data-autoplay muted loop playsinline preload="none" @if($poster) poster="{{ $poster->urlNear(640) }}" @endif aria-label="{{ $m['alt'] ?? $m['caption'] }}"><source src="{{ $v->url() }}" type="{{ $v->mime }}"></video>
                                <figcaption><span class="live-dot"></span> {{ $m['caption'] }}</figcaption>
                            @else
                                <x-picture :id="$m['image_id'] ?? null" :alt="$m['alt'] ?? ''" :sizes="($m['tall'] ?? false) ? '(max-width: 760px) 100vw, 30vw' : '(max-width: 760px) 50vw, 30vw'" />
                                <figcaption>{{ $m['caption'] }}</figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($st = $s['steps'] ?? null)
        <section class="section bg-sand">
            <div class="wrap steps-grid-wrap">
                <div class="steps-visual" data-reveal>
                    <x-picture :id="$st['image_id'] ?? null" :alt="$st['alt'] ?? ''" sizes="(max-width: 1024px) 100vw, 45vw" />
                    @if (filled($st['script'] ?? null))<span class="script" aria-hidden="true">{{ $st['script'] }}</span>@endif
                </div>
                <div>
                    <div data-reveal>
                        @if (filled($st['eyebrow'] ?? null))<p class="eyebrow">{{ $st['eyebrow'] }}</p>@endif
                        <h2 style="font-size:var(--fs-h2);max-width:14ch">{{ $st['title'] ?? '' }} @if (filled($st['accent'] ?? null))<span class="accent">{{ $st['accent'] }}</span>@endif</h2>
                        @if (filled($st['text'] ?? null))<p class="lead" style="margin-top:18px">{{ $st['text'] }}</p>@endif
                    </div>
                    <ol class="steps" style="list-style:none;padding:0">
                        @foreach ($st['steps'] ?? [] as $i => $step)
                            <li class="step" data-reveal style="--i:{{ $i }}">
                                <span class="step-num">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                <div><h3>{{ $step['title'] }}</h3><p>{{ $step['text'] }}</p></div>
                            </li>
                        @endforeach
                    </ol>
                    <div class="btn-row" style="margin-top:32px" data-reveal>
                        <x-cta :link="$st['primary'] ?? null" class="btn btn-sun on-light" />
                        <x-cta :link="$st['secondary'] ?? null" class="btn btn-ghost" :icon="false" />
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if (($t = $s['testimonials'] ?? null) && ($approved = collect($t['items'] ?? [])->where('approved', true))->isNotEmpty())
        <section class="section soft-section">
            <div class="container">
                <x-section-heading :data="$t" :link="null" :text="null" />
                <div class="steps-grid">
                    @foreach ($approved as $review)
                        <figure class="step-card" style="margin:0" data-reveal>
                            @if ($photo = $site->media($review['photo_id'] ?? null))
                                <img src="{{ $photo->urlNear(200) }}" alt="" width="44" height="44" loading="lazy" style="border-radius:50%;object-fit:cover;margin-bottom:12px">
                            @endif
                            <blockquote style="margin:0 0 16px"><p>{{ $review['text'] }}</p></blockquote>
                            <figcaption><strong>{{ $review['name'] }}</strong></figcaption>
                            @if (($r = (int) ($review['rating'] ?? 0)) >= 1 && $r <= 5)
                                <p aria-label="{{ $r }} out of 5 stars">{{ $r }} / 5</p>
                            @endif
                            @if (filled($review['source_url'] ?? null))
                                <a class="text-link" href="{{ $review['source_url'] }}" target="_blank" rel="noopener noreferrer">Read original review ↗</a>
                            @endif
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($ga = $s['gallery'] ?? null)
        <section class="section">
            <div class="wrap">
                <x-section-heading :data="$ga" />
                <x-gallery :items="$ga['items'] ?? []" compact />
            </div>
        </section>
    @endif

    <x-enquiry-banner :page="$s['enquiry_banner'] ?? null" />

    @if ($film && $filmVideo)
        <dialog class="film-dialog" aria-labelledby="film-title">
            <div class="dialog-top">
                <h2 id="film-title">{{ $film['title'] ?? '' }}</h2>
                <button class="icon-button" data-close-film aria-label="Close film"><x-ta-icon name="close" :size="20" /></button>
            </div>
            <video controls playsinline preload="none" @if($filmPoster) poster="{{ $filmPoster->urlNear(640) }}" @endif aria-label="{{ $film['aria'] ?? '' }}"><source src="{{ $filmVideo->url() }}" type="{{ $filmVideo->mime }}"></video>
            @if (filled($film['caption'] ?? null))<p>{{ $film['caption'] }}</p>@endif
        </dialog>
    @endif
@endsection
