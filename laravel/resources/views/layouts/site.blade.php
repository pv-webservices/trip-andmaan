@php
    $seo = $site->global('seo');
    $contact = $site->global('contact');
    $nav = $site->g('navigation', 'items', []);
    $path = request()->getPathInfo();
    $path = str_starts_with($path, '/preview') ? (substr($path, 8) ?: '/') : $path;
    $noindex = ($noindex ?? false) || $site->isPreview();

    $title = $title ?? ($meta['title'] ?? ($seo['site_name'] ?? 'Trip Andaman'));
    $description = $description ?? ($meta['description'] ?? '');
    $branded = $title.($seo['title_suffix'] ?? ' | Trip Andaman');
    $fullTitle = preg_match('/trip andaman/i', $title) || mb_strlen($branded) > 65 ? $title : $branded;
    $canonical = $site->absolute($path);

    $ogMedia = $site->media($ogImageId ?? null) ?? $site->media($meta['og_image_id'] ?? null) ?? $site->media($seo['og_image_id'] ?? null);
    $ogAlt = $ogMedia ? (($meta['og_alt'] ?? null) ?: ($seo['og_alt'] ?? '')) : 'Turquoise lagoon and white sand on Havelock Island, Andaman';
    $og = $ogMedia
        ? ['url' => $site->absolute($ogMedia->urlNear(1200)), 'type' => $ogMedia->mime, 'w' => $ogMedia->displayWidth(), 'h' => $ogMedia->displayHeight()]
        : ['url' => $site->absolute('/images/brand/og-image-1200x630.jpg'), 'type' => 'image/jpeg', 'w' => 1200, 'h' => 630];

    $section = collect($nav)->first(fn ($item) => ($item['url'] ?? '/') !== '/' && str_starts_with($path, $item['url']));
    $pageWhatsapp = $site->whatsappFor($enquirySubject ?? ($section
        ? ($contact['default_subject'] ?? 'an Andaman trip').' (from your '.$section['label'].' page)'
        : ($contact['default_subject'] ?? 'an Andaman trip')));
    $active = fn (string $href) => $href === '/' ? $path === '/' : str_starts_with($path, $href);

    $org = $site->absolute('/').'#organization';
    $graph = [
        [
            '@type' => 'TravelAgency',
            '@id' => $org,
            'name' => $contact['business_name'] ?? 'Trip Andaman',
            'url' => $site->absolute('/'),
            'logo' => ['@type' => 'ImageObject', 'url' => $site->absolute('/images/brand/logo-800x221.png'), 'width' => 800, 'height' => 221],
            'image' => $og['url'],
            'telephone' => $site->tel(),
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $contact['street'] ?? null,
                'addressLocality' => $contact['locality'] ?? null,
                'addressRegion' => $contact['region'] ?? null,
                'postalCode' => $contact['postal_code'] ?? null,
                'addressCountry' => $contact['country'] ?? null,
            ]),
            'areaServed' => $contact['area_served'] ?? 'Andaman and Nicobar Islands',
            'contactPoint' => ['@type' => 'ContactPoint', 'telephone' => $site->tel(), 'contactType' => 'customer service'],
        ],
        [
            '@type' => 'WebSite',
            '@id' => $site->absolute('/').'#website',
            'url' => $site->absolute('/'),
            'name' => $seo['site_name'] ?? 'Trip Andaman',
            'inLanguage' => 'en-IN',
            'publisher' => ['@id' => $org],
        ],
        ...($schema ?? []),
    ];
    $announcements = $site->announcements($pageKey ?? '');
    $bar = $site->global('announcement_bar');
    $popups = $site->popups($pageKey ?? '');
    $wa = $site->global('whatsapp');
    $header = $site->global('header');
    $footer = $site->global('footer');
@endphp
<!doctype html>
<html lang="en-IN" @if($site->isPreview()) data-preview="true" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="theme-color" content="#0c2740">
    <meta name="robots" content="{{ $noindex ? 'noindex, follow' : 'index, follow, max-image-preview:large' }}">
    @unless ($noindex)<link rel="canonical" href="{{ $canonical }}">@endunless
    <meta property="og:site_name" content="{{ $seo['site_name'] ?? 'Trip Andaman' }}">
    <meta property="og:locale" content="en_IN">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    @unless ($noindex)<meta property="og:url" content="{{ $canonical }}">@endunless
    <meta property="og:image" content="{{ $og['url'] }}">
    <meta property="og:image:type" content="{{ $og['type'] }}">
    @if ($og['w'])<meta property="og:image:width" content="{{ $og['w'] }}">
    <meta property="og:image:height" content="{{ $og['h'] }}">@endif
    <meta property="og:image:alt" content="{{ $ogAlt }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $og['url'] }}">
    <link rel="icon" href="/favicon.ico" sizes="48x48">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon-96x96.png" type="image/png" sizes="96x96">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="manifest" href="/site.webmanifest">
    @foreach (\App\Cms\Assets::fontPreloads() as $font)
        <link rel="preload" href="{{ $font }}" as="font" type="font/woff2" crossorigin>
    @endforeach
    <script>document.documentElement.classList.add('js');</script>
    @vite(['resources/css/site.css', 'resources/js/site-entry.ts'])
    <script type="application/ld+json">{!! \App\Cms\JsonLd::graph($graph) !!}</script>
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    @if ($announcements->isNotEmpty())
        @include('partials.announcement-bar', ['announcements' => $announcements, 'bar' => $bar])
    @endif
    <header class="site-header">
        <div class="header-bar">
            <a class="brand" href="/" aria-label="Trip Andaman home">
                <img src="/images/brand/logo-light-400.webp" srcset="/images/brand/logo-light-240.webp 240w, /images/brand/logo-light-400.webp 400w" sizes="200px" width="400" height="111" alt="Trip Andaman">
            </a>
            <nav class="desktop-nav" aria-label="Main navigation">
                @foreach ($nav as $item)
                    <a href="{{ $item['url'] }}" @if($active($item['url'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </nav>
            <div class="header-actions">
                <a class="header-call" href="tel:{{ $site->tel() }}">
                    <span><x-ta-icon name="phone" :size="17" /></span>
                    <span><small>{{ $header['call_label'] ?? 'Call us' }}</small>{{ $site->phone() }}</span>
                </a>
                @if (filled($header['cta']['url'] ?? null))
                    <a class="btn btn-sun on-light btn-sm header-cta" href="{{ $header['cta']['url'] }}">{{ $header['cta']['label'] ?? '' }} <x-ta-icon :size="16" /></a>
                @endif
                <button class="menu-toggle" aria-expanded="false" aria-controls="mobile-menu" aria-label="Open navigation"><span></span><span></span><span></span></button>
            </div>
        </div>
    </header>
    <dialog id="mobile-menu" class="mobile-menu" aria-label="Navigation">
        <div class="mobile-menu-top">
            <img src="/images/brand/logo-dark-400.webp" width="400" height="111" alt="Trip Andaman" loading="lazy">
            <button class="menu-close icon-button" aria-label="Close navigation"><x-ta-icon name="close" :size="20" /></button>
        </div>
        <nav aria-label="Mobile navigation">
            @foreach ($nav as $i => $item)
                <a href="{{ $item['url'] }}" @if($active($item['url'])) aria-current="page" @endif><small>{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</small>{{ $item['label'] }}<x-ta-icon name="arrow-up-right" :size="18" /></a>
            @endforeach
        </nav>
        <div class="mobile-menu-foot">
            @if (filled($header['cta']['url'] ?? null))
                <a class="btn btn-sun" href="{{ $header['cta']['url'] }}">{{ $header['cta']['label'] ?? '' }} <x-ta-icon :size="18" /></a>
            @endif
            <a class="btn btn-glass" href="tel:{{ $site->tel() }}"><x-ta-icon name="phone" :size="18" /> Call {{ $site->phone() }}</a>
            @if (filled($header['mobile_tagline'] ?? null))<p>{{ $header['mobile_tagline'] }}</p>@endif
        </div>
    </dialog>
    <noscript>
        <nav class="no-js-nav" aria-label="Site navigation">
            @foreach ($nav as $item)<a href="{{ $item['url'] }}">{{ $item['label'] }}</a>@endforeach
        </nav>
    </noscript>
    <main id="main" tabindex="-1">
        @yield('content')
    </main>
    <footer class="site-footer">
        <div class="wrap footer-grid">
            <div class="footer-brand">
                <a href="/" aria-label="Trip Andaman home"><img src="/images/brand/logo-dark-400.webp" width="400" height="111" alt="Trip Andaman" loading="lazy"></a>
                <p>{{ $footer['about'] ?? '' }}</p>
                @if (filled($footer['tagline'] ?? null))<span class="script">{{ $footer['tagline'] }}</span>@endif
                @if (! empty($footer['social_links']))
                    <ul class="social-links">
                        @foreach ($footer['social_links'] as $link)
                            @if (filled($link['url'] ?? null))<li><a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">{{ $link['label'] }}</a></li>@endif
                        @endforeach
                    </ul>
                @endif
            </div>
            <div>
                <h2>{{ $footer['explore_heading'] ?? 'Explore' }}</h2>
                <ul>
                    @foreach (array_slice($nav, 1) as $item)<li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>@endforeach
                </ul>
            </div>
            <div>
                <h2>{{ $footer['islands_heading'] ?? 'The islands' }}</h2>
                <ul>
                    @foreach ($site->places() as $place)<li><a href="{{ $place->url() }}">{{ $place->name }}</a></li>@endforeach
                </ul>
            </div>
            <div class="on-dark">
                <h2>{{ $footer['contact_heading'] ?? 'Get in touch' }}</h2>
                <x-contact-details />
            </div>
        </div>
        <div class="footer-bottom">
            <div class="wrap">
                <p>{{ str_replace('{year}', now()->year, $footer['copyright'] ?? '© {year} Trip Andaman. All rights reserved.') }}</p>
                <nav aria-label="Legal and information">
                    @foreach ($footer['legal_links'] ?? [] as $link)<a href="{{ $link['url'] }}">{{ $link['label'] }}</a>@endforeach
                </nav>
            </div>
        </div>
    </footer>
    {{-- Desktop/tablet chat widget. The button is a real link, so it still opens WhatsApp without JavaScript. --}}
    <div class="wa-widget">
        <div class="wa-panel" id="wa-panel" role="dialog" aria-labelledby="wa-panel-title" hidden>
            <div class="wa-panel-head">
                <span class="wa-avatar"><x-wa-icon :size="22" /></span>
                <div>
                    <p id="wa-panel-title">{{ $wa['panel_title'] ?? 'Trip Andaman' }}</p>
                    <span>{{ $wa['panel_subtitle'] ?? '' }}</span>
                </div>
                <button class="wa-close" type="button" aria-label="Close WhatsApp chat"><x-ta-icon name="close" :size="18" /></button>
            </div>
            <div class="wa-panel-body"><p class="wa-bubble">{{ $wa['bubble'] ?? '' }}</p></div>
            <a class="wa-start" href="{{ $pageWhatsapp }}" target="_blank" rel="noopener noreferrer"><x-wa-icon :size="20" /> {{ $wa['start_label'] ?? 'Start chat on WhatsApp' }}</a>
        </div>
        <a class="wa-fab" href="{{ $pageWhatsapp }}" target="_blank" rel="noopener noreferrer" aria-controls="wa-panel" aria-expanded="false" aria-label="Chat with Trip Andaman on WhatsApp">
            <span class="wa-fab-label" aria-hidden="true">{{ $wa['fab_label'] ?? 'Chat with us' }}</span>
            <span class="wa-fab-icon"><x-wa-icon :size="30" /></span>
        </a>
    </div>
    <nav class="quick-actions" aria-label="Quick contact">
        <a class="btn btn-glass" href="tel:{{ $site->tel() }}"><x-ta-icon name="phone" :size="17" /> {{ $wa['quick_call_label'] ?? 'Call' }}</a>
        <a class="btn btn-whatsapp" href="{{ $pageWhatsapp }}" target="_blank" rel="noopener noreferrer"><x-wa-icon :size="18" /> {{ $wa['quick_whatsapp_label'] ?? 'WhatsApp' }}</a>
        <a class="btn btn-sun" href="{{ $wa['quick_plan_url'] ?? '/tour-packages/customized-trip/' }}">{{ $wa['quick_plan_label'] ?? 'Plan My Trip' }}</a>
    </nav>
    @foreach ($popups as $popup)
        @include('partials.popup', ['popup' => $popup])
    @endforeach
    @if ($site->isPreview())
        <div class="preview-bar" role="status">Preview — drafts are shown. Visitors cannot see this page. <a href="/admin">Back to admin</a></div>
    @endif
</body>
</html>
