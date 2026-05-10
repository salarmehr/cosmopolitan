#!/usr/bin/env php
<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$template = file_get_contents(__DIR__ . '/readme.md');
$code = file_get_contents(__DIR__ . '/sample.php');

exec('php ' . escapeshellarg(__DIR__ . '/sample.php') . ' 2>&1', $outputLines, $exitCode);
if ($exitCode !== 0) {
    fwrite(STDERR, "sample.php exited with code $exitCode:\n" . implode("\n", $outputLines) . "\n");
    exit(1);
}
$output = implode("\n", $outputLines);

$readme = str_replace(
    ['{{sample.php}}', '{{sample.php output}}'],
    [$code, $output],
    $template
);

file_put_contents($root . '/readme.md', $readme);
echo "readme.md generated.\n";
