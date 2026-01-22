<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Exceptions;

use Symfony\Component\Console\Style\StyleInterface;
use Throwable;

class MockForgeException extends AbstractMockForgeException
{

    public static function handleThrowableWithIo(Throwable|MockForgeExceptionInterface $e, StyleInterface $io): void
    {
        $io->error(sprintf("%s", $e->getMessage()));
    }

    public static function handleThrowable(
        Throwable|MockForgeExceptionInterface $e,
        array $context = [],
        array $solutions = []
    ): self {

        $context = method_exists($e, 'getContext') && is_array($e->getContext()) ? $e->getContext() : $context;
        $solutions = method_exists($e, 'getSolutions') && is_array($e->getSolutions()) ? $e->getSolutions() : $solutions;

        return new self(
            message: $e->getMessage(),
            code: $e->getCode(),
            previous: $e,
            context: $context,
            solutions: $solutions
        );
    }
}
