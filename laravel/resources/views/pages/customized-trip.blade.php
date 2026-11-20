@extends('layouts.site')

@section('content')
    @php $in = $s['intro'] ?? []; @endphp
    <x-page-hero :hero="$s['hero'] ?? []" :crumbs="[['Tour Packages', '/tour-packages/']]" />
    <section class="section bg-mist">
        <div class="wrap contact-grid">
            <div class="contact-panel" data-reveal>
                @if (filled($in['eyebrow'] ?? null))<p class="eyebrow">{{ $in['eyebrow'] }}</p>@endif
                <h2>{{ $in['title'] ?? '' }} @if (filled($in['accent'] ?? null))<span class="accent">{{ $in['accent'] }}</span>@endif</h2>
                @if (filled($in['text'] ?? null))<p class="lead">{{ $in['text'] }}</p>@endif
                @if ($site->media($in['image_id'] ?? null))
                    <div class="media-frame"><x-picture :id="$in['image_id']" :alt="$in['alt'] ?? ''" sizes="(max-width: 1024px) 100vw, 40vw" /></div>
                @endif
                @if (! empty($in['next_items']))
                    <h3 style="font-size:var(--fs-h3);margin-top:32px">{{ $in['next_title'] ?? '' }}</h3>
                    <ul class="check-list">
                        @foreach ($in['next_items'] as $item)<li><x-ta-icon name="check" :size="20" />{{ $item }}</li>@endforeach
                    </ul>
                @endif
                @if (filled($in['note'] ?? null))
                    <p class="note" style="margin-top:20px">
                        {{ $in['note'] }}
                        <a href="tel:{{ $site->tel() }}" style="color:var(--brand);font-weight:700">{{ str_replace('{phone}', $site->phone(), $in['call_label'] ?? 'Call {phone}') }}</a>.
                    </p>
                @endif
            </div>
            <div data-reveal style="--i:1">
                <x-enquiry-form :source="$in['form_source'] ?? 'Plan My Trip page'" />
            </div>
        </div>
    </section>
@endsection
