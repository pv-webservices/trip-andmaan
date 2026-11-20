{{-- Popup campaign. The key includes the campaign version, so a changed campaign is not hidden by an earlier dismissal. --}}
@php
    $image = $site->media($popup->image_id);
    $headingId = 'promo-title-'.$popup->id;
@endphp
<dialog class="promo-dialog" data-popup data-popup-key="ta-popup:{{ $popup->id }}:{{ $popup->campaign_version }}" data-frequency="{{ $popup->frequency }}" data-delay="{{ $popup->delay_seconds }}" @if(filled($popup->heading)) aria-labelledby="{{ $headingId }}" @else aria-label="{{ $popup->image_alt ?: 'Announcement' }}" @endif>
    <button class="icon-button promo-close" type="button" aria-label="Close"><x-ta-icon name="close" :size="20" /></button>
    @if ($image)
        <img src="{{ $image->urlNear(800) }}" srcset="{{ $image->srcset() }}" sizes="(max-width: 600px) 100vw, 560px" alt="{{ $popup->image_alt ?? '' }}" @if($image->displayWidth()) width="{{ $image->displayWidth() }}" height="{{ $image->displayHeight() }}" @endif loading="lazy" decoding="async">
    @endif
    @if (filled($popup->heading) || filled($popup->description) || filled($popup->button_url))
        <div class="promo-body">
            @if (filled($popup->heading))<h2 id="{{ $headingId }}">{{ $popup->heading }}</h2>@endif
            @if (filled($popup->description))<p>{{ $popup->description }}</p>@endif
            @if (filled($popup->button_url) && filled($popup->button_label))
                <a class="btn btn-sun on-light" href="{{ $popup->button_url }}" @if(str_starts_with($popup->button_url, 'http')) target="_blank" rel="noopener noreferrer" @endif>{{ $popup->button_label }} <x-ta-icon :size="18" /></a>
            @endif
        </div>
    @endif
</dialog>
