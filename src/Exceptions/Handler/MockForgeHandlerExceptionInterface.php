<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Exceptions\Handler;

use Throwable;
use Symfony\Component\Console\Style\SymfonyStyle;

interface MockForgeHandlerExceptionInterface
{
    public function handle(Throwable $e, SymfonyStyle $io): void;
}
