<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Contracts\Able;

interface CompactArrayAbleInterface
{
    public function toArray(): array;

    public static function fromArray(array $data): static;
}
