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

namespace Sylius\Bundle\ThemeBundle\Locator;

use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Symfony\Component\Filesystem\Filesystem;

final class ApplicationResourceLocator implements ResourceLocatorInterface
{
    /** @var Filesystem */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function locateResource(string $template, ThemeInterface $theme): string
    {
        $path = sprintf('%s/templates/%s', $theme->getPath(), $template);
        if (!$this->filesystem->exists($path)) {
            throw new ResourceNotFoundException($template, [$theme]);
        }

        return $path;
    }
}
