<?php

namespace App\Cms\Views;

/** Read-only view of a package snapshot (or live data in preview). */
class Tour
{
    public readonly string $slug;

    public readonly string $title;

    public readonly string $overview;

    public readonly int $nights;

    public readonly int $days;

    public readonly bool $featured;

    public readonly int $featuredOrder;

    public readonly int $sort;

    public readonly Price $price;

    public function __construct(public readonly array $data)
    {
        $this->slug = (string) $data['slug'];
        $this->title = (string) $data['title'];
        $this->overview = (string) $data['overview'];
        $this->nights = (int) $data['nights'];
        $this->days = (int) $data['days'];
        $this->featured = (bool) ($data['is_featured'] ?? false);
        $this->featuredOrder = (int) ($data['featured_order'] ?? 0);
        $this->sort = (int) ($data['sort'] ?? 0);
        $this->price = new Price($data['price'] ?? []);
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function url(): string
    {
        return '/tour-packages/'.$this->slug.'/';
    }

    public function durationLabel(): string
    {
        return "{$this->nights} Nights / {$this->days} Days";
    }

    public function routeLabel(): string
    {
        return collect($this->data['stays'] ?? [])
            ->map(fn ($s) => ((int) ($s['nights'] ?? 0)).'N '.($s['place'] ?? ''))
            ->implode(' · ');
    }

    /** @return list<array{title:string, paragraphs:list<string>}> */
    public function itinerary(): array
    {
        return $this->data['itinerary'] ?? [];
    }

    public function mentions(string $needle): bool
    {
        return collect($this->itinerary())->contains(fn ($d) => str_contains($d['title'] ?? '', $needle));
    }
}
