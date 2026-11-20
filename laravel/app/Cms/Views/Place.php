<?php

namespace App\Cms\Views;

/** Read-only view of a destination snapshot (or live data in preview). */
class Place
{
    public readonly string $slug;

    public readonly string $name;

    public readonly string $official;

    public readonly string $short;

    public readonly string $intro;

    public readonly int $sort;

    public function __construct(public readonly array $data)
    {
        $this->slug = (string) $data['slug'];
        $this->name = (string) $data['name'];
        $this->official = (string) $data['official'];
        $this->short = (string) $data['short'];
        $this->intro = (string) $data['intro'];
        $this->sort = (int) ($data['sort'] ?? 0);
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
        return '/destinations/'.$this->slug.'/';
    }
}
