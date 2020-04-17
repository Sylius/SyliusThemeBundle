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

namespace Sylius\Bundle\ThemeBundle\Templating\Locator;

use Sylius\Bundle\ThemeBundle\Context\ThemeContextInterface;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Sylius\Bundle\ThemeBundle\Locator\ResourceLocatorInterface;
use Sylius\Bundle\ThemeBundle\Locator\ResourceNotFoundException;

final class TemplateLocator implements TemplateLocatorInterface
{
    /** @var ThemeContextInterface */
    private $themeContext;

    /** @var ThemeHierarchyProviderInterface */
    private $themeHierarchyProvider;

    /** @var ResourceLocatorInterface */
    private $resourceLocator;

    public function __construct(
        ThemeContextInterface $themeContext,
        ThemeHierarchyProviderInterface $themeHierarchyProvider,
        ResourceLocatorInterface $resourceLocator
    ) {
        $this->themeContext = $themeContext;
        $this->themeHierarchyProvider = $themeHierarchyProvider;
        $this->resourceLocator = $resourceLocator;
    }

    public function locate(string $template): string
    {
        $theme = $this->themeContext->getTheme();

        if (null === $theme) {
            throw new ResourceNotFoundException($template, []);
        }

        $themes = $this->themeHierarchyProvider->getThemeHierarchy($theme);
        foreach ($themes as $theme) {
            try {
                return $this->resourceLocator->locateResource($template, $theme);
            } catch (ResourceNotFoundException $exception) {
                // Ignore if resource cannot be found in given theme.
            }
        }

        throw new ResourceNotFoundException($template, $themes);
    }
}
