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

namespace Sylius\Bundle\ThemeBundle\Asset;

use Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsProviderInterface;
use Sylius\Bundle\ThemeBundle\Filesystem\FilesystemInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;

final class PathResolver implements PathResolverInterface
{
    private AssetsProviderInterface $assetsProvider;

    private FilesystemInterface $filesystem;

    public function __construct(AssetsProviderInterface $assetsProvider, FilesystemInterface $filesystem)
    {
        $this->assetsProvider = $assetsProvider;
        $this->filesystem = $filesystem;
    }

    public function resolve(string $path, string $basePath, ThemeInterface $theme): string
    {
        $basePath = rtrim($basePath, '/');

        if ($basePath === '' || strpos($path, $basePath) === false) {
            $basePathPositionAtPath = 0;
            $basePathLength = 0;
        } else {
            $basePathPositionAtPath = (int) strpos($path, $basePath);
            $basePathLength = strlen($basePath);
        }

        $relativePath = trim(substr($path, $basePathPositionAtPath + $basePathLength), '/');

        if ($this->shouldPathBeModified($relativePath, $theme)) {
            $prefixPath = rtrim(substr($path, $basePathPositionAtPath, $basePathLength), '/');

            return sprintf('%s/_themes/%s/%s', $prefixPath, $theme->getName(), $relativePath);
        }

        return $path;
    }

    private function shouldPathBeModified(string $relativePath, ThemeInterface $theme): bool
    {
        foreach ($this->assetsProvider->provideDirectoriesForTheme($theme) as $originDir => $targetDir) {
            $targetDir = trim($targetDir, '/');

            if ($targetDir !== '' && strpos($relativePath, $targetDir) === false) {
                continue;
            }

            if (!$this->filesystem->exists($originDir . '/' . ltrim(str_replace($targetDir, '', $relativePath), '/'))) {
                continue;
            }

            return true;
        }

        return false;
    }
}
