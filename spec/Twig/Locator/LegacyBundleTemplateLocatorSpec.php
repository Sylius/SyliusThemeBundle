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
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class LegacyBundleTemplateLocatorSpec extends ObjectBehavior
{
    function let(Filesystem $filesystem, KernelInterface $kernel): void
    {
        $this->beConstructedWith($filesystem, $kernel);
    }

    function it_implements_resource_locator_interface(): void
    {
        $this->shouldImplement(TemplateLocatorInterface::class);
    }

    function it_locates_bundle_resource_using_path_derived_from_bundle_notation_and_symfony4_kernel_behaviour(
        Filesystem $filesystem,
        KernelInterface $kernel,
        ThemeInterface $theme,
        BundleInterface $justBundle
    ): void {
        $kernel->getBundle('JustBundle')->willReturn($justBundle);

        $justBundle->getName()->willReturn('JustBundle');

        $theme->getPath()->willReturn('/theme/path');

        $filesystem->exists('/theme/path/JustBundle/views/Directory/index.html.twig')->shouldBeCalled()->willReturn(true);

        $this->locate('@JustBundle/Resources/views/Directory/index.html.twig', $theme)->shouldReturn('/theme/path/JustBundle/views/Directory/index.html.twig');
    }

    function it_throws_an_exception_if_resource_can_not_be_located_using_path_derived_from_bundle_notation(
        Filesystem $filesystem,
        KernelInterface $kernel,
        ThemeInterface $theme,
        BundleInterface $bundle
    ): void {
        $kernel->getBundle('Bundle')->willReturn($bundle);

        $bundle->getName()->willReturn('Bundle');

        $theme->getName()->willReturn('theme/name');
        $theme->getPath()->willReturn('/theme/path');

        $filesystem->exists('/theme/path/Bundle/views/Directory/index.html.twig')->shouldBeCalled()->willReturn(false);

        $this->shouldThrow(TemplateNotFoundException::class)->during('locate', ['@Bundle/Resources/views/Directory/index.html.twig', $theme]);
    }

    function it_throws_an_exception_if_resource_path_contains_two_dots_in_a_row(ThemeInterface $theme): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('locate', ['@ParentBundle/Resources/views/../views/Directory/index.html.twig', $theme]);
    }
}
