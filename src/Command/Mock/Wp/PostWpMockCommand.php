<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Command\Mock\Wp;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Vigihdev\MockForge\Validators\DirectoryValidator;

#[AsCommand(
    name: 'mock:wp-post',
    description: 'Mock Post and their meta data'
)]
final class PostWpMockCommand extends BaseWpCommand
{


    protected function configure(): void
    {
        $this
            ->addOption('count', 'c', InputOption::VALUE_OPTIONAL, 'Number of posts to mock', null,)
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output directory', null,)
            ->addOption('dry-run', null, InputOption::VALUE_OPTIONAL, 'Dry run', false)
            ->setHelp(
                <<<'HELP'
                    <info>Mock Post and their meta data</info>

                    <comment>Usage:</comment>
                    %command.name% --count=100 --output=./mocks

                    <comment>Example:</comment>
                    %command.name% --count=100 --output=./mocks
                    %command.name% --count=100 --output=./mocks --dry-run

                    <comment>Note:</comment>
                    • Project name must be unique
                    • Path can be absolute or relative
                    • Label determine project category
                    HELP
            );
    }

    /**
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = $input->getOption('count');
        if ($count === null) {
            $count = 10;
        }

        try {
            DirectoryValidator::validate('')
                ->mustExist();
            $io->writeln('Mocking post...');
            $io->success(sprintf('Successfully mocked %d posts', $count));
        } catch (\Throwable $e) {
            $this->handlerException->handle($e, $io);
        }

        return Command::SUCCESS;
    }

    private function dryRun() {}
}
