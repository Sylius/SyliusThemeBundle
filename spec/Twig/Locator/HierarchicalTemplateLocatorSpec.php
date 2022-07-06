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

namespace spec\Sylius\Bundle\ThemeBundle\Twig\Locator;

use PhpSpec\ObjectBehavior;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateLocatorInterface;
use Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateNotFoundException;

final class HierarchicalTemplateLocatorSpec extends ObjectBehavior
{
    function let(
        TemplateLocatorInterface $templateLocator,
        ThemeHierarchyProviderInterface $themeHierarchyProvider,
        ThemeInterface $childTheme,
        ThemeInterface $parentTheme,
    ): void {
        $themeHierarchyProvider->getThemeHierarchy($childTheme)->willReturn([$childTheme, $parentTheme]);

        $this->beConstructedWith($templateLocator, $themeHierarchyProvider);
    }

    function it_is_a_template_locator(): void
    {
        $this->shouldImplement(TemplateLocatorInterface::class);
    }

    function it_locates_a_template_using_themes_hierarchy(
        TemplateLocatorInterface $templateLocator,
        ThemeInterface $childTheme,
        ThemeInterface $parentTheme,
    ): void {
        $templateLocator->locate('template.html.twig', $childTheme)->willThrow(TemplateNotFoundException::class);
        $templateLocator->locate('template.html.twig', $parentTheme)->willReturn('located.html.twig');

        $this->locate('template.html.twig', $childTheme)->shouldReturn('located.html.twig');
    }

    function it_throws_an_exception_if_no_locator_returns_meaningful_response_for_given_themes(
        TemplateLocatorInterface $templateLocator,
        ThemeInterface $childTheme,
        ThemeInterface $parentTheme,
    ): void {
        $templateLocator->locate('template.html.twig', $childTheme)->willThrow(TemplateNotFoundException::class);
        $templateLocator->locate('template.html.twig', $parentTheme)->willThrow(TemplateNotFoundException::class);

        $childTheme->getName()->willReturn('child/theme');
        $parentTheme->getName()->willReturn('parent/theme');

        $this->shouldThrow(TemplateNotFoundException::class)->during('locate', ['template.html.twig', $childTheme]);
    }

    function it_supports_locating_a_template_if_decorated_loader_does(TemplateLocatorInterface $templateLocator): void
    {
        $templateLocator->supports('template.html.twig')->willReturn(true);

        $this->supports('template.html.twig')->shouldReturn(true);
    }

    function it_does_not_support_locating_a_template_if_decorated_loader_does_not(TemplateLocatorInterface $templateLocator): void
    {
        $templateLocator->supports('template.html.twig')->willReturn(false);

        $this->supports('template.html.twig')->shouldReturn(false);
    }
}
