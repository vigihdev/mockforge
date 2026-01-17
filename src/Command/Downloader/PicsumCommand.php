<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Command\Downloader;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Vigihdev\Downloader\Clients\GuzzleClient;
use Vigihdev\Downloader\ImageDownloader;
use Vigihdev\Downloader\Results\DownloadResult;
use Vigihdev\Downloader\Providers\PicsumProvider;
use Vigihdev\MockForge\Support\{MockForgeHelper, TempFileManager};
use Vigihdev\MockForge\Validators\{DirectoryValidator};
use Vigihdev\Support\Collection;

#[AsCommand(
    name: 'picsum',
    description: 'Download images images from Picsum Photo max 20 images'
)]
final class PicsumCommand extends AbstractDownloaderCommand
{

    private const MAX_COUNT = 20;

    protected function configure(): void
    {
        $this
            ->addOption('count', 'c', InputOption::VALUE_OPTIONAL, 'Number of images to download max (20)', 10)
            ->addOption('out', 'o', InputOption::VALUE_REQUIRED, 'Out Filepath to save images', null)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run', null)
            ->setHelp(
                <<<'HELP'
                    <info>Download images images from Picsum Photo</info>

                    <comment>Usage:</comment>
                    %command.name% --count=10 --output=./mocks

                    <comment>Example:</comment>
                    %command.name% --count=10 --output=./mocks
                    %command.name% --count=10 --output=./mocks --dry-run

                    <comment>Note:</comment>
                    • Count must be a positive integer
                    • Output path must be absolute or relative
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
        $outpath = $input->getOption('out');
        $count = (int) $input->getOption('count');
        $dryRun = $input->getOption('dry-run');

        if ($outpath === null) {
            $io->error('Out path is required');
            return Command::INVALID;
        }

        if ($count <= 0 || $count > self::MAX_COUNT) {
            $io->error(sprintf('Count must be between 1 and %d.', self::MAX_COUNT));
            return Command::INVALID;
        }

        $this->count = $count;
        $outpath = $this->normalizeOutpath($outpath);
        try {

            DirectoryValidator::validate($outpath)
                ->mustExist()
                ->mustBeWritable()
                ->mustBeReadable();

            if ($dryRun) {
                $this->dryRun($io, $outpath);
                return Command::SUCCESS;
            }

            $this->process($io, $outpath);
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->handlerException->handle($e, $io);
            return Command::FAILURE;
        }
    }

    private function dryRun(SymfonyStyle $io, string $outpath): void
    {
        $tmp = new TempFileManager();
        $tmpDir = $tmp->getTempDir();

        $io->writeln(sprintf('<fg=white>%s</>', "Start Downloading Picsum Images ...."));
        $collection = $this->picsumDowload($io, $tmpDir, 2);

        $io->title(
            sprintf("🔍 DRY RUN - Preview Picsum Images (%d) items", $this->count)
        );

        $io->note('No changes will be implemented.');
        $io->writeln(sprintf('Destination: <fg=green>%s</>', $outpath));

        $io->newLine();
        $io->table(
            ['No', 'Filename', 'Filesize'],
            $collection->map(function (DownloadResult $result, int $index) {
                return [
                    $index + 1,
                    basename($result->getDestination()),
                    MockForgeHelper::filesizeFormat($result->getSize()),
                ];
            })->toArray()
        );

        $io->success('Dry run done!');
        $io->info('Use without --dry-run to execute the actual process.');

        $tmp->clearAll();
    }

    private function process(SymfonyStyle $io, string $outpath): void
    {

        $io->writeln(sprintf('<fg=yellow>Processing Downloading %d Images ...</>', $this->count));
        $io->writeln(sprintf('Destination: <fg=green>%s</>', $outpath));
        $io->newLine();

        $client = new GuzzleClient();

        $count = $this->count;
        $progressBar = $io->createProgressBar($count);
        $progressBar->setFormat(
            "%current%/%max% [%bar%] %percent:3s%% %message%"
        );

        for ($i = 0; $i < $this->count; $i++) {
            try {
                $downloader = new ImageDownloader(
                    client: $client,
                    provider: new PicsumProvider(destination: $outpath)
                );
                $result = $downloader->download();
                $fileSize = MockForgeHelper::filesizeFormat($result->getSize());
                $filename = basename($result->getDestination());
                $progressBar->setMessage(
                    sprintf('<fg=green> ✔ SUCCESS</> %s (<fg=yellow>%s</>)', $filename, $fileSize)
                );
                if ($i === 0) {
                    $progressBar->setOverwrite(true);
                } else {
                    $progressBar->setOverwrite(false);
                }
                $progressBar->advance();
                usleep(600000);
            } catch (\Throwable $e) {
                $io->newLine();
                $io->writeln(sprintf('<fg=red>✘ FAILURE %s</>', $e->getMessage()));
            }
        }

        $progressBar->finish();
        $io->newLine(2);
        $io->writeln(sprintf('<fg=green>Processing Downloading %d Images done!</>', $this->count));
        $io->newLine();
    }


    /**
     * Download images from Picsum Photo
     *
     * @param SymfonyStyle $io  SymfonyStyle IO
     * @param string $outpath  Outpath to save images
     * @param int $count Number of images to download max (20)
     * @return Collection<DownloadResult>
     */
    private function picsumDowload(SymfonyStyle $io, string $outpath, int $count): Collection
    {
        $client = new GuzzleClient();
        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $downloader = new ImageDownloader(
                client: $client,
                provider: new PicsumProvider(
                    destination: $outpath
                )
            );
            try {
                $results[] = $downloader->download();
            } catch (\Throwable $e) {
                $io->writeln(sprintf('<fg=red>%s</>', $e->getMessage()));
            }
        }

        return new Collection($results);
    }
}
