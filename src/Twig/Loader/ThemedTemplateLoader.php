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

namespace Sylius\Bundle\ThemeBundle\Twig\Loader;

use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateLocatorInterface;
use Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateNotFoundException;
use Twig\Loader\LoaderInterface as TwigLoaderInterface;
use Twig\Source;

final class ThemedTemplateLoader implements LoaderInterface
{
    private TwigLoaderInterface $decoratedLoader;

    private TemplateLocatorInterface $templateLocator;

    private ThemeContextInterface $themeContext;

    public function __construct(
        TwigLoaderInterface $decoratedLoader,
        TemplateLocatorInterface $templateLocator,
        ThemeContextInterface $themeContext,
    ) {
        $this->decoratedLoader = $decoratedLoader;
        $this->templateLocator = $templateLocator;
        $this->themeContext = $themeContext;
    }

    public function getSourceContext($name): Source
    {
        try {
            $path = $this->locateTemplate($name);

            return new Source((string) file_get_contents($path), (string) $name, $path);
        } catch (TemplateNotFoundException $exception) {
            return $this->decoratedLoader->getSourceContext($name);
        }
    }

    public function getCacheKey($name): string
    {
        try {
            return $this->locateTemplate($name);
        } catch (TemplateNotFoundException $exception) {
            return $this->decoratedLoader->getCacheKey($name);
        }
    }

    /**
     * @param int $time
     */
    public function isFresh($name, $time): bool
    {
        try {
            return filemtime($this->locateTemplate($name)) <= $time;
        } catch (TemplateNotFoundException $exception) {
            return $this->decoratedLoader->isFresh($name, $time);
        }
    }

    public function exists($name): bool
    {
        try {
            return stat($this->locateTemplate($name)) !== false;
        } catch (TemplateNotFoundException $exception) {
            return $this->decoratedLoader->exists($name);
        }
    }

    /**
     * @throws TemplateNotFoundException
     */
    private function locateTemplate(string $template): string
    {
        $theme = $this->themeContext->getTheme();

        if ($theme === null) {
            throw new TemplateNotFoundException($template, []);
        }

        return $this->templateLocator->locate($template, $theme);
    }
}
