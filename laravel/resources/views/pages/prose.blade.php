@extends('layouts.site')

@section('content')
    @php $b = $s['body'] ?? []; @endphp
    <x-page-hero :hero="$s['hero'] ?? []" />
    <section class="section">
        <div class="legal-layout prose">
            @include('partials.prose-blocks', ['blocks' => $b['blocks'] ?? []])
            <x-cta :link="$b['button'] ?? null" :icon="false" />
        </div>
    </section>
@endsection
