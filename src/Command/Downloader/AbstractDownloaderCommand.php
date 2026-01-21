<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Command\Downloader;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Path;
use Vigihdev\MockForge\Exceptions\Handler\{MockForgeHandlerException, MockForgeHandlerExceptionInterface};
use Vigihdev\Support\TempFileManager;
use Vigihdev\Support\Contracts\TempFileManagerInterface;

abstract class AbstractDownloaderCommand extends Command
{

    protected string $out = '';

    protected int $count = 0;


    protected ?MockForgeHandlerExceptionInterface $handlerException = null;

    /**
     * @var Te $tempManager
     */
    protected ?TempFileManagerInterface $tempManager = null;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);

        if ($this->handlerException === null) {
            $this->handlerException = new MockForgeHandlerException();
        }

        if ($this->tempManager === null) {
            $this->tempManager = new TempFileManager();
        }
    }


    /**
     * Normalize filepath to absolute path
     *
     * @return string
     */
    protected function normalizeOutpath(string $out): string
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
