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
use Vigihdev\Downloader\Providers\NativeUrlProvider;
use Vigihdev\MockForge\Command\Helper\ProgressSpinner;
use Vigihdev\MockForge\Exceptions\MockForgeException;
use Vigihdev\MockForge\Support\{MockForgeHelper};
use Vigihdev\Validators\{DirectoryValidator, FileValidator};

#[AsCommand(
    name: 'download:image',
    description: 'Download image from Url and save to specified path'
)]
final class NativeUrlCommand extends AbstractDownloaderCommand
{

    protected function configure(): void
    {
        $this
            ->addOption('file-url', null, InputOption::VALUE_OPTIONAL, 'List url from file path for download image')
            ->addOption('url', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Url to download image')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output Filepath to save images', null)
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force override existing file')
            ->setHelp(
                <<<'HELP'
                     <info>Download image from Url and save to specified path</info>

                    <comment>Usage:</comment>
                    %command.name% <url> --output=./mocks

                    <comment>Example:</comment>
                    %command.name% https://example.com/image.jpg --output=./mocks
                    %command.name% https://example.com/image.jpg --output=./mocks --dry-run

                    <comment>Note:</comment>
                    • Count must be a positive integer
                    • Output path must be absolute or relative to current working directory
                    • Output path must end with .jpg, .jpeg, .png, .gif, .bmp
                    • Output path must not contain any special characters
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
        $urls = $input->getOption('url');
        $force = (bool)$input->getOption('force');
        $fileUrl = $input->getOption('file-url');

        $listUrls = [];
        if ($urls === null && $fileUrl === null) {
            $io->error('Url or file-url is required');
            return Command::INVALID;
        }

        if ($fileUrl !== null) {
            FileValidator::validate('file-url', $fileUrl)
                ->mustExist()
                ->mustBeReadable()
                ->mustBeExtension('json');
            $listUrls = array_merge($listUrls, json_decode(file_get_contents($fileUrl), true));
        }

        if (is_array($urls)) {
            $listUrls = array_merge($listUrls, $urls);
        }

        if (empty($listUrls)) {
            $io->error('Url or file-url is empty');
            return Command::INVALID;
        }

        if ($outpath === null) {
            $io->error('Out path is required');
            return Command::INVALID;
        }

        $outpath = $this->normalizeOutpath($outpath);

        try {

            DirectoryValidator::validate('output', $outpath)
                ->mustExist()
                ->mustBeWritable()
                ->mustBeReadable();

            $this->process($io, $outpath, $listUrls, (bool)$force);
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            MockForgeException::handleThrowableWithIo($e, $io);
            return Command::FAILURE;
        }
    }

    private function process(SymfonyStyle $io, string $outpath, array $urls, bool $force): void
    {

        $count = count($urls);
        $io->newLine();
        $io->writeln(sprintf('<fg=yellow>Processing Downloading %d Images ...</>', $count));
        $io->writeln(sprintf('Destination: <fg=green>%s</>', $outpath));
        $io->newLine();

        $successCount = 0;
        $errorCount = 0;
        $sizes = [];
        $client = new GuzzleClient();
        $spinner = new ProgressSpinner($io);
        foreach ($urls as $i => $url) {
            try {
                $spinner->start(
                    sprintf("%d/%d %s", $i + 1, $count, MockForgeHelper::getUrlPath($url))
                );
                usleep(600000); // 600ms
                $downloader = new ImageDownloader(
                    client: $client,
                    provider: new NativeUrlProvider(url: $url, destination: $outpath, allowOverwrite: $force)
                );

                $result = $downloader->download();
                $fileSize = MockForgeHelper::filesizeFormat($result->getSize());
                $filename = basename($result->getDestination());

                $successCount++;
                $sizes[] = $result->getSize();
                $spinner->success(
                    sprintf('%d/%d <fg=green> ✔ SUCCESS</> %s (<fg=yellow>%s</>)', $i + 1, $count, $filename, $fileSize)
                );
            } catch (\Throwable $e) {
                $errorCount++;
                $spinner->failure(
                    sprintf('%d/%d <fg=red> ✔ FAILURE %s</>', $i + 1, $count, $e->getMessage())
                );
            }
        }

        $io->newLine();
        $io->writeln(
            sprintf(
                '<fg=green>Processing Downloading <fg=yellow>(%d)</> Images done! <fg=yellow>(%d)</> Success, <fg=yellow>(%d)</> Error, <fg=yellow>(%s)</> Total Size</>',
                $count,
                $successCount,
                $errorCount,
                MockForgeHelper::filesizeFormat(array_sum($sizes))
            )
        );
    }
}
