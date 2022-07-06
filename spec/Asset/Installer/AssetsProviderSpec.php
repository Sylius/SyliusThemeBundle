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

namespace spec\Sylius\Bundle\ThemeBundle\Asset\Installer;

use PhpSpec\ObjectBehavior;
use Sylius\Bundle\ThemeBundle\Asset\Installer\AssetsProviderInterface;
use Sylius\Bundle\ThemeBundle\HierarchyProvider\ThemeHierarchyProviderInterface;
use Sylius\Bundle\ThemeBundle\Model\ThemeInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class AssetsProviderSpec extends ObjectBehavior
{
    function let(
        KernelInterface $kernel,
        ThemeHierarchyProviderInterface $themeHierarchyProvider,
        BundleInterface $acmeBundle,
        ThemeInterface $childTheme,
        ThemeInterface $parentTheme,
    ) {
        $kernel->getBundles()->willReturn([$acmeBundle]);

        $acmeBundle->getPath()->willReturn('/src/bundle/AcmeBundle');
        $acmeBundle->getName()->willReturn('AcmeBundle');

        $childTheme->getPath()->willReturn('/src/theme/child');
        $parentTheme->getPath()->willReturn('/src/theme/parent');

        $themeHierarchyProvider->getThemeHierarchy($childTheme)->willReturn([$childTheme, $parentTheme]);

        $this->beConstructedWith($kernel, $themeHierarchyProvider);
    }

    function it_is_an_assets_provider(): void
    {
        $this->shouldImplement(AssetsProviderInterface::class);
    }

    function it_returns_map_for_bundle(BundleInterface $acmeBundle): void
    {
        $this->provideDirectoriesForBundle($acmeBundle)->shouldYield([
            '/src/bundle/AcmeBundle/Resources/public' => '/bundles/acme',
            '/src/bundle/AcmeBundle/public' => '/bundles/acme',
        ]);
    }

    function it_returns_map_for_theme(ThemeInterface $childTheme): void
    {
        $this->provideDirectoriesForTheme($childTheme)->shouldYield([
            '/src/bundle/AcmeBundle/Resources/public' => '/bundles/acme',
            '/src/bundle/AcmeBundle/public' => '/bundles/acme',
            '/src/theme/parent/public' => '/',
            '/src/theme/child/public' => '/',
        ]);
    }
}
