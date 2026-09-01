<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Demo\Mode\Reset;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Facades\File;

/**
 * Restores file state for a demo: re-copies a baseline directory over the
 * configured location and/or purges configured storage disks.
 */
final readonly class FileResetter
{
    public function __construct(private FilesystemFactory $filesystem) {}

    public function reset(): void
    {
        $config = (array) config('demo-mode.reset.files', []);

        $this->restoreBaseline($config['baseline_path'] ?? null);
        $this->purgeDisks((array) ($config['disks'] ?? []));
    }

    private function restoreBaseline(mixed $baseline): void
    {
        if (! is_array($baseline) || ! isset($baseline['from'], $baseline['to'])) {
            return;
        }

        $from = (string) $baseline['from'];
        $to = (string) $baseline['to'];

        if (! File::isDirectory($from)) {
            return;
        }

        File::deleteDirectory($to);
        File::ensureDirectoryExists($to);
        File::copyDirectory($from, $to);
    }

    /**
     * @param  list<string>  $disks
     */
    private function purgeDisks(array $disks): void
    {
        foreach ($disks as $disk) {
            $fs = $this->filesystem->disk($disk);

            foreach ($fs->allDirectories() as $directory) {
                $fs->deleteDirectory($directory);
            }

            $fs->delete($fs->allFiles());
        }
    }
}
