<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Command\Mock\Wp;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Path;
use Vigihdev\MockForge\Exceptions\Handler\{MockForgeHandlerException, MockForgeHandlerExceptionInterface};

abstract class BaseWpCommand extends Command
{

    protected string $out = '';

    protected int $count = 0;

    protected ?MockForgeHandlerExceptionInterface $handlerException = null;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);

        if ($this->handlerException === null) {
            $this->handlerException = new MockForgeHandlerException();
        }
    }

    /**
     * Normalize filepath to absolute path
     *
     * @return string
     */
    protected function normalizeOutFilepath(string $out): string
    {
        if (Path::isAbsolute($out)) {
            $directory = Path::getDirectory($out);
            if ($realpath = realpath($directory)) {
                $filename = pathinfo($out, PATHINFO_FILENAME);
                return Path::join($realpath, $filename);
            }
            throw new \RuntimeException(sprintf('Cannot resolve absolute path for %s', $out));
        }

        return Path::join(getcwd() ?? '', $out);
    }
}
