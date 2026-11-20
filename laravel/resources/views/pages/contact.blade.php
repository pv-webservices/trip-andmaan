@extends('layouts.site')

@section('content')
    @php
        $p = $s['panel'] ?? [];
        $cards = [
            ['icon' => 'phone', 'label' => $p['call_label'] ?? 'Call us', 'value' => $site->phone(), 'href' => 'tel:'.$site->tel(), 'external' => false],
            ['icon' => 'whatsapp', 'label' => $p['whatsapp_label'] ?? 'WhatsApp', 'value' => $site->phone(), 'href' => $site->whatsappFor($p['whatsapp_subject'] ?? 'an Andaman trip (from your Contact page)'), 'external' => true],
            ['icon' => 'pin', 'label' => $p['map_label'] ?? 'Find us', 'value' => $site->g('contact', 'address'), 'href' => $site->g('contact', 'map_url'), 'external' => true],
        ];
    @endphp
    <x-page-hero :hero="$s['hero'] ?? []" />
    <section class="section bg-mist">
        <div class="wrap contact-grid">
            <div class="contact-panel" data-reveal>
                @if (filled($p['eyebrow'] ?? null))<p class="eyebrow">{{ $p['eyebrow'] }}</p>@endif
                <h2>{{ $p['title'] ?? '' }} @if (filled($p['accent'] ?? null))<span class="accent">{{ $p['accent'] }}</span>@endif</h2>
                @if (filled($p['text'] ?? null))<p class="lead">{{ $p['text'] }}</p>@endif
                <div class="contact-cards">
                    @foreach ($cards as $c)
                        <a class="contact-card" href="{{ $c['href'] }}" @if($c['external']) target="_blank" rel="noopener noreferrer" @endif>
                            <span @class(['contact-icon', 'wa' => $c['icon'] === 'whatsapp'])>
                                @if ($c['icon'] === 'whatsapp')<x-wa-icon :size="22" />@else<x-ta-icon :name="$c['icon']" :size="22" />@endif
                            </span>
                            <span><small>{{ $c['label'] }}</small><strong>{{ $c['value'] }}</strong></span>
                            <x-ta-icon name="arrow-up-right" :size="18" />
                        </a>
                    @endforeach
                </div>
                @if (filled($p['note'] ?? null))<p class="note" style="margin-top:16px">{{ $p['note'] }}</p>@endif
            </div>
            <div data-reveal style="--i:1">
                <x-enquiry-form :source="$p['form_source'] ?? 'Contact page'" />
            </div>
        </div>
    </section>
@endsection
