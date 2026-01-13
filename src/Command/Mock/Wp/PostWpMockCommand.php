<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Command\Mock\Wp;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption, InputArgument};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Vigihdev\MockForge\DTOs\Wp\PostWpDto;
use Vigihdev\MockForge\Faker\Provider\PostWpProvider;
use Vigihdev\MockForge\Validators\{DirectoryValidator, FileValidator};
use Vigihdev\Support\Collection;
use Vigihdev\Support\File;

#[AsCommand(
    name: 'mock:wp-post',
    description: 'Mock Post and their meta data'
)]
final class PostWpMockCommand extends BaseWpCommand
{

    /**
     *
     * @var Collection<PostWpDto>
     */
    private Collection $posts;

    protected function configure(): void
    {
        $this
            ->addOption('count', 'c', InputOption::VALUE_OPTIONAL, 'Number of posts to mock', 10)
            ->addOption('out', 'o', InputOption::VALUE_REQUIRED, 'Out Filepath to save mock data', null)
            ->addOption('author-count', 'ac', InputOption::VALUE_REQUIRED, 'Number of unique authors', 10)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run', null)
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
        $this->count = (int) $input->getOption('count');
        $outFilepath = $input->getOption('out');
        $authorCount = (int) $input->getOption('author-count');
        $dryRun = $input->getOption('dry-run');

        if ($outFilepath === null) {
            $io->error('Out filepath is required');
            return Command::INVALID;
        }

        $outFilepath = $this->normalizeOutFilepath($outFilepath);

        try {
            FileValidator::validate($outFilepath)
                ->mustHaveExtension()
                ->mustBeExtension('json', 'csv')
                ->mustBeNotExist();

            $directory = Path::getDirectory($outFilepath);
            DirectoryValidator::validate($directory)
                ->mustExist();

            $this->posts = (new PostWpProvider(
                count: $this->count,
                authorCount: $authorCount,
            ))->generatePosts();
            if ($dryRun) {
                $this->dryRun($io, $outFilepath);
                return Command::SUCCESS;
            }

            $this->process($io, $outFilepath);
        } catch (\Throwable $e) {
            $this->handlerException->handle($e, $io);
        }

        return Command::SUCCESS;
    }

    private function dryRun(SymfonyStyle $io, string $outFilepath): void
    {
        $io->title(
            sprintf("🔍 DRY RUN - Preview Mock Post (%d) items", $this->posts->count())
        );

        $io->note('No changes to the sytem file, just a preview.');
        $io->writeln(sprintf('Destination: <fg=green>%s</>', $outFilepath));

        $io->newLine();
        $io->table(
            ['No', 'Title', 'Type', 'Status', 'Author', 'Date'],
            $this->posts->map(function (PostWpDto $post, int $index) {
                return [
                    $index + 1,
                    $post->getTitle(),
                    $post->getType(),
                    $post->getStatus(),
                    $post->getAuthor(),
                    $post->getDate(),
                ];
            })->toArray()
        );

        $io->success('Dry run done!');
        $io->info('Use without --dry-run to execute the actual process.');
    }

    private function process(SymfonyStyle $io, string $outFilepath): void
    {
        $io->writeln(sprintf('<fg=yellow>Processing Mocking %d Post ...</>', $this->count));

        if ((bool) File::put($outFilepath, $this->posts->map(fn($t) => $t->toArray())->toJson())) {
            $io->success(sprintf('Successfully mocked %d posts to %s', $this->count, $outFilepath));
        } else {
            $io->error(sprintf("Failed to mock %d posts to %s", $this->count, $outFilepath));
        }
    }
}
