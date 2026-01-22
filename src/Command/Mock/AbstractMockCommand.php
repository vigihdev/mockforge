<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Command\Mock;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Path;

abstract class AbstractMockCommand extends Command
{

    protected string $outFilepath = '';

    protected int $count = 0;

    protected string $class = '';


    public function __construct(?string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * Normalize filepath to absolute path
     *
     * @return string
     */
    protected function normalizeOutputpath(string $out): string
    {
        if (Path::isAbsolute($out)) {
            $directory = Path::getDirectory($out);
            if ($realpath = realpath($directory)) {
                $filename = pathinfo($out, PATHINFO_FILENAME);
                return Path::join($realpath, $filename);
            }
            throw new \RuntimeException(sprintf('Cannot resolve absolute path for %s', $out));
        }
        return Path::join(getcwd() ?? '', $out);
    }

    protected function normalizeItemsTableRow(array $items, int $lengthColumn): array
    {
        $itemsRow = [];
        foreach ($items as $index => $item) {
            if (is_array($item)) {
                $data = array_values($item);
                $data = array_slice($data, 0, $lengthColumn);

                $data = array_merge([$index + 1], $data);
                $data = array_map(function ($item) use ($index) {
                    $item = is_array($item) ? implode(', ', $item) : (string)$item;
                    $item = substr($item, 0, 40);
                    return $item;
                }, $data);

                $itemsRow[] = $data;
            }
        }
        return $itemsRow;
    }
}
