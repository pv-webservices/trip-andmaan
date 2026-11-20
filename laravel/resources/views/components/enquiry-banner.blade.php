{{-- Shared "Let’s plan your dream trip" band. $page: this page's enquiry_banner section (visibility + optional image). --}}
@props(['page' => null])
@php $g = $site->global('enquiry_banner'); @endphp
@if (is_array($page) && $g)
    <section class="cta-band">
        <x-picture :id="($page['image_id'] ?? null) ?: ($g['image_id'] ?? null)" alt="" sizes="100vw" />
        <div class="wrap cta-grid">
            <div data-reveal>
                <p class="eyebrow" style="color:var(--sun)">{{ $g['eyebrow'] ?? '' }}</p>
                <h2>{{ $g['title'] ?? '' }} @if (filled($g['script'] ?? null))<span class="script">{{ $g['script'] }}</span>@endif</h2>
                @if (filled($g['text'] ?? null))<p class="lead" style="color:rgb(255 255 255 / .82)">{{ $g['text'] }}</p>@endif
                <div class="btn-row">
                    <x-cta :link="$g['primary'] ?? null" class="btn btn-sun" />
                    <x-whatsapp-button :subject="$g['whatsapp_subject'] ?? 'planning an Andaman trip'">{{ $g['whatsapp_label'] ?? 'WhatsApp Us' }}</x-whatsapp-button>
                </div>
            </div>
            <div class="cta-card on-dark" data-reveal style="--i:1">
                <h3>{{ $g['card_title'] ?? '' }}</h3>
                <x-contact-details />
            </div>
        </div>
    </section>
@endif
