<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Contracts\Able;

interface JsonAbleInterface
{
    public function toJson(): string;
}
