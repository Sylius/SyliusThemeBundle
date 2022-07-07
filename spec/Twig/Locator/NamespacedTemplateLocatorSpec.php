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
use Symfony\Component\Filesystem\Filesystem;

final class NamespacedTemplateLocatorSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem): void
    {
        $this->beConstructedWith($filesystem);
    }

    function it_implements_resource_locator_interface(): void
    {
        $this->shouldImplement(TemplateLocatorInterface::class);
    }

    function it_locates_bundle_resource_using_path_derived_from_twig_namespaces(
        Filesystem $filesystem,
        ThemeInterface $theme,
    ): void {
        $theme->getPath()->willReturn('/theme/path');

        $filesystem->exists('/theme/path/templates/bundles/JustBundle/Directory/index.html.twig')->shouldBeCalled()->willReturn(true);

        $this->locate('@Just/Directory/index.html.twig', $theme)->shouldReturn('/theme/path/templates/bundles/JustBundle/Directory/index.html.twig');
    }

    function it_locates_plugin_resource_using_path_derived_from_twig_namespaces(
        Filesystem $filesystem,
        ThemeInterface $theme,
    ): void {
        $theme->getPath()->willReturn('/theme/path');

        $filesystem->exists('/theme/path/templates/bundles/JustPlugin/Directory/index.html.twig')->shouldBeCalled()->willReturn(true);

        $this->locate('@JustPlugin/Directory/index.html.twig', $theme)->shouldReturn('/theme/path/templates/bundles/JustPlugin/Directory/index.html.twig');
    }

    function it_throws_an_exception_if_resource_can_not_be_located_using_path_derived_from_twig_namespaces(
        Filesystem $filesystem,
        ThemeInterface $theme,
    ): void {
        $theme->getName()->willReturn('theme/name');
        $theme->getPath()->willReturn('/theme/path');

        $filesystem->exists('/theme/path/templates/bundles/JustBundle/Directory/index.html.twig')->shouldBeCalled()->willReturn(false);

        $this->shouldThrow(TemplateNotFoundException::class)->during('locate', ['@Just/Directory/index.html.twig', $theme]);
    }

    function it_throws_an_exception_if_resource_path_contains_two_dots_in_a_row(ThemeInterface $theme): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('locate', ['@ParentBundle/Resources/views/../views/Directory/index.html.twig', $theme]);
    }
}
