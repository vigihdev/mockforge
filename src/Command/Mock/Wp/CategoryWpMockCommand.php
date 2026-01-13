<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Command\Mock\Wp;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mock:wp-category',
    description: 'Mock Category and their meta data'
)]
final class CategoryWpMockCommand extends Command
{

    /**
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->writeln('Mocking category...');
        $io->success('Categories mocked successfully');

        return Command::SUCCESS;
    }
}
