<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ScrapedOpportunityMetadata
{
    public function __construct(
        public ?string $imageUrl = null,
        public ?string $title = null,
        public ?string $description = null,
    ) {}

    public function hasImage(): bool
    {
        return ! empty($this->imageUrl);
    }

    public function hasTitle(): bool
    {
        return ! empty($this->title);
    }

    public function hasDescription(): bool
    {
        return ! empty($this->description);
    }
}
