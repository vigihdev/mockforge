<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Exceptions\Handler;

use Throwable;
use Symfony\Component\Console\Style\SymfonyStyle;

final class MockForgeHandlerException implements MockForgeHandlerExceptionInterface
{


    /**
     * @param Throwable $e
     * @return void
     */
    public function handle(Throwable $e, SymfonyStyle $io): void
    {
        $io->error($e->getMessage());
    }
}
