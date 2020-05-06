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
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;

/**
 * @todo Simplify/extract and improve its performance
 */
final class PathResolver implements PathResolverInterface
{
    /** @var AssetsProviderInterface */
    private $assetsProvider;

    public function __construct(AssetsProviderInterface $assetsProvider)
    {
        $this->assetsProvider = $assetsProvider;
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

        $prefixPath = rtrim(substr($path, $basePathPositionAtPath, $basePathLength), '/');
        $relativePath = trim(substr($path, $basePathPositionAtPath + $basePathLength), '/');

        foreach ($this->assetsProvider->provideDirectoriesForTheme($theme) as $originDir => $targetDir) {
            $targetDir = trim($targetDir, '/');

            if ($targetDir !== '' && strpos($relativePath, $targetDir) === false) {
                continue;
            }

            if (!file_exists($originDir . '/' . str_replace($targetDir, '', $relativePath))) {
                continue;
            }

            return sprintf('%s/_themes/%s/%s', $prefixPath, $theme->getName(), $relativePath);
        }

        return $path;
    }
}
