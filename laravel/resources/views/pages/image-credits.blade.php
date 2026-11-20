@extends('layouts.site')

@section('content')
    @php
        $credits = \App\Models\Media::query()->whereNotNull('credit_author')->whereNotNull('credit_source_url')->orderBy('id')->get();
        $b = $s['body'] ?? [];
    @endphp
    <x-page-hero :hero="$s['hero'] ?? []" />
    <section class="section">
        <div class="legal-layout">
            @if ($credits->isNotEmpty())
                <ul class="credit-list">
                    @foreach ($credits as $c)
                        <li>
                            <strong>{{ $c->credit_title ?: $c->label() }}</strong>
                            <span>{{ $c->credit_author }} · </span>
                            @if (filled($c->credit_license_url))
                                <a href="{{ $c->credit_license_url }}" target="_blank" rel="noopener noreferrer">{{ $c->credit_license }}</a>
                            @else
                                {{ $c->credit_license }}
                            @endif
                            ·
                            <a href="{{ $c->credit_source_url }}" target="_blank" rel="noopener noreferrer">Original photograph ↗</a>
                            @if (filled($c->credit_changes))<p>{{ $c->credit_changes }} Derivative image files retain the source licence.</p>@endif
                        </li>
                    @endforeach
                </ul>
            @endif
            <div class="prose">
                @include('partials.prose-blocks', ['blocks' => $b['blocks'] ?? []])
            </div>
        </div>
    </section>
@endsection
