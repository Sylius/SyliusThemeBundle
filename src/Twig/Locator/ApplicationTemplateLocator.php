<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\ThemeBundle\Twig\Locator;

use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

/**
 * Handles paths like "template.html.twig" or "Directory/template.html.twig".
 */
final class ApplicationTemplateLocator implements TemplateLocatorInterface
{
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function locate(string $template, ThemeInterface $theme): string
    {
        $templatesDirectory = sprintf('%s/templates', $theme->getPath());
        $path = Path::canonicalize(sprintf('%s/%s', $templatesDirectory, str_replace('\\', '/', $template)));

        if (!Path::isBasePath($templatesDirectory, $path) || !$this->filesystem->exists($path)) {
            throw new TemplateNotFoundException($template, [$theme]);
        }

        return $path;
    }

    public function supports(string $template): bool
    {
        return strpos($template, '@') !== 0;
    }
}
