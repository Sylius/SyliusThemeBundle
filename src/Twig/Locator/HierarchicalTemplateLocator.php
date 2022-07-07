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

use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;

final class HierarchicalTemplateLocator implements TemplateLocatorInterface
{
    private TemplateLocatorInterface $templateLocator;

    private ThemeHierarchyProviderInterface $themeHierarchyProvider;

    public function __construct(
        TemplateLocatorInterface $templateLocator,
        ThemeHierarchyProviderInterface $themeHierarchyProvider,
    ) {
        $this->templateLocator = $templateLocator;
        $this->themeHierarchyProvider = $themeHierarchyProvider;
    }

    public function locate(string $template, ThemeInterface $theme): string
    {
        $providedThemes = $this->themeHierarchyProvider->getThemeHierarchy($theme);
        foreach ($providedThemes as $providedTheme) {
            try {
                return $this->templateLocator->locate($template, $providedTheme);
            } catch (TemplateNotFoundException $exception) {
                // Ignore if resource cannot be found in given theme.
            }
        }

        throw new TemplateNotFoundException($template, $providedThemes);
    }

    public function supports(string $template): bool
    {
        return $this->templateLocator->supports($template);
    }
}
