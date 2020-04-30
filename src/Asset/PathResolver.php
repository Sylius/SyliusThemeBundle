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

use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;

final class PathResolver implements PathResolverInterface
{
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

        return sprintf(
            '%s/_themes/%s/%s',
            rtrim(substr($path, $basePathPositionAtPath, $basePathLength), '/'),
            $theme->getName(),
            ltrim(substr($path, $basePathPositionAtPath + $basePathLength), '/')
        );
    }
}
