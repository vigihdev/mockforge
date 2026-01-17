<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Command\Helper;

use LogicException;
use RuntimeException;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ProgressSpinner
{

    /**
     * The process ID of the spinner loader.
     */
    private int $pid = 0;

    /**
     * The frames for the spinner animation.
     */
    private array $frames = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];

    /**
     * The interval for updating the spinner animation.
     */
    private int $interval = 100000;

    /**
     * Whether the spinner loader has been started.
     */
    private bool $started = false;

    /**
     * Whether the spinner loader has been finished.
     */
    private bool $finished = false;

    private const CLEAR_LINE = "\r\033[2K"; // Remove Line
    private const HIDE_CURSOR = "\e[?25l"; // hide cursor 
    private const SHOW_CURSOR = "\e[?25h"; // show cursor 

    public function __construct(
        private readonly SymfonyStyle $io
    ) {}

    /**
     * Start the spinner loader. fork process.
     * 
     * @param string $message The message to display while loading.
     * @return void 
     */
    public function start(string $message): void
    {

        if ($this->started) {
            posix_kill($this->pid, SIGKILL);
            pcntl_waitpid($this->pid, $status);
            throw new LogicException('Spinner loader already started.');
        }

        if (
            !function_exists('pcntl_fork') ||
            !function_exists('posix_kill') ||
            !function_exists('pcntl_waitpid')
        ) {
            throw new RuntimeException('pcntl_fork, posix_kill, or pcntl_waitpid function is not available.');
        }

        $this->pid = pcntl_fork();

        if ($this->pid == -1) {
            echo "$message\n";
            return;
        }

        $this->started = true;
        $this->finished = false;

        if ($this->pid > 0) {
            $this->io->write(self::CLEAR_LINE);
            return;
        }

        $i = 0;
        while (true) {
            $frame = $this->frames[$i++ % count($this->frames)];
            $this->io->write(sprintf("\r%s %s", $frame, $message), false);
            flush();
            usleep($this->interval);
        }
    }

    public function success(string|iterable $messages): void
    {
        $this->stoped();
        $this->io->writeln(
            sprintf("%s%s", self::CLEAR_LINE, $messages)
        );
    }

    public function failure(string|iterable $messages): void
    {
        $this->stoped();
        $this->io->writeln(
            sprintf("%s%s", self::CLEAR_LINE, $messages)
        );
    }

    public function skip(string|iterable $messages): void
    {
        $this->stoped();
        $this->io->writeln(
            sprintf("%s%s", self::CLEAR_LINE, $messages)
        );
    }

    private function stoped(): void
    {
        if ($this->pid > 0 && $this->started && !$this->finished) {
            $this->started = false;
            $this->finished = true;
            posix_kill($this->pid, SIGKILL);
            pcntl_waitpid($this->pid, $status);
        }
    }
}
