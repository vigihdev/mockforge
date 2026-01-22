<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Command\Mock;

use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputArgument, InputOption, InputInterface};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Vigihdev\FakerReflection\FakerReflection;
use Vigihdev\MockForge\Exceptions\MockForgeException;
use Vigihdev\MockForge\Support\MockForgeHelper;
use Vigihdev\Validators\{DirectoryValidator, FileValidator};
use Vigihdev\Support\Collection;
use Vigihdev\Support\File;

#[AsCommand(
    name: 'mock:dto',
    description: 'Create object from DTO class and their meta data'
)]
final class DtoMockCommand extends AbstractMockCommand
{

    /**
     * @var Collection<string,mixed> $collection
     */
    private Collection $collection;

    protected function configure(): void
    {
        $this
            ->addArgument('class', InputArgument::REQUIRED, 'DTO class Or Model class to mock')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output Filepath to save mock data')
            ->addOption('count', 'c', InputOption::VALUE_REQUIRED, 'Number of mock data to generate', 10)
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force override existing file')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run', null)
            ->setHelp(
                <<<'HELP'
                    <info>Mock DTO class and their meta data</info>

                    <comment>Usage:</comment>
                    %command.name% <class>

                    <comment>Example:</comment>
                    %command.name% Vigihdev\WpCliModels\DTOs\Args\Post\CreatePostArgsDto --out=./mocks/create_post_args.json
                    %command.name% Vigihdev\WpCliModels\DTOs\Args\Post\UpdatePostArgsDto --out=./mocks/update_post_args.json
                    %command.name% Vigihdev\WpCliModels\DTOs\Args\Post\UpdatePostArgsDto --out=./mocks/update_post_args.json --force

                    <comment>Note:</comment>
                    • Class must be a valid DTO class
                    • Path can be absolute or relative
                    • Output filepath must be a valid JSON file
                    • Output filepath must not exist
                    • Use --force option to override existing file
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
        $force = $input->getOption('force');
        $outFilepath = $input->getOption('output');
        $count = (int)$input->getOption('count');
        $dryRun = $input->getOption('dry-run');

        if ($outFilepath === null) {
            $io->error('Output path is required');
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

        $outFilepath = $this->normalizeOutputpath($outFilepath);
        try {

            $fileValidator = FileValidator::validate('output', $outFilepath)
                ->mustHaveExtension()
                ->mustBeExtension('json');

            if (!$force) {
                $fileValidator->mustBeNotExist();
            }

            $directory = Path::getDirectory($outFilepath);
            DirectoryValidator::validate('output', $directory)
                ->mustExist()
                ->mustBeWritable()
                ->mustBeReadable();

            $generator = new FakerReflection(
                reflection: new ReflectionClass($class),
                count: $count,
            );
            $this->collection = $generator->generate();

            $this->outFilepath = $outFilepath;
            $this->class = $class;
            if ($dryRun) {
                $this->dryRun($io);
                return Command::SUCCESS;
            }

            $this->process($io);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            MockForgeException::handleThrowableWithIo($e, $io);
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
            sprintf('Destination: <fg=green>%s</>', $this->outFilepath)
        );
        $io->writeln(
            sprintf('Count: <fg=green>%d</>', $this->collection->count())
        );
        $io->writeln(
            sprintf('Class: <fg=green>%s</>', $this->class)
        );
        $io->newLine();

        $headers = array_keys($this->collection->first() ?? []);
        $headers = array_slice($headers, 0, 3);
        $itemsRow = $this->normalizeItemsTableRow($this->collection->toArray(), 3);

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
            sprintf('Destination: <fg=green>%s</>', $this->outFilepath)
        );
        $io->writeln(
            sprintf('Count: <fg=green>%d</>', $this->collection->count())
        );

        if ((bool)File::put($this->outFilepath, $this->collection->toJson())) {
            $io->success(
                sprintf("Successfully mocked to %s with %d items", $this->outFilepath, $this->collection->count())
            );
            $io->newLine();
            return;
        }

        $io->error(
            sprintf("Failure mocking to %s", $this->outFilepath)
        );
        $io->newLine();
    }
}
