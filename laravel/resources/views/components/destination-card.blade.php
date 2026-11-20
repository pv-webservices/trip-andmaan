@props(['place', 'sizes' => '(max-width: 760px) 80vw, 33vw', 'index' => 0])
<a class="destination-card" href="{{ $place->url() }}" data-reveal style="--i:{{ $index }}">
    <x-picture :id="$place->image_id" :alt="$place->name.' — '.$place->short" :sizes="$sizes" />
    <div class="destination-label">
        <p class="island-name">{{ $place->official }}</p>
        <h3>{{ $place->name }}</h3>
        <p>{{ $place->short }}</p>
    </div>
    <span class="card-arrow" aria-hidden="true"><x-ta-icon :size="20" /></span>
</a>
