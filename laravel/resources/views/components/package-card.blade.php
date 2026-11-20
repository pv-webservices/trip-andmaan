@props(['tour', 'index' => 0])
<a class="package-card" href="{{ $tour->url() }}" data-reveal style="--i:{{ $index % 3 }}">
    <div class="package-media">
        <x-picture :id="$tour->image_id" alt="" :focal="$tour->focal !== '50% 50%' ? $tour->focal : null" sizes="(max-width: 760px) 100vw, (max-width: 1120px) 50vw, 33vw" />
        <span class="package-duration">{{ $tour->durationLabel() }}</span>
    </div>
    <div class="package-body">
        <h3>{{ $tour->title }}</h3>
        <p class="package-route"><x-ta-icon name="route" :size="16" />{{ $tour->routeLabel() }}</p>
        <p>{{ $tour->overview }}</p>
        @unless ($tour->price->isOnRequest())
            <p class="package-price">{{ $tour->price->label() }}@if ($tour->price->original())<s aria-label="Original price {{ $tour->price->original() }}">{{ $tour->price->original() }}</s>@endif</p>
        @endunless
        <ul class="package-tags" aria-label="Highlights">
            @foreach ($tour->tags ?? [] as $tag)<li>{{ $tag }}</li>@endforeach
        </ul>
        <span class="text-link">View day-by-day itinerary <x-ta-icon :size="16" /></span>
    </div>
</a>
