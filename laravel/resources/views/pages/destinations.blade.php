@extends('layouts.site')

@section('content')
    <x-page-hero :hero="$s['hero'] ?? []" />
    @if ($list = $s['list'] ?? null)
        <section class="section">
            <div class="wrap">
                <x-section-heading :data="$list" />
                <div class="dest-grid">
                    @foreach ((int) ($list['count'] ?? 0) > 0 ? $site->places()->take((int) $list['count']) : $site->places() as $i => $place)
                        <x-destination-card :place="$place" :index="$i" :sizes="$i < 2 ? '(max-width: 760px) 100vw, 50vw' : '(max-width: 760px) 100vw, 33vw'" />
                    @endforeach
                </div>
                @if (filled($list['note'] ?? null))<p class="note" style="margin-top:24px">{{ $list['note'] }}</p>@endif
            </div>
        </section>
    @endif
    <x-enquiry-banner :page="$s['enquiry_banner'] ?? null" />
@endsection
