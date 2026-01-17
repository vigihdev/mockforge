<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Contracts\Faker;

interface MockGeneratorInterface
{
    /**
     * @param string $class FQCN of class to mock
     * @return array<string, mixed> Array of mocked data
     */
    public function generate(string $class): array;
}
