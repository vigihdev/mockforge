<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Command\Mock\Wp;

use Symfony\Component\Console\Command\Command;
use Vigihdev\MockForge\Exceptions\Handler\{MockForgeHandlerException, MockForgeHandlerExceptionInterface};

abstract class BaseWpCommand extends Command
{

    protected ?MockForgeHandlerExceptionInterface $handlerException = null;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);

        if ($this->handlerException === null) {
            $this->handlerException = new MockForgeHandlerException();
        }
    }
}
