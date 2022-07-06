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

namespace Sylius\Bundle\ThemeBundle\Twig\Loader;

use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateLocatorInterface;
use Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateNotFoundException;
use Symfony\Component\Templating\TemplateReferenceInterface;
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

    /**
     * @param string|TemplateReferenceInterface $name
     */
    public function getSourceContext($name): Source
    {
        try {
            $path = $this->locateTemplate($name);

            /** @psalm-suppress RedundantCastGivenDocblockType */
            return new Source((string) file_get_contents($path), (string) $name, $path);
        } catch (TemplateNotFoundException | \InvalidArgumentException $exception) {
            /** @psalm-suppress PossiblyInvalidArgument */
            return $this->decoratedLoader->getSourceContext($name);
        }
    }

    /**
     * @param string|TemplateReferenceInterface $name
     */
    public function getCacheKey($name): string
    {
        try {
            return $this->locateTemplate($name);
        } catch (TemplateNotFoundException | \InvalidArgumentException $exception) {
            /** @psalm-suppress PossiblyInvalidArgument */
            return $this->decoratedLoader->getCacheKey($name);
        }
    }

    /**
     * @param string|TemplateReferenceInterface $name
     * @param int $time
     */
    public function isFresh($name, $time): bool
    {
        try {
            return filemtime($this->locateTemplate($name)) <= $time;
        } catch (TemplateNotFoundException | \InvalidArgumentException $exception) {
            /** @psalm-suppress PossiblyInvalidArgument */
            return $this->decoratedLoader->isFresh($name, $time);
        }
    }

    /**
     * @param string|TemplateReferenceInterface $name
     */
    public function exists($name): bool
    {
        try {
            return stat($this->locateTemplate($name)) !== false;
        } catch (TemplateNotFoundException | \InvalidArgumentException $exception) {
            /** @psalm-suppress PossiblyInvalidArgument */
            return $this->decoratedLoader->exists($name);
        }
    }

    /**
     * @psalm-assert string $template
     *
     * @param string|TemplateReferenceInterface $template
     *
     * @throws TemplateNotFoundException|\InvalidArgumentException
     */
    private function locateTemplate($template): string
    {
        if ($template instanceof TemplateReferenceInterface) {
            // Symfony 4.x still pushes TemplateReferenceInterface to Twig loader (especially when warming up cache)
            throw new \InvalidArgumentException(sprintf('Instances of "%s" are not supported.', TemplateReferenceInterface::class));
        }

        $theme = $this->themeContext->getTheme();

        if ($theme === null) {
            throw new TemplateNotFoundException($template, []);
        }

        return $this->templateLocator->locate($template, $theme);
    }
}
