{{-- body_html is sanitised to a small allow-list when saved (App\Cms\Sanitizer), and again here as defence in depth. --}}
@foreach ($blocks as $block)
    <h2>{{ $block['title'] ?? '' }} @if (filled($block['accent'] ?? null))<span class="accent">{{ $block['accent'] }}</span>@endif</h2>
    {!! \App\Cms\Sanitizer::html($block['body_html'] ?? '') !!}
@endforeach
