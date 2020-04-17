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

namespace Sylius\Bundle\ThemeBundle\Twig;

use Sylius\Bundle\ThemeBundle\Templating\Locator\TemplateLocatorInterface;
use Twig\Loader\LoaderInterface as TwigLoaderInterface;
use Twig\Source;

final class ThemeFilesystemLoader implements LoaderInterface
{
    /** @var TwigLoaderInterface */
    private $decoratedLoader;

    /** @var TemplateLocatorInterface */
    private $templateLocator;

    public function __construct(
        TwigLoaderInterface $decoratedLoader,
        TemplateLocatorInterface $templateLocator
    ) {
        $this->decoratedLoader = $decoratedLoader;
        $this->templateLocator = $templateLocator;
    }

    /**
     * @param string $name
     */
    public function getSourceContext($name): Source
    {
        try {
            $path = $this->templateLocator->locate($name);

            return new Source((string) file_get_contents($path), (string) $name, $path);
        } catch (\Exception $exception) {
            return $this->decoratedLoader->getSourceContext($name);
        }
    }

    /**
     * @param string $name
     */
    public function getCacheKey($name): string
    {
        try {
            return $this->templateLocator->locate($name);
        } catch (\Exception $exception) {
            return $this->decoratedLoader->getCacheKey($name);
        }
    }

    /**
     * @param string $name
     * @param int $time
     */
    public function isFresh($name, $time): bool
    {
        try {
            return filemtime($this->templateLocator->locate($name)) <= $time;
        } catch (\Exception $exception) {
            return $this->decoratedLoader->isFresh($name, $time);
        }
    }

    /**
     * @param string $name
     */
    public function exists($name): bool
    {
        try {
            return stat($this->templateLocator->locate($name)) !== false;
        } catch (\Exception $exception) {
            return $this->decoratedLoader->exists($name);
        }
    }
}
