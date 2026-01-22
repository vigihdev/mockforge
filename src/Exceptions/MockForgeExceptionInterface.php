<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Exceptions;

use Throwable;

interface MockForgeExceptionInterface extends Throwable
{
    public function getContext(): array;

    public function getSolutions(): array;

    public function getFormattedMessage(): string;
}
