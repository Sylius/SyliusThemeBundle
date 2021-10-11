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

namespace Sylius\Bundle\ThemeBundle\Twig\Locator;

use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Symfony\Component\Filesystem\Filesystem;

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
        $path = sprintf('%s/templates/%s', $theme->getPath(), $template);
        if (!$this->filesystem->exists($path)) {
            throw new TemplateNotFoundException($template, [$theme]);
        }

        return $path;
    }

    public function supports(string $template): bool
    {
        return strpos($template, '@') !== 0;
    }
}
