#!/usr/bin/env php
<?php

function packageName(string $subject): string
{
    return ucwords(str_replace('-', ' ', $subject));
}

function titleCase(string $subject): string
{
    return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $subject)));
}

function removePrefix(string $prefix, string $content): string
{
    if (str_starts_with($content, $prefix)) {
        return mb_substr($content, mb_strlen($prefix));
    }

    return $content;
}

function replaceForWindows(): array
{
    return preg_split('/\\r\\n|\\r|\\n/', run('dir /S /B * | findstr /v /i .git\ | findstr /v /i vendor | findstr /v /i '.basename(__FILE__).' | findstr /r /i /M /F:/ ":author_name :author_username author@domain.com :package_name :package_slug Skeleton skeleton :package_description"'));
}

function replaceForAllOtherOSes(): array
{
    return explode(PHP_EOL, run('grep -E -r -l -i ":author_name|:author_username|author@domain.com|:package_name|:package_slug|Skeleton|skeleton|:package_description" --exclude-dir=vendor ./* ./.github/* | grep -v '.basename(__FILE__)));
}

function run(string $command): string
{
    return trim((string) shell_exec($command));
}

function replaceInFile(string $file, array $replacements): void
{
    $contents = file_get_contents($file);

    file_put_contents(
        $file,
        str_replace(
            array_keys($replacements),
            array_values($replacements),
            $contents
        )
    );
}

function determineSeparator(string $path): string
{
    return str_replace('/', DIRECTORY_SEPARATOR, $path);
}

function removeReadmeParagraphs(string $file): void
{
    $contents = file_get_contents($file);

    file_put_contents(
        $file,
        preg_replace('/<!--delete-->.*<!--\/delete-->/s', '', $contents) ?: $contents
    );
}

$authorName = $argv[1];
$authorEmail = $argv[2];
$authorUsername = $argv[3];
$packageName = packageName($argv[4]);
$packageSlug = $argv[4];
$packageSlugWithoutPrefix = removePrefix(prefix: 'laravel-', content: $packageSlug);
$className = titleCase($packageSlug);
$description = $argv[5] !== '' ? $argv[5] : 'A skeleton for building Red Explosion Laravel packages.';

$currentDirectory = (string) getcwd();
$folderName = basename($currentDirectory);

$files = (str_starts_with(mb_strtoupper(PHP_OS), 'WIN') ? replaceForWindows() : replaceForAllOtherOSes());

foreach ($files as $file) {
    replaceInFile($file, [
        ':author_name' => $authorName,
        ':author_username' => $authorUsername,
        'author@domain.com' => $authorEmail,
        ':package_name' => $packageName,
        ':package_slug' => $packageSlug,
        'Skeleton' => $className,
        'skeleton' => $packageSlug,
        ':package_description' => $description,
    ]);

    match (true) {
        str_contains($file, determineSeparator(path: 'src/Skeleton.php')) => rename($file, determineSeparator(path: './src/'.$className.'.php')),
        str_contains($file, determineSeparator(path: 'src/SkeletonServiceProvider.php')) => rename($file, determineSeparator(path: './src/'.$className.'ServiceProvider.php')),
        str_contains($file, determineSeparator(path: 'config/skeleton.php')) => rename($file, determineSeparator(path: './config/'.$packageSlugWithoutPrefix.'.php')),
        str_contains($file, 'README.md') => removeReadmeParagraphs($file),
        default => [],
    };
}
