@extends('layouts.site')

@section('content')
    @php $b = $s['body'] ?? []; @endphp
    <section class="not-found">
        <x-picture :id="$b['image_id'] ?? null" alt="" eager sizes="100vw" />
        <div>
            @if (filled($b['eyebrow'] ?? null))<p class="eyebrow" style="color:var(--sun)">{{ $b['eyebrow'] }}</p>@endif
            @if (filled($b['number'] ?? null))<p class="error-number">{{ $b['number'] }}</p>@endif
            <h1>{{ $b['title'] ?? 'Page not found' }}</h1>
            <p>{{ $b['text'] ?? '' }}</p>
            <nav aria-label="Helpful links" class="not-found-links">
                <ul>
                    @foreach ($site->g('navigation', 'items', []) as $item)<li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>@endforeach
                </ul>
            </nav>
            <div class="btn-row">
                <a class="btn btn-sun" href="/">{{ $b['home_label'] ?? 'Back to Home' }} <x-ta-icon :size="18" /></a>
                <a class="btn btn-whatsapp" href="{{ $site->whatsappFor($b['whatsapp_subject'] ?? 'an Andaman trip (I reached a missing page on your website)') }}" target="_blank" rel="noopener noreferrer"><x-wa-icon :size="18" /> {{ $b['whatsapp_label'] ?? 'WhatsApp us' }}</a>
                <a class="btn btn-glass" href="tel:{{ $site->tel() }}"><x-ta-icon name="phone" :size="18" /> {{ str_replace('{phone}', $site->phone(), $b['call_label'] ?? 'Call {phone}') }}</a>
            </div>
        </div>
    </section>
@endsection
