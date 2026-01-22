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
use Vigihdev\Downloader\Providers\UnsplashProvider;
use Vigihdev\MockForge\Exceptions\MockForgeException;
use Vigihdev\MockForge\Support\MockForgeHelper;
use Vigihdev\Validators\{DirectoryValidator};

#[AsCommand(
    name: 'unsplash',
    description: 'Download Random images from Unsplash max 10 images'
)]
final class UnsplashCommand extends AbstractDownloaderCommand
{

    protected function configure(): void
    {
        $this
            ->addOption('count', 'c', InputOption::VALUE_OPTIONAL, 'Number of images to download max (10)', 5)
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output Filepath to save images', null)
            ->setHelp(
                <<<'HELP'
                    <info>Download Random images from Unsplash</info>

                    <comment>Usage:</comment>
                    %command.name% --count=5 --output=./mocks

                    <comment>Example:</comment>
                    %command.name% --count=5 --output=./mocks

                    <comment>Note:</comment>
                    • Count must be a positive integer
                    • Output path must be absolute or relative
                    • Out path must be a directory
                    • Out path must be a writable directory
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
        $outpath = $input->getOption('output');
        $count = (int) $input->getOption('count');

        if ($outpath === null) {
            $io->error('Out path is required');
            return Command::INVALID;
        }

        if ($count <= 0 || $count > 10) {
            $io->error('Count must be between 1 and 10.');
            return Command::INVALID;
        }

        $this->count = $count;

        $outpath = $this->normalizeOutpath($outpath);
        try {

            DirectoryValidator::validate('output', $outpath)
                ->mustExist()
                ->mustBeWritable()
                ->mustBeReadable();

            $this->process($io, $outpath);
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            MockForgeException::handleThrowableWithIo($e, $io);
            return Command::FAILURE;
        }
    }

    private function process(SymfonyStyle $io, string $outpath): void
    {

        $io->newLine();
        $io->writeln(sprintf('<fg=yellow>Processing Downloading %d Images ...</>', $this->count));
        $io->writeln(sprintf('Destination: <fg=green>%s</>', $outpath));
        $io->newLine();

        $client = new GuzzleClient();
        $count = $this->count;
        $progressBar = $io->createProgressBar($count);
        $progressBar->setFormat(
            "%current%/%max% [%bar%] %percent:3s%% %message%"
        );

        for ($i = 0; $i < $count; $i++) {
            try {
                $downloader = new ImageDownloader(
                    client: $client,
                    provider: new UnsplashProvider(destination: $outpath)
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
}
