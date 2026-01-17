<?php

declare(strict_types=1);

namespace Vigihdev\MockForge\Support;

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
}
