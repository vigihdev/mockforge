<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Support;

use Symfony\Component\Filesystem\Path;

final class MockForgeHelper
{
    public static function filesizeFormat(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $index = 0;
        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            $index++;
        }
        return sprintf('%.' . $precision . 'f %s', $size, $units[$index]);
    }

    public static function getUrlPath(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $basename = pathinfo($path, PATHINFO_BASENAME);
        return $basename;
    }


    public static function isFileJson(string $filePath): bool
    {
        return pathinfo($filePath, PATHINFO_EXTENSION) === 'json';
    }

    public static function isFileTxt(string $filePath): bool
    {
        return pathinfo($filePath, PATHINFO_EXTENSION) === 'txt';
    }

    public static function isFileExist(string $filePath): bool
    {
        return file_exists($filePath);
    }

    public static function findVendorAutoload(): ?string
    {

        if (self::findVendorDirFromCwd()) {
            $autoload = Path::join(self::findVendorDirFromCwd(), 'autoload.php');
            if (is_file($autoload)) {
                return $autoload;
            }
        }

        return null;
    }

    private static function findVendorDirFromCwd(): ?string
    {
        $currentDir = getcwd();

        // Kita coba memanjat hingga 5 kali ke atas
        for ($i = 0; $i < 5; $i++) {
            $vendorPath = Path::join($currentDir, 'vendor');

            if (is_dir($vendorPath) && is_file(Path::join($vendorPath, 'autoload.php'))) {
                return $vendorPath;
            }

            // Naik satu level
            $parentDir = dirname($currentDir);

            // Berhenti jika sudah sampai root sistem operasi
            if ($parentDir === $currentDir) break;

            $currentDir = $parentDir;
        }

        return null;
    }
}
