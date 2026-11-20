<?php

namespace App\Cms\Sections;

use Closure;

/** Definition of one kind of section: its label, help text and admin form. */
class SectionType
{
    /** @param Closure(): array $schema */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $description,
        private readonly Closure $schema,
        public readonly bool $hideable = true,
        private readonly ?Closure $validator = null,
    ) {}

    /** Extra checks beyond field rules. @return list<string> */
    public function errors(array $data): array
    {
        return $this->validator ? ($this->validator)($data) : [];
    }

    public function schema(): array
    {
        return ($this->schema)();
    }
}
