<x-filament-widgets::widget>
    <x-filament::section heading="Waiting to be published" description="Saved drafts that visitors cannot see yet.">
        @if ($items->isEmpty())
            <p>Everything is published.</p>
        @else
            <ul style="display:grid;gap:6px">
                @foreach ($items as $item)
                    <li><a href="{{ $item['url'] }}" style="text-decoration:underline">{{ $item['label'] }}</a></li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
