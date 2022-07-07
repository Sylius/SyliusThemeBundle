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
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateLocatorInterface;
use Sylius\Bundle\ThemeBundle\Twig\Locator\TemplateNotFoundException;

final class CompositeTemplateLocatorSpec extends ObjectBehavior
{
    function let(
        TemplateLocatorInterface $firstTemplateLocator,
        TemplateLocatorInterface $secondTemplateLocator,
    ): void {
        $this->beConstructedWith([$firstTemplateLocator, $secondTemplateLocator]);
    }

    function it_is_a_template_locator(): void
    {
        $this->shouldImplement(TemplateLocatorInterface::class);
    }

    function it_locates_a_template_using_locators_supporting_given_template(
        TemplateLocatorInterface $firstTemplateLocator,
        TemplateLocatorInterface $secondTemplateLocator,
        ThemeInterface $theme,
    ): void {
        $firstTemplateLocator->supports('template.html.twig')->willReturn(false);
        $secondTemplateLocator->supports('template.html.twig')->willReturn(true);

        $firstTemplateLocator->locate('template.html.twig', $theme)->shouldNotBeCalled();
        $secondTemplateLocator->locate('template.html.twig', $theme)->willReturn('located.html.twig');

        $this->locate('template.html.twig', $theme)->shouldReturn('located.html.twig');
    }

    function it_locates_a_template_ignoring_locator_failures(
        TemplateLocatorInterface $firstTemplateLocator,
        TemplateLocatorInterface $secondTemplateLocator,
        ThemeInterface $theme,
    ): void {
        $firstTemplateLocator->supports('template.html.twig')->willReturn(true);
        $secondTemplateLocator->supports('template.html.twig')->willReturn(true);

        $firstTemplateLocator->locate('template.html.twig', $theme)->willThrow(TemplateNotFoundException::class);
        $secondTemplateLocator->locate('template.html.twig', $theme)->willReturn('located.html.twig');

        $this->locate('template.html.twig', $theme)->shouldReturn('located.html.twig');
    }

    function it_throws_an_exception_if_no_locator_supports_given_template(
        TemplateLocatorInterface $firstTemplateLocator,
        TemplateLocatorInterface $secondTemplateLocator,
        ThemeInterface $theme,
    ): void {
        $firstTemplateLocator->supports('template.html.twig')->willReturn(false);
        $secondTemplateLocator->supports('template.html.twig')->willReturn(false);

        $firstTemplateLocator->locate('template.html.twig', $theme)->shouldNotBeCalled();
        $secondTemplateLocator->locate('template.html.twig', $theme)->shouldNotBeCalled();

        $theme->getName()->willReturn('theme/name');

        $this->shouldThrow(TemplateNotFoundException::class)->during('locate', ['template.html.twig', $theme]);
    }

    function it_throws_an_exception_if_no_locator_returns_meaningful_response(
        TemplateLocatorInterface $firstTemplateLocator,
        TemplateLocatorInterface $secondTemplateLocator,
        ThemeInterface $theme,
    ): void {
        $firstTemplateLocator->supports('template.html.twig')->willReturn(true);
        $secondTemplateLocator->supports('template.html.twig')->willReturn(true);

        $firstTemplateLocator->locate('template.html.twig', $theme)->willThrow(TemplateNotFoundException::class);
        $secondTemplateLocator->locate('template.html.twig', $theme)->willThrow(TemplateNotFoundException::class);

        $theme->getName()->willReturn('theme/name');

        $this->shouldThrow(TemplateNotFoundException::class)->during('locate', ['template.html.twig', $theme]);
    }

    function it_supports_locating_a_template_if_one_of_locators_does(
        TemplateLocatorInterface $firstTemplateLocator,
        TemplateLocatorInterface $secondTemplateLocator,
    ): void {
        $firstTemplateLocator->supports('template.html.twig')->willReturn(false);
        $secondTemplateLocator->supports('template.html.twig')->willReturn(true);

        $this->supports('template.html.twig')->shouldReturn(true);
    }

    function it_does_not_support_locating_a_template_if_no_locator_does(
        TemplateLocatorInterface $firstTemplateLocator,
        TemplateLocatorInterface $secondTemplateLocator,
    ): void {
        $firstTemplateLocator->supports('template.html.twig')->willReturn(false);
        $secondTemplateLocator->supports('template.html.twig')->willReturn(false);

        $this->supports('template.html.twig')->shouldReturn(false);
    }
}
