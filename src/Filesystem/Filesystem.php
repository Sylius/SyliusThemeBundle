<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\ThemeBundle\Filesystem;

use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;

final class Filesystem implements FilesystemInterface
{
    private BaseFilesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new BaseFilesystem();
    }

    public function getFileContents(string $file): string
    {
        return (string) file_get_contents($file);
    }

    public function copy(string $originFile, string $targetFile, bool $override = false)
    {
        $this->filesystem->copy($originFile, $targetFile, $override);
    }

    public function mkdir($dirs, int $mode = 0777)
    {
        $this->filesystem->mkdir($dirs, $mode);
    }

    public function exists($files)
    {
        return $this->filesystem->exists($files);
    }

    public function touch($files, int $time = null, int $atime = null)
    {
        $this->filesystem->touch($files, $time, $atime);
    }

    public function remove($files)
    {
        $this->filesystem->remove($files);
    }

    public function chmod($files, int $mode, int $umask = 0000, bool $recursive = false)
    {
        $this->filesystem->chmod($files, $mode, $umask, $recursive);
    }

    public function chown($files, string $user, bool $recursive = false)
    {
        $this->filesystem->chown($files, $user, $recursive);
    }

    public function chgrp($files, string $group, bool $recursive = false)
    {
        $this->filesystem->chgrp($files, $group, $recursive);
    }

    public function rename(string $origin, string $target, bool $overwrite = false)
    {
        $this->filesystem->rename($origin, $target, $overwrite);
    }

    public function symlink(string $originDir, string $targetDir, bool $copyOnWindows = false)
    {
        $this->filesystem->symlink($originDir, $targetDir, $copyOnWindows);
    }

    public function mirror(string $originDir, string $targetDir, \Traversable $iterator = null, array $options = [])
    {
        $this->filesystem->mirror($originDir, $targetDir, $iterator, $options);
    }

    public function makePathRelative(string $endPath, string $startPath)
    {
        return $this->filesystem->makePathRelative($endPath, $startPath);
    }

    public function isAbsolutePath(string $file)
    {
        return $this->filesystem->isAbsolutePath($file);
    }
}
