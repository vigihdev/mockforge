<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Command\Mock\Dto;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputOption, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Vigihdev\MockForge\Faker\Support\DtoMockGenerator;
use Vigihdev\MockForge\Support\MockForgeHelper;
use Vigihdev\MockForge\Validators\{DirectoryValidator, FileValidator};
use Vigihdev\Support\Collection;
use Vigihdev\Support\File;

#[AsCommand(
    name: 'mock:dto',
    description: 'Create object from DTO class and their meta data'
)]
final class DtoMockCommand extends AbstractDtoCommand
{

    /**
     * @var Collection<string,mixed> $collection
     */
    private Collection $collection;
    protected function configure(): void
    {
        $this
            ->addArgument('class', InputArgument::REQUIRED, 'DTO class to mock')
            ->addOption('out', 'o', InputOption::VALUE_REQUIRED, 'Out Filepath to save mock data')
            ->addOption('count', 'c', InputOption::VALUE_REQUIRED, 'Number of mock data to generate', 1)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run', null)
            ->setHelp(
                <<<'HELP'
                    <info>Mock DTO class and their meta data</info>

                    <comment>Usage:</comment>
                    %command.name% <class>

                    <comment>Example:</comment>
                    %command.name% Vigihdev\WpCliModels\DTOs\Args\Post\CreatePostArgsDto --out=./mocks/create_post_args.json
                    %command.name% Vigihdev\WpCliModels\DTOs\Args\Post\UpdatePostArgsDto --out=./mocks/update_post_args.json

                    <comment>Note:</comment>
                    • Class must be a valid DTO class
                    • Path can be absolute or relative
                    • Out filepath must be a valid JSON file
                    • Out filepath must not exist
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
        $class = $input->getArgument('class');
        $outpath = $input->getOption('out');
        $count = $input->getOption('count');
        $dryRun = $input->getOption('dry-run');

        if ($outpath === null) {
            $io->error('Out path is required');
            return Command::INVALID;
        }

        $autoload = MockForgeHelper::findVendorAutoload();
        if (!$autoload || !is_file($autoload)) {
            $io->error(sprintf('Autoload file %s not found', $autoload));
            return Command::FAILURE;
        }

        require_once $autoload;
        if (!class_exists($class)) {
            $io->error(sprintf('Class %s not found', $class));
            return Command::FAILURE;
        }

        $outpath = $this->normalizeOutFilepath($outpath);
        try {

            FileValidator::validate($outpath)
                ->mustHaveExtension()
                ->mustBeExtension('json')
                ->mustBeNotExist();

            $directory = Path::getDirectory($outpath);
            DirectoryValidator::validate($directory)
                ->mustExist()
                ->mustBeWritable()
                ->mustBeReadable();

            $generator = new DtoMockGenerator();
            $dtoMock = [];
            for ($i = 0; $i < $count; $i++) {
                $dtoMock[] = $generator->generate($class);
            }

            $this->collection = new Collection($dtoMock);

            $this->out = $outpath;
            $this->class = $class;
            if ($dryRun) {
                $this->dryRun($io);
                return Command::SUCCESS;
            }

            $this->process($io);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->handlerException->handle($e, $io);
            return Command::FAILURE;
        }
    }

    private function dryRun(SymfonyStyle $io): void
    {
        $io->title(
            sprintf("🔍 DRY RUN - Preview Mock")
        );

        $io->note('No changes will be implemented.');
        $io->writeln(
            sprintf('Destination: <fg=green>%s</>', $this->out)
        );
        $io->writeln(
            sprintf('Count: <fg=green>%d</>', $this->collection->count())
        );
        $io->writeln(
            sprintf('Class: <fg=green>%s</>', $this->class)
        );
        $io->newLine();

        $headers = array_keys($this->collection->first() ?? []);
        $headers = array_slice($headers, 0, 4);
        $itemsRow = [];

        foreach ($this->collection->getIterator() as $index => $item) {
            if (is_array($item)) {
                $data = array_values($item);
                $data = array_slice($data, 0, 4);

                $data = array_merge([$index + 1], $data);
                $data = array_map(function ($item) use ($index) {
                    $item = is_array($item) ? implode(', ', $item) : (string)$item;
                    $item = substr($item, 0, 40);
                    return $item;
                }, $data);

                $itemsRow[] = $data;
            }
        }

        $io->table(array_merge(['No'], $headers), $itemsRow);

        $io->success('Dry run done!');
        $io->info('Use without --dry-run to execute the actual process.');
    }

    private function process(SymfonyStyle $io): void
    {

        $io->section(
            sprintf('<fg=yellow>Processing Mocking %s...</>', $this->class)
        );
        $io->writeln(
            sprintf('Destination: <fg=green>%s</>', $this->out)
        );
        $io->writeln(
            sprintf('Count: <fg=green>%d</>', $this->collection->count())
        );

        if ((bool)File::put($this->out, $this->collection->toJson())) {
            $io->success(
                sprintf("Successfully mocked to %s with %d items", $this->out, $this->collection->count())
            );
            $io->newLine();
            return;
        }

        $io->error(
            sprintf("Failure mocking to %s", $this->out)
        );
        $io->newLine();
    }
}
