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
    /** @var TemplateLocatorInterface */
    private $templateLocator;

    /** @var ThemeHierarchyProviderInterface */
    private $themeHierarchyProvider;

    public function __construct(
        TemplateLocatorInterface $templateLocator,
        ThemeHierarchyProviderInterface $themeHierarchyProvider
    ) {
        $this->templateLocator = $templateLocator;
        $this->themeHierarchyProvider = $themeHierarchyProvider;
    }

    public function locate(string $template, ThemeInterface $rootTheme): string
    {
        $themes = $this->themeHierarchyProvider->getThemeHierarchy($rootTheme);
        foreach ($themes as $theme) {
            try {
                return $this->templateLocator->locate($template, $theme);
            } catch (TemplateNotFoundException $exception) {
                // Ignore if resource cannot be found in given theme.
            }
        }

        throw new TemplateNotFoundException($template, $themes);
    }

    public function supports(string $template): bool
    {
        return $this->templateLocator->supports($template);
    }
}
